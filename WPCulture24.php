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

use \GUMP as RealValidator;
use c24\Service\Validator\GumpValidator as Validator;
use c24\Service\Api\Culture24\Api as Culture24Api;
use c24\Service\Settings\WPSettings as Settings;
use c24\Themes\ThemeInterface as Theme;
use c24\Admin\Admin;

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
     * services
     *
     * @var object[]
     */
    private $services;

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
        //add_action('init', array($this, 'init'), 11);

        // For now init immediately
        $this->init();
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
        wp_register_script('c24', __DIR__.'/assets/js/wp-culture24.js', array('jquery', 'jquery-ui-datepicker'));
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_script('c24');
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
     * We can look at this to get an overview of the whole plugins structure,
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
        $real_validator = new RealValidator();

        $this->setValidator(new Validator($real_validator));
        $this->setApi(new Culture24Api());
        $this->setSettings(new Settings());

        $theme_namespace = $this->getSettings()->getCurrentTheme();
        $this->setTheme(
            new $theme_namespace(
                $this->getSettings(),
                $this->getApi(),
                $this->getValidator()
            )
        );

        $this->setAdmin(
            new Admin(
                $this->getSettings(),
                $this->getTheme()
            )
        );

        // Hookable action
        do_action('wpculture24_init');
    }

    /**
     * getService
     * Allows retrieval of a service outside of plugin
     *
     * @param string $service
     *
     * @return mixed
     * @access public
     */
    public function getService($service)
    {
        if (isset($this->services[$service])) {
            return $this->services[$service];
        }
        return false;
    }

    /**
     * getApi
     *
     * Shortcut for $this->getService('Culsture24Api');
     *
     * @return Culture24Api
     * @access public
     */
    public function getApi()
    {
        return $this->getService('Culture24Api');
    }

    /**
     * setApi
     *
     * @param Culture24Api $api
     *
     * @return self
     * @access public
     */
    public function setApi(Culture24Api $api)
    {
        $this->services['Culture24Api'] = $api;
        return $this;
    }

    /**
     * getValidator
     *
     *
     * @return Validator
     * @access public
     */
    public function getValidator()
    {
        return $this->getService('Validator');
    }

    /**
     * setValidator
     *
     * @param Validator $validator
     *
     * @return self
     * @access public
     */
    public function setValidator(Validator $validator)
    {
        $this->services['Validator'] = $validator;
        return $this;
    }

    /**
     * getAdmin
     * Allows retrieval of Admin class. Really a temporary function to keep
     * legacy code functional during plugin refactor.
     *
     * @return c24\Admin\Admin
     * @access public
     */
    public function getAdmin()
    {
        return $this->getService('Admin');
    }

    /**
     * setAdmin
     *
     * @param Admin $admin
     *
     * @return self
     * @access public
     */
    public function setAdmin(Admin $admin)
    {
        $this->services['Admin'] = $admin;
        return $this;
    }

    /**
     * getSettings
     * Allows retrieval of Settings class. Really a temporary function to keep
     * legacy code functional during plugin refactor.
     *
     * @return c24\Settings\SettingsInterface
     * @access public
     */
    public function getSettings()
    {
        return $this->getService('Settings');
    }

    /**
     * setSettings
     *
     * @param Settings $admin
     *
     * @return self
     * @access public
     */
    public function setSettings(Settings $admin)
    {
        $this->services['Settings'] = $admin;
        return $this;
    }

    /**
     * getTheme
     * Allows retrieval of Theme class. Really a temporary function to keep
     * legacy code functional during plugin refactor.
     *
     * @return c24\Theme\ThemeInterface
     * @access public
     */
    public function getTheme()
    {
        return $this->getService('Theme');
    }

    /**
     * setTheme
     *
     * @param Theme $admin
     *
     * @return self
     * @access public
     */
    public function setTheme(Theme $admin)
    {
        $this->services['Theme'] = $admin;
        return $this;
    }
}
