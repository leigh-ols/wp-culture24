<?php
/**
 * WPCulture24.php
 *
 * PHP Version 5.4
 *
 * @category
 * @package
 * @subpackage
 * @author     Leigh Bicknell <leigh@orangeleaf.com>
 * @license    Copyright Orangeleaf Systems Ltd 2013
 * @link       http://orangeleaf.com
 */

namespace c24;

// Uses

/**
 * Class WPCulture24
 *
 * @category
 * @package
 * @subpackage
 * @author     Leigh Bicknell <leigh@orangeleaf.com>
 * @license    Copyright Orangeleaf Systems Ltd 2013
 * @link       http://orangeleaf.com
 */
class WPCulture24
{
    /**
     * config
     *
     * @var mixed[]
     */
    private $config;

    /**
     * __construct
     *
     * @return void
     * @access public
     */
    public function __construct()
    {
        add_action('wp_enqueue_scripts', array($this, 'enqueueScripts'), 99);
        add_action('admin_enqueue_scripts', array($this, 'enqueueAdminScripts'), 99);
        add_action('wp_head', array($this, 'wpHead'));
        add_action('admin_head', array($this, 'wpHead'));

        // Init plugin after all other plugins
        add_action('init', array($this, 'init'), 11);
    }

    /**
     * enqueueScripts
     * Enqueue js/css scripts for the front end
     *
     * @return void
     * @access public
     */
    public function enqueueScripts()
    {

    }

    /**
     * enqueueAdminScripts
     * Enqueue js/css scripts for the dashboard area
     *
     * @return void
     * @access public
     */
    public function enqueueAdminScripts()
    {

    }

    /**
     * Create Objects
     * This looks complex, but there is no logic in here at all.
     * It is actually the entire plugin wiring all in one place.
     * We can look at this to get an overview of the entire plugins structure,
     * And each components dependency tree.
     * It also has the advantage of making the plugin highly testable.
     *
     * @return void
     * @access public
     */
    public function init()
    {
        // Make sure wordpress doesn't screw with our request variables...
        $_GET = stripslashes_deep($_GET);
        $_POST = stripslashes_deep($_POST);
        $_COOKIE = stripslashes_deep($_COOKIE);
        $_REQUEST = stripslashes_deep($_REQUEST);

        // Create objects
       // $config = new \WordPressSettingsFramework

        // Hookable action
        do_action('wpculture24_init');
    }

    /**
     * wpHead
     *
     * @return void
     * @access public
     */
    public function wpHead()
    {
        // @TODO once config class is implemented, load JS config vars into a global JS var
        return;
        //$js_settings = $this->getConfig()->getModuleSettings('JS');
        echo '<script>
                var C24_CONFIG = JSON.parse(\''.json_encode($js_settings, JSON_HEX_APOS).'\');
            </script>'."\r\n";
    }

    /**
     * getConfig
     * Allows access to this plugins config from outside of the plugin
     * eg:
     * $dams_settings = $__dams_connector->getConfig()->getAllSettings();
     *
     * @return \ol\dams\Service\Config\ConfigInterface
     * @access public
     */
    public function getConfig()
    {
        return $this->config;
    }
}
