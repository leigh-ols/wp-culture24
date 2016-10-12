<?php
/**
 * AbstractDecorator.php
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

namespace c24\Service\Decorator;

/**
 * Class AbstractDecorator

 * @category
 * @package
 * @subpackage
 * @author     Leigh Bicknell <leigh@orangeleaf.com>
 * @license    Copyright Orangeleaf Systems Ltd 2013
 * @link       http://orangeleaf.com
 *
 * @abstract
 */
abstract class AbstractDecorator
{
    protected $object;

    /**
     * __construct
     *
     * @param mixed $object
     *
     * @return void
     * @throws [ExceptionClass] [Description]
     * @access
     */
    public function __construct($object)
    {
        $this->object = $object;
    }

    /**
     * __call
     *
     * @param mixed $method
     * @param mixed $args
     *
     * @return mixed
     * @access public
     */
    public function __call($method, $args)
    {
        return call_user_func_array(array($this->object, $method), $args);
    }

    /**
     * __get
     *
     * Magic method to get params from original object
     *
     * @param string $property
     *
     * @return mixed
     * @access public
     */
    public function __get($property)
    {
        $object = $this->getOriginalObject();
        if (property_exists($object, $property)) {
            return $object->$property;
        }
        return null;
    }

    /**
     * __set
     *
     * Magic method to set params on original object;
     *
     * @param string $property
     * @param mixed $value
     *
     * @return self
     * @access public
     */
    public function __set($property, $value)
    {
        $object = $this->getOriginalObject();
        $object->$property = $value;
        return $this;
    }

    /**
     * getOriginalObject
     *
     * Return original undecorated object, useful for nested decoration.
     *
     * @return RecordInterface
     * @access public
     */
    protected function getOriginalObject()
    {
        $object = $this->object;
        while ($object instanceof AbstractRecordDecorator) {
            $object = $object->getOriginalObject();
        }
        return $object;
    }
}
