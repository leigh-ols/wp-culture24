<?php
/**
 * AbstractRecordDecorator.php
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

use c24\Service\Decorator\AbstractDecorator;
use c24\Service\Api\Culture24\RecordInterface;

/**
 * Class AbstractRecordDecorator

 * @category
 * @package
 * @subpackage
 * @author     Leigh Bicknell <leigh@orangeleaf.com>
 * @license    Copyright Orangeleaf Systems Ltd 2013
 * @link       http://orangeleaf.com
 *
 * @see AbstractDecorator
 * @abstract
 */
abstract class AbstractRecordDecorator extends AbstractDecorator
{
    protected $object;

    public function __construct(RecordInterface $object)
    {
        $this->object = $object;
    }


    /**
     * Shared views on Culture24 data
     */
    public function getAddress()
    {
        return '<address>'
          . $this->get_place() . ",\n "
          . $this->get_address_street() . ",\n "
          . $this->get_address_town() . ",\n"
          . $this->get_address_county() . ",\n"
          . $this->get_address_country() . ",\n"
          . $this->get_address_postcode()
          . '</address>';
    }

    /**
     *
     * @param object $event
     * @return string
     */
    public function getUrl()
    {
        return nl2br(trim($this->get_url()));
    }

    /**
     *
     * @param object $event
     * @return string
     */
    public function getType()
    {
        return nl2br($this->get_type());
    }

    /**
     *
     * @return string
     */
    public function getImageLarge()
    {
        $result = '';
        $image = $this->get_image_url_large();
        if (!empty($image)) {
            $result = '<br /><img src="' . $image . '"/>';
        }
        return $result;
    }

    /**
     *
     * @return string
     */
    public function charges()
    {
        return nl2br($this->get_charges());
    }

    /**
     * Seems like this could be replaced with simple:
     * address(); image();
     *
     * @return string
     */
    public function where($showpic = false)
    {
        $result = $this->get_address_string();
        if ($showpic) {
            $image = $this->get_image_url();
            if (!empty($image)) {
                $result .= '<br /><img src="' . $image . '"/>';
            }
        }
        return $result;
    }

    /**
     * @TODO look into the difference between get_link and get_url functions
     *
     * @return string
     */
    public function links()
    {
        $result = '';
        $c24 = $this->get_link();
        if (!empty($c24)) {
            $result .= '<span>&nbsp;<a href="' . $c24 . '" target="_blank">Culture24 page</a></span>';
        }
        $url = $this->get_url();
        if (!empty($url)) {
            $result .= '<span>&nbsp;<a href="' . $url . '" target="_blank">Event source page</a></span>';
        }
        return $result;
    }

    /**
     * Format dates
     *
     * @param array $date_array
     *
     * @return array of formatted date strings
     */
    public function formatDates($dates, $format = 'd F Y')
    {
        if (empty($dates)) {
            return '';
        } else {
            $unique = array();
            foreach ($dates as $k => $v) {
                $start = strtotime(str_replace('/', '-', $v->startDate));
                $end = strtotime(str_replace('/', '-', $v->endDate));
                if ($start == $end) {
                    if (!in_array($start, $unique)) {
                        $dates[$k] = date($format, $start);
                        $unique[] = $start;
                    } else {
                        unset($dates[$k]);
                    }
                } else {
                    $dates[$k] = date($format, $start) . ' - ' . date($format, $end);
                }
            }
        }
        return $dates;
    }
}
