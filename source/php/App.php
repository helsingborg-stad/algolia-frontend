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
        add_action('admin_enqueue_scripts', array($this, 'enqueueStyles'));
        add_action('admin_enqueue_scripts', array($this, 'enqueueScripts'));
    }

    /**
     * Enqueue required style
     * @return void
     */
    public function enqueueStyles()
    {
    }

    /**
     * Enqueue required scripts
     * @return void
     */
    public function enqueueScripts()
    {
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
