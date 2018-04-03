<?php

if(!function_exists('queryAlgoliaSearch')) {
    function queryAlgoliaSearch($query) {

        if(!defined('ALGOLIA_FRONTEND_INDEXES')) {
            return null;
        }

        if(!defined('ALGOLIA_PATH')) {
            return null;
        }

        if(!is_array(ALGOLIA_FRONTEND_INDEXES) ||empty(ALGOLIA_FRONTEND_INDEXES)) {
            return null;
        }

        //Get settings
        $appId      = get_option('algolia_application_id');
        $appSecret  = get_option('algolia_api_key');

        //Return null if settings is faulty
        if(empty($appId)||empty($appSecret)) {
            return null;
        }

        //Run search helper
        $algolia = new AlgoliaFrontend\Search($appId, $appSecret);

        //Limit search to index?
        if(isset($_GET['index_id']) && is_numeric($_GET['index_id'])) {
            $indexId = array($_GET['index_id']);
        } elseif(isset($_GET['index_id']) && !is_numeric($_GET['index_id'])) {
            $indexId = (array) explode(",", $_GET['index_id']);
        } else {
            $indexId = null;
        }

        //Add indexes to search
        foreach(ALGOLIA_FRONTEND_INDEXES as $indexKey => $index) {
            if(is_null($indexId) ||!is_null($indexId) && in_array($indexKey, $indexId)) {
                $algolia->addIndex($index[0], $index[1]);
            }
        }

        //Query
        return $algolia->search($query, 100);
    }
}
