<?php
/**
 * AbstractAdmin.php
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

namespace c24\Admin;

use c24\Service\Settings\SettingsInterface;

/**
 * Class AbstractAdmin
 *
 * @category
 * @package
 * @subpackage
 * @author     Leigh Bicknell <leigh@orangeleaf.com>
 * @license    Copyright Orangeleaf Systems Ltd 2013
 * @link       http://orangeleaf.com
 */
class AbstractAdmin
{
    protected $settings;

    public function __construct(SettingsInterface $settings)
    {
        $this->settings = $settings;
    }

    /**
     * includeAdminFile
     *
     * @param string $file
     *
     * @return self
     * @access protected
     */
    protected function includeAdminFile($file, $vars = array())
    {
        // Make vars available to template file
        foreach ($vars as $k => $v) {
            ${$k} = $v;
        }

        include $file;
        return $this;
    }

    /**
     * saveSettings
     *
     * Called by WordPress when user clicks 'Save Changes' button in Dashboard
     *
     * @param mixed $input
     *
     * @return void
     * @access public
     */
    public function saveSettings($input)
    {
        // Loop through each of our settings and store the values
        foreach ($this->settings_fields as $v) {
            if (isset($input[$v])) {
                $this->settings->setSetting($v, $input[$v]);
            }
        }

        return $input;
    }


}

