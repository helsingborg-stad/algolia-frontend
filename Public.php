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

        //Add indexes to search
        foreach(ALGOLIA_FRONTEND_INDEXES as $index) {
            $algolia->addIndex($index[0], $index[1]);
        }

        //Query
        return $algolia->search($query, 10);
    }
}
