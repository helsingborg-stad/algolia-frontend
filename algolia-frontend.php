<?php

/**
 * Plugin Name:       Algolia Frontend
 * Plugin URI:        https://github.com/helsingborg-stad/algolia-frontend
 * Description:       Replaces the standard algolia frontend with our own. Forces algolia to use specific settings.
 * Version:           1.0.0
 * Author:            Sebastian Thulin
 * Author URI:        https://github.com/helsingborg-stad/
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       algolia-frontend
 * Domain Path:       /languages
 */

 // Protect agains direct file access
if (! defined('WPINC')) {
    die;
}

define('ALGOLIAFRONTEND_PATH', plugin_dir_path(__FILE__));
define('ALGOLIAFRONTEND_URL', plugins_url('', __FILE__));
define('ALGOLIAFRONTEND_TEMPLATE_PATH', ALGOLIAFRONTEND_PATH . 'templates/');

load_plugin_textdomain('algolia-frontend', false, plugin_basename(dirname(__FILE__)) . '/languages');

require_once ALGOLIAFRONTEND_PATH . 'source/php/Vendor/Psr4ClassLoader.php';
require_once ALGOLIAFRONTEND_PATH . 'Public.php';

// Instantiate and register the autoloader
$loader = new AlgoliaFrontend\Vendor\Psr4ClassLoader();
$loader->addPrefix('AlgoliaFrontend', ALGOLIAFRONTEND_PATH);
$loader->addPrefix('AlgoliaFrontend', ALGOLIAFRONTEND_PATH . 'source/php/');
$loader->register();

// Start application
new AlgoliaFrontend\App();
