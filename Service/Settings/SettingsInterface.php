<?php
/**
 * SettingsInterface.php
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
 * Interface SettingsInterface
 *
 * @category
 * @package
 * @subpackage
 * @author     Leigh Bicknell <leigh@orangeleaf.com>
 * @license    Copyright Orangeleaf Systems Ltd 2013
 * @link       http://orangeleaf.com
 */
interface SettingsInterface
{
    /**
     * getOption
     *
     * Retrieves a setting/option from WordPress automatically adding $this->settings_prefix
     *
     * @param string $option Option key (not including $this->settings_prefix)
     * @param mixed $default (optional)
     *
     * @return mixed
     * @access public
     */
    public function getSetting($option, $default=null);

    /**
     * setSetting
     *
     * @param string $option
     * @param mixed $value
     *
     * @return bool (true if changed, false if fail or not changed)
     * @access public
     */
    public function setSetting($option, $value);


    /**
     * getCurrentTheme
     *
     * Gets current theme setting, falls back to default if Theme is missing.
     *
     * @return string
     * @access public
     */
    public function getCurrentTheme();

    /**
     * getCurrentThemeNamespace
     *
     * Return the namespace to the current Theme without the final theme class.
     *
     * @return string
     * @access public
     */
    public function getCurrentThemeNamespace();

    /**
     * setPrefix
     *
     * Set the settings prefix
     *
     * @param string $prefix
     *
     * @return self
     * @access public
     */
    public function setPrefix($prefix);
}

