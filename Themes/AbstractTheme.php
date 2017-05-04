<?php
/**
 * AbstractTheme.php
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

use c24\Service\Settings\SettingsInterface;
use c24\Service\Api\Culture24\Api as Api;
use c24\Service\Validator\ValidatorInterface;
use c24\Service\FormBuilder\FormBuilder;

/**
 * Class AbstractTheme
 *
 * @category
 * @package
 * @subpackage
 * @author     Leigh Bicknell <leigh@orangeleaf.com>
 * @license    Copyright Orangeleaf Systems Ltd 2013
 * @link       http://orangeleaf.com
 */
abstract class AbstractTheme implements ThemeInterface
{
    /**
     * We use the settings class to retrieve settings from WP
     *
     * @var SettingsInterface
     */
    protected $settings;

    /**
     * api
     *
     * Use getApi()
     *
     * @var Api
     */
    protected $api;

    /**
     * theme_path
     * Stores the path of the current implemented theme
     *
     * @var string
     */
    protected $theme_path;

    /**
     * Array of user input field keys accepted by this class
     *
     * @var array
     */
    protected $input_fields = array(
        'date_start',
        'date_end',
        'region',
        'audience',
        'type',
        'event',
        'venue'
    );

    /**
     * Input validation field rules (see the validator)
     *
     * @var array
     */
    protected $input_rules = array(
        'date_start' => 'regex,/[0-9]{2}\/[0-9]{2}\/[0-9]{4}/',
        'date_end'   => 'regex,/[0-9]{2}\/[0-9]{2}\/[0-9]{4}/',
        'region'     => 'regex,/[ 0-9a-zA-Z\-\(\)]+/',
        'audience'   => 'regex,/[ 0-9a-zA-Z\-\(\)]+/',
        'type'       => 'regex,/[ 0-9a-zA-Z\-\(\)]+/',
        'event'      => 'regex,/EVENT[0-9]+/',
        'venue'      => 'regex,/[A-Z]{2}[0-9]+/'
    );

    /**
     * Input validation field keys (see the validator)
     *
     * @var array
     */
    protected $input_filters = array(
        'date_start' => 'trim|sanitize_string',
        'date_end'   => 'trim|sanitize_string',
        'region'     => 'trim|sanitize_string',
        'audience'   => 'trim|sanitize_string',
        'type'       => 'trim|sanitize_string'
    );

    /**
     * sanitize_input
     *
     * Should we sanitize all user input?
     * Turning this on prevents input of arrays.
     *
     * @var mixed
     */
    protected $sanitize_input = true;

    /**
     * Stores user input. Must be set and accessed through setInput() to ensure
     * proper sanitation/validation
     *
     * @var array
     */
    private $input = array();

    /**
     * validator
     *
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * form_builder
     *
     * @var FormBuilder
     */
    protected $form_builder;

    /**
     * __construct
     *
     * @param SettingsInterface $settings
     * @param Api $api
     * @param ValidatorInterface $validator
     *
     * @return void
     * @access public
     */
    public function __construct(SettingsInterface $settings, Api $api, ValidatorInterface $validator, FormBuilder $form_builder)
    {
        $this->settings = $settings;
        $this->api = $this->decorateApi($api);
        $this->validator = $validator;
        $this->form_builder = $form_builder;

        $this->setInput($_REQUEST);


        // Get the path of $this
        $theme_reflection = new \ReflectionClass($this);
        $this->theme_path = dirname($theme_reflection->getFileName());

        add_shortcode('c24page', array($this, 'shortcode'));
        add_shortcode('c24slider', array($this, 'slider'));
        // @NOTE Race hazard.... WPCulture24 class fires on init...
        add_filter('init', array($this, 'feedHook'));

        if (method_exists($this, 'init')) {
            $this->init();
        }
    }

    /**
     * getApi
     *
     * @return Api
     * @access public
     */
    public function getApi()
    {
        return $this->api;
    }

    /**
     * getThemePath
     *
     * @return string
     * @access public
     */
    public function getThemePath()
    {
        return $this->theme_path;
    }

    /**
     * includeThemeFile
     * Include a file that exists in the themes directory
     *
     * @param string $file
     *
     * @return self
     * @access protected
     */
    protected function includeThemeFile($file, $vars = array())
    {
        // Make vars available to template file
        if (!isset($vars['input'])) {
            $vars['input'] = $this->getInput();
        }
        if (!isset($vars['validator'])) {
            $vars['validator'] = $this->validator;
        }
        if (!isset($vars['form'])) {
            $vars['form'] = $this->form_builder;
        }
        foreach ($vars as $k => $v) {
            ${$k} = $v;
        }

        include $this->getThemePath().'/'.$file;
        return $this;
    }

    /**
     * slider
     *
     *
     * @return void
     * @throws [ExceptionClass] [Description]
     * @access
     */
    public function slider()
    {
        ob_start();
        $this->displaySlider();
        return ob_get_clean();
    }

    /**
     * Display a page
     *
     * @TODO At the moment we're using _GET params and a shortcode to display the
     * pages... This is uuuugly. After primary refactor of code, consider using
     * Page class from dams-connector, allowing for url rewrites etc
     *
     * @return void
     */
    public function shortcode($atts, $content = '')
    {
        // Hack to force returning a string for now....
        ob_start();
        if ($event = $this->getInput('event')) {
            $this->displayEvent($event);
            return ob_get_clean();
        }

        if ($venue = $this->getInput('venue')) {
            $this->displayVenue($venue);
            return ob_get_clean();
        }

        $this->displayListing();
        return ob_get_clean();
    }

    /**
     * displayEvent
     *
     * @return void
     */
    public function displayEvent($event)
    {
        $options = array(
            'query_type' => CULTURE24_API_EVENTS
        );

        /** @var $obj Api */
        $obj = $this->getApi()->setOptions($options);
        if ($obj->requestID($event)) {
            $c24objects = $obj->getEvents();
            foreach ($c24objects as $c24event) {
                $this->includeThemeFile('page-event.php', array('c24event' => $c24event));
            }
        } else {
            $c24error = $obj->get_message();
        }
        return;
    }

    /**
     * displayVenue
     *
     * @return void
     */
    public function displayVenue($venue)
    {
        $options = array(
            'query_type' => CULTURE24_API_VENUES
        );

        /** @var $obj Api */
        $obj = $this->getApi()->setOptions($options);
        if ($obj->requestID($venue)) {
            $c24objects = $obj->getVenues();
            foreach ($c24objects as $c24venue) {
                // Get upcoming events for the venue
                $venue_events = false;
                $event_options = array(
                    'query_type' => CULTURE24_API_EVENTS,
                    'keyfield' => 'venueID',
                    'keyword'  => $venue
                );
                $obj = $this->getApi()->setOptions($event_options);
                if ($obj->requestSet()) {
                    $venue_events = $obj->getEvents();
                } else {
                    $c24error = $obj->get_message();
                }

                // Include the venue template, passing the venue obj and its
                // events
                $this->includeThemeFile(
                    'page-venue.php',
                    array(
                        'c24venue' => $c24venue,
                        'venue_events' => $venue_events
                    )
                );
            }
        } else {
            $c24error = $obj->get_message();
        }

        return;
    }

    /**
     * displayListing
     *
     * @return void
     */
    public function displayListing()
    {
        $obj = $this->setupListingApi();

        $c24perpage = $this->settings->getSetting('epp');
        $c24objects = array();
        $c24error = $c24debug = false;
        $date_start = $date_end = '';
        $c24regions = $this->getApi()->getRegions();
        $audiences = $this->getApi()->getAudiences();
        $types = $this->getApi()->getTypes();

        $form_vars = array(
            'date_start' => $date_start,
            'date_end' => $date_end,
            'audiences' => $audiences,
            'audience' => '',
            'types' => $types,
            'type' => ''
        );

        // Override defaults with validated+sanitized+filters user submitted
        // values
        $input = $this->getInput();
        foreach ($form_vars as $k => $v) {
            if (isset($input[$k])) {
                $form_vars[$k] = $input[$k];
            }
        }

        if ($obj->requestSet()) {
            $c24objects = $obj->getEvents();

            if ($date_range = $obj->get_dates()) {
                $date_start = str_replace('/', '-', substr($date_range, 0, strpos($date_range, ',')));
                $date_end = str_replace('/', '-', substr($date_range, strpos($date_range, ',') + 1));
            }
        } else {
            $c24error = $obj->get_message();
        }
        // @TODO move this to a theme file?
        ?>
            <div class="c24">

                <?php $this->includeThemeFile('content-event-form.php', $form_vars); ?>
                <?php $this->displayEvents($c24objects); ?>

                <?php //@TODO get real max number of results ?>
                <div class="pagination">
                    <?php echo $this->pager($obj->get_found(), $c24perpage); ?>
                </div>
                <div class="c24__logoc">
                    <img class="c24__logo" alt="Culture 24" src="/wp-content/plugins/wp-culture24/Themes/DefaultTheme/culture24-logo.png">
                    <p class="c24__logotext">Culture24 is our events data provider.</p>
                </div>
            </div>
        <?php

    }

    public function displaySlider()
    {
        $obj = $this->setupListingApi();
        $c24perpage = $this->settings->getSetting('epp');
        $c24objects = array();
        $date_start = $date_end = '';
        $c24regions = $this->getApi()->getRegions();
        $audiences = $this->getApi()->getAudiences();
        $types = $this->getApi()->getTypes();

        $form_vars = array(
            'date_start' => $date_start,
            'date_end' => $date_end,
            'audiences' => $audiences,
            'audience' => '',
            'types' => $types,
            'type' => ''
        );

        // Override defaults with validated+sanitized+filters user submitted
        // values
        $input = $this->getInput();
        foreach ($form_vars as $k => $v) {
            if (isset($input[$k])) {
                $form_vars[$k] = $input[$k];
            }
        }

        if ($obj->requestSet()) {
            $c24objects = $obj->getEvents();

            if ($date_range = $obj->get_dates()) {
                $date_start = str_replace('/', '-', substr($date_range, 0, strpos($date_range, ',')));
                $date_end = str_replace('/', '-', substr($date_range, strpos($date_range, ',') + 1));
            }
        } else {
            $c24error = $obj->get_message();
        }

        $this->includeThemeFile('content-slider.php', array('c24objects' => $c24objects));
    }

    /**
     * setupListingApi
     *
     * Set up our Api object's options for a listing based on our plugin options
     * and our search form's $_POST values
     *
     * @return Api
     */
    protected function setupListingApi($options = array())
    {
        global $paged;
        $limit = $this->settings->getSetting('epp');
        $tag_exact = $this->settings->getSetting('tag_exact');
        $tag_text = $this->settings->getSetting('tag_text');
        $venue_id = $this->settings->getSetting('venue_id');
        $user_id = $this->settings->getSetting('user_id');
        $offset = 0;

        if ($paged) {
            $offset = ($paged - 1) * $limit;
        }

        $options_settings = array(
            'query_type' => CULTURE24_API_EVENTS,
            'limit'      => $limit,
            'offset'     => (int)$offset,
            //'tag'      => 'mytag',
            'tagText'    => $tag_text,
            'tagExact'   => $tag_exact,
            //'elements' => @$_GET['elements'],
            //'keywords' => @$_GET['keywords'],
            //'keyfield' => @$_GET['keyfield'],
            'sort'       => 'date',
            'venueID'    => $venue_id,
            'userID'     => $user_id
        );
        $options_input = $this->getInput();

        // Merge all options
        $options = array_replace($options_settings, $options_input);

        /** @var $obj Api */
        $obj = $this->getApi()->setOptions($options);

        return $obj;
    }

    /**
     * displayEvents
     *
     * @param array $events
     *
     * @return void
     */
    public function displayEvents($events)
    {
        echo '<div class="c24events-list">';
        foreach ($events as $object) {
            $c24event = $object;
            $this->includeThemeFile('content-event.php', array('c24event' => $c24event));
        }
        echo '</div>';
    }

    /**
     * displayFeed
     * Should be called from feedHook
     *
     * @return void
     */
    public function displayFeed()
    {
        $obj = $this->setupListingApi();
        if ($obj->requestSet()) {
            echo $obj->get_data_raw();
        } else {
            echo $obj->get_message();
        }
    }

    /**
     * Display a feed
     * The feed needs to be output before any other, we use this to hook and
     * if the user wants a c24 feed.
     *
     * @return void
     */
    public function feedHook()
    {
        if (isset($_GET['c24rawfeed'])) {
            $this->displayFeed();
            die();
        }
    }

    /**
     * Compile a WP pagination string that can be printed in a template
     *
     * Example usage:
     * echo $this->pager($c24obj->get_found(), $_POST['limit']);
     *
     * @param type $total_items
     * @param type $per_page
     * @return string
     */
    public function pager($total_items, $per_page = 10)
    {
        $max = ceil($total_items / $per_page);
        $pages = '';

        if (!$current = get_query_var('paged')) {
            $current = 1;
        }

        $a['base'] = str_replace(999999999, '%#%', get_pagenum_link(999999999));
        $a['total'] = $max;
        $a['current'] = $current;
        $a['mid_size'] = 6;
        $total = 1; // 1 show the text "Page N of N", 0 - do not display
        $result = '';

        if ($total == 1 && $max > 1) {
            $pages .= ' Page' . $current . ' of ' . $max . '  ' . "<br/>\n";
        }

        $result .= $pages . paginate_links($a);
        return $result;
    }

    /**
     * Return validator+filtered user input.
     *
     * Accepts multiple parameters for specific user input keys.
     *
     * @param string ...$keys
     *
     * @return mixed
     * @access protected
     */
    protected function getInput()
    {
        $input = $this->input;

        // If we were passed arguments, get the relevant keys (if they exist)
        $keys = func_get_args();
        if (count($keys)) {
            foreach ($keys as $v) {
                if (isset($input[$v])) {
                    $input = $input[$v];
                } else {
                    return false;
                }
            }

            return $input;
        }

        // Return the entire array
        return $this->input;
    }

    /**
     * set the user input
     *
     * Strips unexpected input, sanitizes and then filters. Stores result in
     * $this->input, which can only be accessed through getInput() method.
     *
     * @param array $input
     *
     * @return self
     * @access protected
     */
    protected function setInput($input)
    {
        // Strip unexpected keys/values,
        $unsanitized_input = array();
        foreach ($this->input_fields as $v) {
            if (isset($input[$v])) {
                $unsanitized_input[$v] = $input[$v];
            }
        }


        $data = false;

        if ($unsanitized_input && $this->sanitize_input) {
            // Sanitize our data
            $data = $this->validator->sanitize($unsanitized_input);
        } else {
            $data = $unsanitized_input;
        }

        if ($data) {
            // Filter and validate our data
            $this->input = $this->validator->filter($data, $this->input_filters);
            $valid = $this->validator->validate($this->input, $this->input_rules);
            $this->form_builder->setErrors($this->validator->getErrors());
        }
    }

    /**
     * decorateApi
     *
     * Our api decorator allows our themes to modify the output of the API
     * functions.
     * It also decorates the events/venues recieved from the api, allowing
     * individual themes to modify the output of their functions also.
     *
     * @param Api $api
     *
     * @return Api
     * @access protected
     */
    protected function decorateApi($api)
    {
        $decorator_class = $this->settings->getCurrentThemeNamespace().'/ApiDecorator';
        if (!class_exists($decorator_class)) {
            $decorator_class = '\c24\Themes\ApiDecorator';
        }
        return new $decorator_class($api, $this->settings->getCurrentThemeNamespace());
    }
}
