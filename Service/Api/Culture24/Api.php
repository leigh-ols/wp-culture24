<?php
namespace c24\Service\Api\Culture24;

/**
 * @package Culture24
 *
 */

/**
 * Class that handles Culture24 requests and responses
 */
class Api
{

    /**
     *
     * @var string
     */
    protected $_url = '';

    /**
     *
     * @var string
     */
    protected $_key = '';

    /**
     *
     * @var string
     */
    protected $_api_version = '1';

    /**
     * @var string
     */
    protected $_message = '';

    /**
     * @var integer
     */
    protected $_attempt = 0;

    /**
     * @var boolean
     */
    protected $_error = false;

    /**
     * @var string
     */
    protected $_response = '';

    /**
     * @var integer
     */
    protected $_offset = 0;

    /**
     * @var integer
     */
    protected $_limit = 2;

    /**
     * @var string
     */
    protected $_date_start = false;

    /**
     * @var string
     */
    protected $_date_end = false;

    /**
     * @var string
     */
    protected $_date_end_default = CULTURE24_API_DATE_END_DEFAULT;

    /**
     * @var string
     */
    protected $_region = '';

    /**
     * @var string
     */
    protected $_audience = '';

    /**
     * @var string
     */
    protected $_type = '';

    /**
     * @var string
     */
    protected $_keywords = '';

    /**
     * @var string
     */
    protected $_keyfield = '';

    /**
     * @var string
     */
    protected $_name = '';

    /**
     * @var string
     */
    protected $_venueID = '';

    /**
     * @var string
     */
    protected $_userID = '';

    /**
     * @var string
     */
    protected $_sort = '';

    /**
     * @var string
     */
    protected $_tag = '';

    /**
     * @var string
     */
    protected $_tagExact = '';

    /**
     * @var string
     */
    protected $_tagText = '';

    /**
     * @var string
     */
    protected $_last_request = '';

    /**
     * @var string
     */
    protected $_request = '';

    /**
     * @var string
     */
    protected $_query_type = '';

    /**
     * @var  string
     */
    protected $_data_raw = '';

    /**
     * @var  array
     */
    protected $_data_parsed = array();

    /**
     * @var integer
     */
    protected $_found = 0;

    /**
     * @var string
     */
    protected $_records = array();

    /**
     * @var array of Event
     */
    protected $_objects = array();

    /**
     *
     * @var array
     */
    protected $_validation_errors = array();

    /**
     * Constructor
     * Query type defaults to events but can be overriden by options
     * @param type options
     */
    public function __construct($options = array())
    {
        $this->_query_type = CULTURE24_API_EVENTS;
        $this->_url = get_option('c24api_url', 'http://www.culture24.org.uk/api/rest/v');
        $this->_key = get_option('c24api_key', '');
        $this->_api_version = get_option('c24api_version', '1');

        if (!empty($options)) {
            $this->setOptions($options);
        }
    }

    /**
     * setOptions
     * Implemented to allow object re-use
     *
     * @param mixed[] $options
     *
     * @return self
     * @access public
     */
    public function setOptions($options)
    {
        if (isset($options['elements'])) {
            $this->set_elements($options['elements']);
            unset($options['elements']);
        }
        foreach ($options as $key => $val) {
            $this->{'_' . $key} = trim($val);
        }

        return $this;
    }

    /**
     * Return API search request query string with data format and key
     * @return string
     */
    protected function _get_base_search_query()
    {
        return $this->_url . $this->_api_version . '/' . $this->_query_type . '/?format=json&key=' . $this->_key;
    }

    /**
     * Return API single record request query string with data format and key
     * @param string $id
     * @return string
     */
    protected function _get_base_id_query($id)
    {
        return $this->_url . $this->_api_version . '/' . $this->_query_type . '/' . $id . '/?format=json&key=' . $this->_key;
    }

    /**
     * Set important properties to empty defaults
     */
    protected function _reset()
    {
        $this->_message = '';
        $this->_error = false;
        $this->_request = '';
        $this->_response = '';
        $this->_data_raw = '';
        $this->_data_parsed = array();
        $this->_found = 0;
        $this->_records = array();
        $this->_objects = array();
    }

    /**
     * Fetch a single event or venue by it's uniqueID
     * @param string $id
     * @return boolean
     */
    public function requestID($id)
    {
        $this->_reset();
        $this->_request = $this->_get_base_id_query($id);
        $this->_request .= $this->_get_request_arg('elements');
        if ($this->_request()) {
            $this->_found = 1;
            $this->_records[0] = $this->_data_parsed->result;
            $this->_objects = $this->_build_record_objects();
            return true;
        }
        return false;
    }

    /**
     * Getter for _data_raw
     * @return string
     */
    public function get_data_raw()
    {
        if ($this->_data_raw) {
            return $this->_data_raw;
        }
        return null;
    }

    /**
     * Fetch a set of records filtered by arguments
     * @return boolean
     */
    public function requestSet()
    {
        $this->_reset();
        $this->_request = $this->_get_base_search_query();
        $this->_request .= $this->_get_request_arg('limit');
        $this->_request .= $this->_get_request_arg('offset');
        $this->_request .= $this->_get_request_arg('tag');
        $this->_request .= $this->_get_request_arg('tagExact');
        $this->_request .= $this->_get_request_arg('tagText');
        $this->_request .= $this->_get_request_arg('type');
        $this->_request .= $this->_get_request_arg('name');
        $this->_request .= $this->_get_request_arg('elements');
        $this->_request .= $this->_get_request_arg('dates');
        $this->_request .= $this->_get_request_arg('keywords');
        $this->_request .= $this->_get_request_arg('audience');
        $this->_request .= $this->_get_request_arg('venueID');
        $this->_request .= $this->_get_request_arg('userID');
        $this->_request .= $this->_get_request_arg('sort');
        if ($this->_request()) {
            $this->_found = $this->_data_parsed->result->found;
            $this->_records = $this->_data_parsed->result->items;
            $this->_objects = $this->_build_record_objects();
            return true;
        }
        return false;
    }

    /**
     * Execute a Culture24 API http request with optional re-attempts on failure
     *
     * @return boolean
     */
    protected function _request()
    {

        // Echo the request url and clear the cache
        if (isset($_GET['dbg'])) {
            echo $this->_request;
            echo '<!-- Cache cleared -->';
            delete_transient(md5($this->_request));
            delete_transient(md5($this->_request.'_request'));
        }

        // Check if we have a valid cached version ( if if if... :/ )
        if (false !== ($this->_response = get_transient(md5($this->_request.'_request')))) {
            if (false !== ($this->_data_raw = get_transient(md5($this->_request)))) {
                if ($this->_data_parsed = $this->_parse_json()) {
                    return true;
                }
            }
        }
        try {
            $this->_error = false;
            $this->_attempt++;
            $this->_response = wp_remote_get($this->_request, array('timeout' => 20));
            if (isset($_GET['show_feed_url'])) {
                echo $this->_request;
            }
            $this->_error = wp_remote_retrieve_response_code($this->_response) !== 200;

            if (!$this->_error) {
                $this->_data_raw = wp_remote_retrieve_body($this->_response);
                if ($this->_data_parsed = $this->_parse_json()) {
                    if ($this->_data_parsed->success == 'true') {
                        if (isset($this->_response->errors)) {
                            if ($this->_attempt == CULTURE24_API_MAX_TRIES) {
                                $this->_set_error_message('result', $this->_data_parsed->result);
                            } else {
                                $this->_request();
                            }
                        } else {
                            $this->_set_message('response', $this->_response);
                        }
                    } else {
                        if ($this->_attempt == CULTURE24_API_MAX_TRIES) {
                            $this->_set_error_message('result', $this->_data_parsed->result);
                        } else {
                            $this->_request();
                        }
                    }
                }
            } else {
                $this->_set_message('response', $this->_response);
            }
        } catch (Exception $e) {
            $this->_set_error_message('exception', $e);
        }
        $this->_last_request = $this->_request;

        // Store the request and the raw data in cache for 2 hours
        set_transient(md5($this->_request.'_request'), $this->_response, 2 * 60 * 60);
        set_transient(md5($this->_request), $this->_data_raw, 2 * 60 * 60);
        return !$this->_error;
    }

    /**
     * JSON_ERROR handling only works with PHP >= 5.3.0
     *
     * @return string
     */
    protected function _parse_json()
    {
        $this->_data_parsed = null;
        try {
            $parsed = json_decode($this->_data_raw);
            //      switch (json_last_error()) {
            //        case JSON_ERROR_DEPTH:
            //          $this->_set_error_message('json', JSON_ERROR_DEPTH . ': Maximum stack depth exceeded');
            //          break;
            //        case JSON_ERROR_CTRL_CHAR:
            //          $this->_set_error_message('json', JSON_ERROR_CTRL_CHAR . ': Unexpected control character found');
            //          break;
            //        case JSON_ERROR_SYNTAX:
            //          $this->_set_error_message('json', JSON_ERROR_SYNTAX . ': Syntax error, malformed JSON');
            //          break;
            //        case JSON_ERROR_STATE_MISMATCH:
            //          $this->_set_error_message('json', JSON_ERROR_STATE_MISMATCH . ': Invalid or malformed JSON');
            //          break;
            //        case JSON_ERROR_UTF8:
            //          $this->_set_error_message('json', JSON_ERROR_UTF8 . ': Malformed UTF-8 characters, possibly incorrectly encoded');
            //          break;
            //        case JSON_ERROR_NONE:
            //        default:
            //          break;
            //      }
        } catch (Exception $e) {
            $this->_set_error_message('exception', $e);
        }
        return $parsed;
    }

    /**
     * Return a formatted query argument string for a given key
     * @param string $name
     * @return string
     */
    protected function _get_request_arg($name)
    {
        $result = '';
        switch ($name) {
        case 'dates':
            $dates = $this->get_dates();
            $result = (empty($dates) ? '' : '&q.date.range=' . $dates);
            break;
        case 'limit':
            $result = '&limit=' . $this->_limit;
            break;
        case 'offset':
            $result = '&offset=' . $this->_offset;
            break;
        case 'tag':
            $tags = '';
            if (!empty($this->_tag)) {
                $tags = $this->_tag . ',';
            }
            if (!empty($this->_region)) {
                $tags .= $this->_region;
            }
            $tags = trim($tags, ',');
            $tags = str_replace(' ', '+', $tags);
            $result = '&q.contentTag=' . $tags;
            break;
        case 'tagExact':
            $result = '&q.contentTag.exact='.$this->_tagExact;
            break;
        case 'tagText':
            $result = '&q.contentTag.text='.$this->_tagText;
            break;
        case 'type':
            if (!empty($this->_type)) {
                $result = '&q.type=' . str_replace(' ', '+', $this->_type);
            }
            break;
        case 'name':
            if (!empty($this->_name)) {
                $result = '&q.name=' . $this->_name;
            }
            break;
        case 'venueID':
            if (!empty($this->_venueID)) {
                $result = '&q.venueID=' . $this->_venueID;
            }
            break;
        case 'userID':
            if (!empty($this->_userID)) {
                $result = '&q.userID=' . $this->_userID;
            }
            break;
        case 'audience':
            if (!empty($this->_audience)) {
                $result = '&q.audience=' . str_replace(' ', '+', $this->_audience);
            }
            break;
        case 'sort':
            $result = '&sort=' . $this->_sort;
            break;
        case 'keywords':
            if (!empty($this->_keywords)) {
                $keywords = str_replace(' ', '+', $this->_keywords);
                if (empty($this->_keyfield)) {
                    $key = 'q';
                } else {
                    $key = 'q.' . $this->_keyfield;
                }
                $result = '&' . $key . '=' . $keywords;
            }
            break;
        case 'elements':
            $elems = $this->_get_elements();
            $result = (empty($elems) ? '' : '&elements=' . $elems);
            break;
        default:
            $result = '';
            break;
        }
        return $result;
    }

    /**
     * Format date range arguments for query string
     * Empty date-start defaults to today
     * Empty date-end defaults to CULTURE24_API_DATE_END_DEFAULT, override in constructor
     *
     * @return string
     */
    public function get_dates()
    {
        if ($this->_date_start && $this->_date_end) {
            $date_start = date(CULTURE24_API_DATE_FORMAT_OUTPUT, strtotime($this->_date_start));
            $date_end = date(CULTURE24_API_DATE_FORMAT_OUTPUT, strtotime($this->_date_end));
            return $date_start . ',' . $date_end;
        } elseif ($this->_date_start) {
            $date = strtotime($this->_date_start);
            $this->_date_end = $this->_date_end_default;
            return $this->get_dates();
        } elseif ($this->_date_end) {
            $this->_date_start = date(CULTURE24_API_DATE_FORMAT_INPUT);
            return $this->get_dates();
        } else {
            return '';
        }
    }

    /**
     * Return default data elements if not overriden by options/setter
     * @return string
     */
    protected function _get_elements()
    {
        if (!empty($this->_elements)) {
            return $this->_elements;
        }
        if ($this->_query_type == CULTURE24_API_EVENTS) {
            return Event::get_default_data_elements();
        } elseif ($this->_query_type == CULTURE24_API_VENUES) {
            return Venue::get_default_data_elements();
        }
    }

    /**
     * Return an array of class-specific objects
     * @return array
     */
    protected function _build_record_objects()
    {
        $result = array();
        if ($this->_query_type == CULTURE24_API_EVENTS) {
            $result = $this->_build_event_objects();
            $this->_store_event_validation_errors();
        } elseif ($this->_query_type == CULTURE24_API_VENUES) {
            $result = $this->_build_venue_objects();
        }
        return $result;
    }

    /**
     * Build an array of event objects
     * @return array
     */
    protected function _build_event_objects()
    {
        $result = array();
        foreach ($this->_records as $k => $v) {
            $event = new Event($v);
            $errors = $event->get_validation_errors();
            if (!empty($errors)) {
                $this->_validation_errors[$event->get_property('eventID')] = $errors;
            }
            $result[] = $event;
        }
        return $result;
    }

    /**
     * Build an array of venue objects
     * @return array
     */
    protected function _build_venue_objects()
    {
        $result = array();
        foreach ($this->_records as $k => $v) {
            $result[$v->uniqueID] = new Venue($v);
        }
        return $result;
    }

    /**
     *
     * @param string $val
     * @param string $field
     */
    public function set_keywords($val, $field = '')
    {
        $this->_keywords = trim($val);
        $this->_keyfield = $field;
    }

    /**
     *
     * @param string $val
     */
    public function set_date_start($val)
    {
        $this->_date_start = $val;
    }

    /**
     *
     * @param string $val
     */
    public function set_date_end($val)
    {
        $this->_date_end = $val;
    }

    /**
     *
     * @param string $val
     */
    public function set_limit($val)
    {
        $this->_limit = $val;
    }

    /**
     *
     * @param string $val
     */
    public function set_tag($val)
    {
        $this->_tag = trim($val);
    }

    /**
     *
     * @param string $val
     */
    public function set_type($val)
    {
        $this->_type = $val;
    }

    /**
     *
     * @param string $val
     */
    public function set_sort($val)
    {
        $this->_sort = trim($val);
    }

    /**
     *
     * @param string $val
     */
    public function set_region($val)
    {
        $this->_region = $val;
    }

    /**
     *
     * @param string $val
     */
    public function set_audience($val)
    {
        $this->_audience = $val;
    }

    /**
     *
     * @param string $val
     */
    public function set_name($val)
    {
        $this->_name = $val;
    }

    /**
     *
     * @param string $val
     */
    public function set_offset($val)
    {
        $this->_offset = $val;
    }

    /**
     * Sets data elements argument
     * Adds uniqueID if not found in list
     * @param string $val
     */
    public function set_elements($val)
    {
        if (!empty($val) && strpos($val, 'uniqueID') === false) {
            $val = 'uniqueID,' . $val;
        }
        $this->_elements = $val;
    }

    /**
     * Total number of query results (not records fetched)
     * @return boolean
     */
    public function get_found()
    {
        return $this->_found;
    }

    /**
     * Array of parsed records
     * @return array
     */
    public function get_records()
    {
        return $this->_records;
    }

    /**
     * Assigns an array of records
     * Uses offset and limit to reduce objects built
     * @param type $records
     * @param type $offset
     * @param type $limit
     */
    public function set_records($records, $offset = 0, $limit = 0)
    {
        if ($offset || $limit) {
            $this->_records = array_slice($records, $offset, $limit);
        } else {
            $this->_records = $records;
        }
        $this->_objects = $this->_build_record_objects();
    }

    /**
     * Return array of class objects
     * @return array
     */
    public function get_objects()
    {
        return $this->_objects;
    }

    /**
     * Returns last executed API request query string
     * @return string
     */
    public function get_last_request()
    {
        return $this->_last_request;
    }

    /**
     * Returns the last message set by a request
     * @return string
     */
    public function get_message()
    {
        return $this->_message;
    }

    /**
     * Returns the last error status set by a request
     * @return string
     */
    public function get_error()
    {
        return $this->_error;
    }

    /**
     * Sets the current message using data arg according to type
     * @param string $type
     * @param object $data
     */
    protected function _set_message($type, $data)
    {
        switch ($type) {
        case 'response':
            if ($code = wp_remote_retrieve_response_code($data)) {
                $this->_message = wp_remote_retrieve_response_code($data)
                    . ': ' . wp_remote_retrieve_response_message($data);
            } elseif (isset($data->errors)) {
                if (isset($data->errors['http_request_failed'])) {
                    $this->_message = 'Despite ' . $this->_attempt . ' attempts, Culture24 was unable to fulfill the request, please try again later.';
                } else {
                    $this->_message = print_r($data->errors, 1);
                }
            }
            break;
        default:
            $this->_message = '';
            break;
        }
    }

    /**
     * Sets the current error message using data arg according to type
     * @param string $type
     * @param object $data
     */
    protected function _set_error_message($type, $data)
    {
        switch ($type) {
        case 'result':
            $this->_message = $data->errorCode . ': ' . $data->errorMessage;
            break;
        case 'exception':
            $this->_message = $data->getCode() . ': ' . $data->getMessage();
            break;
        case 'json':
        default:
            $this->_message = $data;
            break;
        }
        $this->_error = true;
    }

    /**
     *
     * Validation was useful in the early days of the API
     *
     * @return array
     */
    public function get_validation_errors()
    {
        return $this->_validation_errors;
    }

    protected function _store_event_validation_errors()
    {
        //delete_transient('c24_event_validation_errors');
        if ($transient = get_transient('c24_event_validation_errors')) {
            $cache = array_merge($transient, $this->_validation_errors);
        } else {
            $cache = $this->_validation_errors;
        }
        return set_transient('c24_event_validation_errors', $cache, 7 * (60 * 60 * 24));
    }


    /*
     * LOOKUP DATA
     *
     * NOTES ON FILTERS IN QUERIES
     *
     * 1. At first glance it looks like the API can search on multiple filters
     * but it will only match on the first one, e.g.
     * &q.audience=Any+age&q.audience=Family+friendly matches on 'Any age' only.
     *
     * 2. Region values are held in the contentTag array in an implicit hierarchy.
     * Up to 3 values may or may not be present in any order and any positions in
     * the array, e.g.
     * contentTag: Array ( [0] => History and Heritage [1] => World War I [2] => England [3] => East of England [4] => Hertfordshire )
     * contentTag: Array ( [0] => England [1] => History and Heritage [2] => East of England [3] => World War I [4] => Hertfordshire )
     * contentTag: Array ( [0] => History and Heritage [1] => World War I [2] => England [3] => Hertfordshire )
     *
     * The API seems to execute a free text search for each of the terms across the
     * contentTags which means that "East of England" also matches
     * "England" + "South East", in other words it appears to match each of the
     * terms across all of the tags e.g.
     * a museum tagged Kent, South East and England turns up in the results for an "East of England" query
     */

    /**
     *
     * @return array
     */
    function getRegions() {
        return array(
            'East Midlands',
            'East of England',
            'London',
            'North East',
            'North West',
            'Northern Ireland',
            'Scotland',
            'South East',
            'South West',
            'Wales',
            'West Midlands',
            'Yorkshire',
        );
    }

    /**
     *
     * @return array
     */
    function getForeignparts() {
        return array(
            'Australia',
            'Austria',
            'Belgium ',
            'Bosnia And Herzegovina',
            'Canada',
            'Denmark',
            'Finland',
            'France',
            'Germany',
            'Hungary',
            'Iran',
            'Ireland',
            'Italy',
            'Namibia',
            'Netherlands',
            'New Zealand',
            'Poland',
            'Portugal',
            'Russian Federation',
            'Serbia',
            'Singapore',
            'Slovakia',
            'Slovenia',
            'South Africa',
            'Spain',
            'Sweden',
            'Switzerland',
            'Trinidad and Tobago',
            'United States',
        );
    }

    /**
     *
     * @return array
     */
    function getAudiences() {
        return array(
            'Any age',
            'Family friendly',
            '0-4',
            '5-6',
            '7-10',
            '11-13',
            '14-15',
            '16-17',
            '18+',
        );
    }

    /**
     *
     * @return array
     */
    function getTypes() {
        return array(
            'Late opening',
            'Lecture',
            'Exhibition (permanent)',
            'Exhibition (temporary)',
            'Event',
            'Guided tour',
            'Living history or re-enactment',
            'Performance ',
            'Seasonal event ',
            'Storytelling session',
            'Workshop or activity session',
        );
    }
}
