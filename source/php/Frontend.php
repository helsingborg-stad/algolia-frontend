<?php

namespace AlgoliaFrontend;

class Frontend
{
    public function __construct()
    {
        add_action('pre_get_posts', array($this, 'makeSearchQuery'));
    }

    public function queryAlgoliaSearch($query) {

        if(!defined('ALGOLIA_FRONTEND_INDEXES')) {
            return;
        }

        if(!is_array(ALGOLIA_FRONTEND_INDEXES) ||empty(ALGOLIA_FRONTEND_INDEXES)) {
            return;
        }

        //Get settings
        $appId      = get_option('algolia_application_id');
        $appSecret  = get_option('algolia_api_key');

        if(empty($appId)||empty($appSecret)) {
            return;
        }

        //Run search helper
        $algolia = new AlgoliaFrontend\Search($appId, $appSecret);

        //Add indexes to search
        foreach(ALGOLIA_FRONTEND_INDEXES as $index) {
            $algolia->addIndex($index[0], $index[1]);
        }

        //Query
        $result= $algolia->search($query, 100);
    }
}
