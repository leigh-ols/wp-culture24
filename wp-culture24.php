<?php
/*
 * Plugin Name: WPCulture24
 * Plugin URI: http://github.com/zenlan
 * Version: 1.0.2
 * Description: Connect to the Culture24 API
 * Author: Monique Szpak for the Imperial War Museums
 * Author URI: http://zenlan.com
 * License: MIT
 */

namespace c24;

define('CULTURE24__MINIMUM_WP_VERSION', '3.3');
define('CULTURE24__VERSION', '0.1.0');

define('CULTURE24__CONNECTOR_PATH', dirname(__FILE__));
define('CULTURE24__ASSETS_URI', plugins_url('assets/', __FILE__));
define('CULTURE24__VENDOR_URI', plugins_url('vendor/', __FILE__));
define('CULTURE24__ASSETS_PATH', CULTURE24__CONNECTOR_PATH.'/assets/');
define('CULTURE24__VENDOR_PATH', CULTURE24__CONNECTOR_PATH.'/vendor/');

defined('CULTURE24_API_DEBUG') or define('CULTURE24_API_DEBUG', false);
defined('CULTURE24_API_EVENTS') or define('CULTURE24_API_EVENTS', 'events');
defined('CULTURE24_API_VENUES') or define('CULTURE24_API_VENUES', 'venues');
defined('CULTURE24_API_MAX_TRIES') or define('CULTURE24_API_MAX_TRIES', 3);
define('CULTURE24_API_DATE_FORMAT_INPUT', 'd-m-Y');
define('CULTURE24_API_DATE_FORMAT_OUTPUT', 'd/m/Y');
define('CULTURE24_API_DATE_END_DEFAULT', date(CULTURE24_API_DATE_FORMAT_INPUT, time() + (7 * (24 * 3600))));
// @TODO, this is used for features which aren't necessarily displayed on the
// event page. When we allow page use through overriding slug instead of using
// a shortcode, we can remove this
define('C24_SLUG', 'events');

// Composer PSR-4 auto-loader
// We check to see if this exists because it's possible we've been included as
// a dependency in a higher project
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require __DIR__ . '/vendor/autoload.php';
}

global $__c24;
$__c24 = new WPCulture24();
do_action('wpculture24_loaded');
