<?php
/**
 * VenueDecorator.php
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

use c24\Service\Api\Culture24\Venue;

/**
 * Class VenueDecorator
 *
 * Themes will wrap c24\Service\Api\Culture24\Venue objects in decorators that
 * either extend or decorate this class. Why decorate here? Firstly we don't
 * want to start messing with the base Culture24 Api class code. Secondly it
 * allows a little more flexibility.
 *
 * @category
 * @package
 * @subpackage
 * @author     Leigh Bicknell <leigh@orangeleaf.com>
 * @license    Copyright Orangeleaf Systems Ltd 2013
 * @link       http://orangeleaf.com
 */
class VenueDecorator extends AbstractRecordDecorator
{
    protected $object;

    /**
     * __construct
     *
     * @return void
     * @access public
     */
    public function __construct(Venue $object)
    {
        $this->object = $object;
    }

    /**
     *
     * @return string
     */
    public function getUrl()
    {
        $result = '';
        $urls = (array) $this->get_url('webonly');
        foreach ($urls as $url) {
            $result .= '<a href="http://' . $url . '" target="_blank">' . $url . '</a><br />';
        }
        return $result;
    }

}
