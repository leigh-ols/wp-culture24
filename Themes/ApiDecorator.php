<?php
/**
 * ApiDecorator.php
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

use c24\Service\Api\Culture24\Api;
use c24\Service\Decorator\AbstractDecorator;

/**
 * Class ApiDecorator
 *
 * This class adds getEvents() and getVenues() functionality.
 * It can be extended and overridden in a specific theme.
 *
 * @category
 * @package
 * @subpackage
 * @author     Leigh Bicknell <leigh@orangeleaf.com>
 * @license    Copyright Orangeleaf Systems Ltd 2013
 * @link       http://orangeleaf.com
 *
 * @see AbstractDecorator
 */
class ApiDecorator extends AbstractDecorator
{
    /**
     * object
     *
     * @var Api
     */
    protected $object;

    /**
     * current_theme_namespace
     *
     * @var string
     */
    protected $current_theme_namespace = '\c24\Themes';

    /**
     * construct
     *
     * @param mixed $object
     * @param mixed $current_theme_namespace
     *
     * @return void
     * @throws [ExceptionClass] [Description]
     * @access
     */
    public function construct($object, $current_theme_namespace = null)
    {
        $this->object = $object;
        if ($current_theme_namespace) {
            $this->current_theme_namespace = $current_theme_namespace;
        }
    }

    /**
     * getEvents
     *
     * @return array
     * @access public
     */
    public function getEvents()
    {
        return $this->decorateEvents($this->get_objects());
    }

    /**
     * getVenues
     *
     * @return array
     * @access public
     */
    public function getVenues()
    {
        return $this->decorateVenues($this->get_objects());
    }

    /**
     * decorateEvent
     *
     * Accepts single event or array of.
     * Decorates with Theme's EventDecorator, if that doesn't exist, decorates
     * with c24\Themes\EventDecorator instead
     *
     * @param c24\Service\Api\Culture24\Event $event[]
     *
     * @return mixed Decorated event(s)
     * @access protected
     */
    protected function decorateEvents($events)
    {
        $decorator_class = $this->current_theme_namespace.'\EventDecorator';
        if (!class_exists($decorator_class)) {
            $decorator_class = '\c24\Themes\EventDecorator';
        }

        return $this->decorate($events, $decorator_class);
    }

    /**
     * decorateVenue
     *
     * Accepts single venue or array of.
     * Decorates with Theme's VenueDecorator, if that doesn't exist, decorates
     * with c24\Themes\VenueDecorator instead
     *
     * @param c24\Service\Api\Culture24\Venue $venue[]
     *
     * @return mixed Decorate venue(s)
     * @access protected
     */
    protected function decorateVenues($venues)
    {
        $decorator_class = $this->current_theme_namespace.'\VenueDecorator';
        if (!class_exists($decorator_class)) {
            $decorator_class = '\c24\Themes\VenueDecorator';
        }

        return $this->decorate($venues, $decorator_class);
    }

    /**
     * Decorate wrap a single object with another.
     *
     * @param mixed $objects[] Objects to decorate
     * @param string $decorator Namespace of the class to decorate with.
     *
     * @return $decorator
     * @access protected
     */
    protected function decorate($objects, $decorator)
    {
        if (!is_array($objects)) {
            return new $decorator($objects);
        }

        foreach ($objects as $k => $object) {
            $objects[$k] = $this->decorate($object, $decorator);
        }

        return $objects;
    }
}
