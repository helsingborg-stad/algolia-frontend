<?php

namespace AlgoliaFrontend;

class App
{
    public function __construct()
    {
        //Require algolia master plugin
        add_action('init', function () {
            if (!defined('ALGOLIA_PATH')) {
                $this->notice();
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
        add_action('admin_enqueue_scripts', array($this, 'adminEnqueueScripts'));

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

    /**
     * Enqueue required scripts
     * @return void
     */
    public function adminEnqueueScripts()
    {
    }

    public function filterSettings()
    {
        //Deactivate algolia plugin search
        add_filter('option_algolia_override_native_search', function () {
            return "native";
        });

        //Always use powerd by option
        add_filter('option_algolia_powered_by_enabled', function () {
            return "yes";
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

        //Remove autocomplete option
        add_action('admin_menu', function() {
            //remove_submenu_page('algolia','algolia');
        }, 50);
    }

    /**
     * Requires algolia nag message
     * @return void
     */
    public function notice()
    {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p>' .__('Could not find algolia master plugin (Search By Algolia). Please activate this to enable the frontend modifications.', 'algolia-frontend'). '</p>';
            echo '</div>';
        });
    }
}
