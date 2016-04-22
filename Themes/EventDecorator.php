<?php
/**
 * EventDecorator.php
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

use c24\Service\Api\Culture24\Event;

/**
 * Class EventDecorator
 *
 * Themes will wrap c24\Service\Api\Culture24\Event objects in decorators that
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
class EventDecorator extends AbstractRecordDecorator
{
    protected $object;

    /**
     * __construct
     *
     * @return void
     * @access public
     */
    public function __construct(Event $object)
    {
        $this->object = $object;
    }

    /**
     *
     * @return string
     */
    public function getAudience()
    {
        $result = trim(nl2br($this->get_audience()), ',');
        if (empty($result)) {
            $result = 'Everyone';
        }
        return $result;
    }

    /**
     *
     * @return string
     */
    public function partnerUrl()
    {
        return '/partner?id=' . $this->get_venue_id();
    }

    /**
     *
     * @param integer $i
     * @return string
     */
    public function starts($i)
    {
        return '<time datetime="' . $this->get_date_start($i) . ' ' . $this->get_time_start($i) . '">'
          . $this->get_date_start($i) . ' ' . $this->get_time_start($i)
          . '</time>';
    }

    /**
     *
     * @param integer $i
     * @return string
     */
    public function ends($i)
    {
        return '<time datetime="' . $this->get_date_end($i) . ' ' . $this->get_time_end($i) . '">'
          . $this->get_date_end($i) . ' ' . $this->get_time_end($i)
          . '</time>';
    }

    /**
     * @TODO we're using c24_format_dates here... consider where to put this
     * function as it appears to be being used all over the place. Whos
     * responsibility is it to format the dates?
     *
     * @return string
     */
    public function dates()
    {
        $result = '';
        $dates = c24_format_dates($this->get_date_array(), 'd F Y');
        for ($i = 0; $i < count($dates); $i++) {
            $result .= $dates[$i] . ($i < count($dates) - 1 ? '<br />' : '');
        }
        return $result;
    }

    /**
     * @TODO integrate this with charges()
     *
     * @return string
     */
    public function free()
    {
        $result = '';
        if ($this->get_free() == 'Y') {
            $result = 'Free';
        }
        return $result;
    }

    /**
     *
     * @param object $event
     * @return string
     */
    public function registration($event)
    {
        $result = 'N/A';
        if ($event->get_registration() == 'Y') {
            $result = 'Registration required';
        }
        return $result;
    }

    /**
     *
     * @param object $event
     * @return string
     */
    public function concessions()
    {
        $result = 'N/A';
        if ($this->get_concessions() == 'Y') {
            $result = 'Concessions available';
        }
        return $result;
    }

    /**
     *
     * @return string
     */
    public function summary()
    {
        $result = $this->get_description_short();
        if (empty($result)) {
            $result = wp_trim_words($this->get_description(), 48);
        }
        return $result;
    }
}
