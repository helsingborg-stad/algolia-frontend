<?php

namespace AlgoliaFrontend;

class Search
{
    private $indexes = []; // Will change each query, do not interpitate as a template
    private $client;

    /**
     * Init plugin with construct, only if constant is set and valid
     * @return void
     */
    public function __construct($client, $secret)
    {
        if (is_null($client) ||is_null($secret)) {
            die("Could not connect to Algolia search API due to missing client or secret key.");
        }

        if (defined('ALGOLIA_FRONTEND_RESULTS')) {
            $this->numberOfResults = ALGOLIA_FRONTEND_RESULTS;
        } else {
            $this->numberOfResults = 150;
        }

        //Init client
        $this->client = new \AlgoliaSearch\Client($client, $secret);

        //Setup extra headers
        $this->client->setExtraHeader("X-Forwarded-For", $_SERVER['REMOTE_ADDR']);
    }

    /* Add a index for searching. Hits per page may be set to decrease the importance/size of the index (less hits).
     * @param string $indexName The key of the index.
     * @param integer $hitsPerPage Number of hits to retrive
     * @return bool Boolean if the index was added successfully or not.
     */
    public function addIndex($indexName, $hitsPerPage = 500) : bool
    {
        if (isset($indexName) && !empty($indexName)) {
            $this->indexes[$indexName] = array(
                'indexName' => $indexName,
                'hitsPerPage' => $hitsPerPage,
                'getRankingInfo' => true
            );

            return true;
        }

        throw new Exception('A index definition must contain a valid name.');

        return false;
    }

    /* Do the actual search, sanitize and formatting of the response.
     * @param string $searchQuery What to search for
     * @param integer $numberOfResults Truncate number of results to N objects.
     * @return array Formatted response of found items, or empty array.
     */
    public function search($searchQuery, $numberOfResults = null) : array
    {
        if (!is_numeric($numberOfResults)) {
            $numberOfResults = $this->numberOfResults;
        }

        if (isset($this->indexes) && is_array($this->indexes) && !empty($this->indexes)) {

            //Create multi index query
            foreach ($this->indexes as &$index) {
                $index['query'] = $searchQuery;
            }

            //Define cache key & group
            $cacheKey = md5($this->indexes) . md5($searchQuery);
            $cacheGroup = 'algolia-frontend';

            //Make search
            if (!$response = wp_cache_get($cacheKey, $cacheGroup)) {
                $response = $this->client->multipleQueries($this->indexes)['results'];
                wp_cache_add($cacheKey, $response, $cacheGroup, 60*15);
            }

            //Make calc of relative score
            $response = $this->calculateRelativeScore($response);

            //Return empty response if no matches
            if (!array_filter($response, function ($check) {
                return !empty($check['hits']); }
            )) {
                return array();
            }

            //Must run pre formatting
            $response = $this->mergeHits($response);

            //Simplification
            $response = $this->simplifyHits($response);

            // Requires simplified
            $response = $this->removeInvalidUrls($response);
            $response = $this->sortResponse($response, "score");

            //Return truncated results
            return array_slice($response, 0, $numberOfResults);
        }

        throw new Exception('No indexes defined, please use "addIndex" function to add an index before querying.');
    }

    /* Calculate response score based on number or found results in the index.
     * @param array $response Response to calculate on.
     * @return array Formatted response with score. Lower is better.
     */
    public function calculateRelativeScore($response) : array
    {
        if (is_array($response) && !empty($response)) {
            foreach ($response as $indexKey => $index) {
                if (is_array($response[$indexKey]['hits']) && !empty($response[$indexKey]['hits'])) {
                    foreach ($response[$indexKey]['hits'] as $objectKey => $object) {

                        //Calculate score
                        $score = array(
                            'defaultPosition' => ((int) ($objectKey+1) / count($response[$indexKey]['hits']) * 100) * 0.2 // Simple merge by position in each result. Lower multiplication in the end, will bring each site "closer" to eachother.
                        );

                        $response[$indexKey]['hits'][$objectKey]['calculated_score'] = abs(array_sum($score));
                    }
                }
            }
        }

        return $response;
    }

    /* Merge multiple indexes to one combined index.
     * @param array $response Response to calculate on.
     * @return array Merged response
     */
    public function mergeHits($response) : array
    {
        $mergedResponse = [];

        if (is_array($response) && !empty($response)) {
            foreach ($response as $indexResponse) {
                if (isset($indexResponse['hits']) && !empty($indexResponse['hits']) && is_array($indexResponse['hits'])) {

                    //Add identifiers (readable)
                    foreach ($indexResponse['hits'] as $key => $item) {
                        $indexResponse['hits'][$key]['index_name']  = $this->indexKeyToName($indexResponse['index']);
                        $indexResponse['hits'][$key]['index_id']    = $this->indexKeyToId($indexResponse['index']);
                        $indexResponse['hits'][$key]['index_slug']  = $indexResponse['index'];
                    }

                    //Merge result
                    $mergedResponse = array_merge($mergedResponse, $indexResponse['hits']);
                }
            }
            return $mergedResponse;
        }

        throw new Exception('Could not merge array, response is broken.');
    }

    /* Make response array as simple as possible, remove unncessary data.
     * @param array $response Response to simplify.
     * @return array Simplified response
     */
    public function simplifyHits($response) : array
    {
        $simplifiedResponse = [];

        if (is_array($response) && !empty($response)) {
            foreach ($response as $responseItem) {
                $simplifiedResponse[] = array(
                    'post_title'    => $responseItem['post_title'],
                    'post_excerpt'  => $responseItem['_snippetResult']['content']['value'],
                    'post_content'  => $responseItem['content'],
                    'post_date'     => $responseItem['post_date_formatted'],
                    'permalink'     => $responseItem['permalink'],
                    'score'         => $responseItem['calculated_score'],
                    'index_name'    => $responseItem['index_name'],
                    'index_id'      => $responseItem['index_id'],
                    'index_slug'    => $responseItem['index_slug'],
                );
            }

            return $simplifiedResponse;
        }

        throw new Exception('Could not simplify response. The input was not valid.');
    }

    /* Simple array sorting function
     * @param array $response Array to sort
     * @param string $sortKey The name of the key that array should be sorted by.
     * @return array Sorted array
     */
    public function sortResponse($response, $sortKey = "score") : array
    {
        usort($response, function ($a, $b) use ($sortKey) {
            return $a[$sortKey] <=> $b[$sortKey];
        });

        return $response;
    }

    /* Remove every page that dosen't have a valid permalink
     * @param array $response Array to sanitize
     * @return array Sanitized array
     */
    public function removeInvalidUrls($response) : array
    {
        return array_filter($response, function ($item) {
            return !preg_match("/page_id/i", $item['permalink']);
        });
    }

    /* Translate index key to name of index
     * @param string The index key (from algolia)
     * @return string The name of index
     */
    public function indexKeyToName($indexKey) : string
    {
        foreach (ALGOLIA_FRONTEND_INDEXES as $index) {
            if (in_array($indexKey, $index)) {
                return $index[2];
            }
        }
        return "";
    }

    /* Translate index key to (local)id of index
     * @param string The index key (from algolia)
     * @return string The id of index
     */
    public function indexKeyToId($indexKey) : int
    {
        foreach (ALGOLIA_FRONTEND_INDEXES as $key => $index) {
            if (in_array($indexKey, $index)) {
                return $key;
            }
        }
        return 0;
    }
}
