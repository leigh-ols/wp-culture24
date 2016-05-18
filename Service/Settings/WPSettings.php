<?php
/**
 * WPSettings.php
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

namespace c24\Service\Settings;

/**
 * Class WPSettings
 *
 * @category
 * @package
 * @subpackage
 * @author     Leigh Bicknell <leigh@orangeleaf.com>
 * @license    Copyright Orangeleaf Systems Ltd 2013
 * @link       http://orangeleaf.com
 */
class WPSettings implements SettingsInterface
{
    /**
     * Settings field keys are prefixed with this string
     *
     * @var string
     */
    public $settings_prefix = 'c24api_';

    protected $fallback_theme = 'DefaultTheme';
    protected $theme_root_namespace = '\c24\Themes';

    /**
     * setThemeNamespace
     *
     * @param string $namespace
     *
     * @return self
     * @access public
     */
    public function setThemeNamespace($namespace)
    {
        $this->theme_root_namespace = $namespace;
        return $this;
    }

    /**
     * setPrefix
     *
     * @param string $prefix
     *
     * @return self
     * @access public
     */
    public function setPrefix($prefix)
    {
        $this->settings_prefix = $prefix;
        return $this;
    }

    /**
     * setSetting
     *
     * Wrapper for WordPress update_option function. Automatically adds
     * 'settings_prefix'.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return bool (true if changed, false if fail or not changed)
     * @access public
     */
    public function setSetting($key, $value)
    {
        return update_option($this->settings_prefix . $key, $value);
    }

    /**
     * getSetting
     *
     * Retrieves a setting/option from WordPress automatically adding $this->settings_prefix
     *
     * @param string $option Option key (not including $this->settings_prefix)
     * @param mixed $default (optional)
     *
     * @return mixed
     * @access public
     */
    public function getSetting($key, $default=null)
    {
        return get_option($this->settings_prefix . $key, $default);
    }

    /**
     * getCurrentTheme
     *
     * Gets current theme setting, falls back to default if Theme is missing.
     *
     * @return string
     * @access public
     */
    public function getCurrentTheme()
    {
        $theme = $this->getSetting('theme', $this->fallback_theme);
        $theme_namespace = $this->theme_root_namespace.'\\'.$theme.'\\'.$theme;
        if (!class_exists($theme_namespace)) {

            // DEBUG
            echo "\r\n<pre><!-- \r\n";
            $DBG_DBG = debug_backtrace();
            foreach ($DBG_DBG as $DD) {
                echo implode(':', array(@$DD['file'], @$DD['line'], @$DD['function'])) . "\r\n";
            }
            echo " -->\r\n";
            var_dump($theme_namespace);
            echo "</pre>\r\n";

            // @TODO add some sort of admin alert here
            $theme = $this->fallback_theme;
            $this->setSetting('theme', $theme);
            $theme_namespace = $this->theme_root_namespace.'\\'.$theme.'\\'.$theme;
        }
        return $theme_namespace;
    }

    /**
     * getCurrentThemeNamespace
     *
     * Return the namespace to the current Theme without the final theme class.
     *
     * @return string
     * @access public
     */
    public function getCurrentThemeNamespace()
    {
        // Get the Theme
        $theme = $this->getCurrentTheme();

        // Remove the Theme Class name from the theme to return just the
        // namespace
        // c24\Themes\DefaultTheme\DefaultTheme
        // becomes:
        // c24\Themes\DefaultTheme
        $theme_namespaces = explode('\\', $theme);
        array_pop($theme_namespaces);
        $theme_namespace = implode('\\', $theme_namespaces);
        return $theme_namespace;
    }
}

