<?php
/**
 * ThemeInterface.php
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

namespace c24\Themes;

use c24\Admin\Admin;
use c24\Service\Api\Culture24\Api;
use c24\Service\Validator\ValidatorInterface;

/**
 * Interface ThemeInterface
 *
 * @category
 * @package
 * @subpackage
 * @author     Leigh Bicknell <leigh@orangeleaf.com>
 * @license    Copyright Orangeleaf Systems Ltd 2013
 * @link       http://orangeleaf.com
 */
interface ThemeInterface
{
    public function __construct(Admin $admin, Api $api, ValidatorInterface $validator);

    /**
     * getAdmin
     *
     * @return c24\Admin\Admin
     * @access public
     */
    public function getAdmin();

    /**
     * setAdmin
     *
     * @param Admin $admin
     *
     * @return self
     * @access public
     */
    public function setAdmin(Admin $admin);
}

