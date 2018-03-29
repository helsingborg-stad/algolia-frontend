<?php

namespace AlgoliaFrontend;

class App
{
    public function __construct()
    {
        //Require algolia master plugin
        add_action('init', function () {
            if (!defined('ALGOLIA_PATH')) {
                $this->pluginNotice();
            } else {
                $this->init();
            }
        });
    }

    /**
     * Init plugin
     * @return void
     */
    public function init()
    {
        add_action('admin_enqueue_scripts', array($this, 'adminEnqueueStyles'));

        $this->configurationNotice();
        $this->filterSettings();
        $this->removeOptions();
    }

    /**
     * Enqueue required style
     * @return void
     */
    public function adminEnqueueStyles($hook)
    {
        wp_enqueue_style('algolia-frontend-admin', ALGOLIAFRONTEND_URL . '/dist/css/algolia-admin.min.css');
    }

    public function filterSettings()
    {
        //Deactivate algolia plugin search
        add_filter('option_algolia_override_native_search', function () {
            return "native";
        });

        //Always disable autocomplete
        add_filter('option_algolia_autocomplete_enabled', function () {
            return "no";
        });
    }

    public function removeOptions()
    {
        //Remove search engine type option
        add_action('admin_menu', function() {
            remove_submenu_page('algolia','algolia-search-page');
        }, 50);
    }

    /**
     * Requires configuration nag message
     * @return void
     */
    public function configurationNotice()
    {
        if(!current_user_can('administrator')&&!is_superadmin()) {
            return;
        }

        if(defined('ALGOLIA_FRONTEND_INDEXES')) {
            return;
        }

        add_action('admin_notices', function () {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p>' .__('Algolia frontend: Please provide indexes to search. Refer to the documentation on how to configure indexes in wp-config.', 'algolia-frontend'). '</p>';
            echo '</div>';
        });
    }

    /**
     * Requires algolia nag message
     * @return void
     */
    public function pluginNotice()
    {
        if(!current_user_can('administrator')&&!is_superadmin()) {
            return;
        }

        add_action('admin_notices', function () {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p>' .__('Algolia frontend: Could not find Algolia master plugin (Search By Algolia). Please activate this to enable the frontend modifications.', 'algolia-frontend'). '</p>';
            echo '</div>';
        });
    }
}
