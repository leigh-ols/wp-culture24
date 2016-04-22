<?php
/**
 * RecordInterface.php
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

namespace c24\Service\Api\Culture24;

/**
 * Class that handles generic Culture24 record
 */
interface RecordInterface
{
    /**
     * Constructor
     */
    public function __construct($record);

    public static function get_default_data_elements();

    /**
     * Purely for data quality control
     * @return array
     */
    public function get_validation_errors();

    /**
     *
     * @return stdClass
     */
    public function get_record();

    /**
     *
     * @param string $name
     * @param boolean $validate
     * @return mixed
     */
    public function get_property($name, $validate = false);

    /**
     *
     * @return string
     */
    public function get_unique_id();

    /**
     *
     * @return string
     */
    public function get_name();

    /**
     *
     * @return string
     */
    public function get_description();

    /**
     *
     * @return string
     */
    public function get_type();

    /**
     *
     * @return string
     */
    public function get_tags();

    /**
     *
     * @return string
     */
    public function get_address_street();

    /**
     *
     * @return string
     */
    public function get_address_town();

    /**
     *
     * @return string
     */
    public function get_address_county();

    /**
     *
     * @return string
     */
    public function get_address_country();

    /**
     *
     * @return string
     */
    public function get_address_postcode();

    /**
     * Format a complete address string
     *
     * @return string
     */
    public function get_address_string($delimiter = ', ');

    /**
     * Format a short location string town/county/country only
     *
     * @return string
     */
    public function get_location_string($delimiter = ', ');

    /**
     *
     * @return string
     */
    public function get_link();

    /**
     *
     * @return string
     */
    public function get_url();

    /**
     *
     * @return string
     */
    public function get_image_url();

    /**
     *
     * @return string
     */
    public function get_image_url_large();

    /**
     *
     * @return string
     */
    public function get_charges();

    /**
     *
     * @return string
     */
    public function get_message();

    /**
     *
     * @return string
     */
    public function get_error();
}

