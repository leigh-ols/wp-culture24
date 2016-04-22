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

use c24\Admin\Admin;
use c24\Service\Api\Culture24\Api as Api;

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
     * We use the admin class to retrieve settings from WP
     *
     * @var Admin
     */
    private $admin;

    /**
     * api
     *
     * Use getApi()
     *
     * @var Api
     */
    private $api;

    /**
     * theme_path
     * Stores the path of the current implemented theme
     *
     * @var string
     */
    private $theme_path;

    public function __construct(Admin $admin, Api $api)
    {
        $this->admin = $admin;
        $this->api = $api;
        // Get the path of $this
        $theme_reflection = new \ReflectionClass($this);
        $this->theme_path = dirname($theme_reflection->getFileName());

        add_shortcode('c24page', array($this, 'shortcode'));
        // @NOTE Race hazard.... WPCulture24 class fires on init...
        add_filter('init', array($this, 'feedHook'));
    }

    /**
     * getAdmin
     *
     * @return Admin
     * @access public
     */
    public function getAdmin()
    {
        return $this->admin;
    }

    /**
     * setAdmin
     *
     * @param Admin $admin
     *
     * @return self
     * @access public
     */
    public function setAdmin(Admin $admin)
    {
        $this->admin = $admin;
        return $this;
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
        foreach ($vars as $k => $v) {
            ${$k} = $v;
        }

        include $this->getThemePath().'/'.$file;
        return $this;
    }

    /**
     * Display a page
     *
     * @TODO At the moment we're using _GET params and a shortcode to display the
     * pages... This is uuuugly. After primary refactor of code, consider using
     * Page class from dams-connector, allowing for url rewrites etc
     * @TODO If this is a shortcode it should return a string... not echo out
     *
     * @return void
     */
    public function shortcode($atts, $content = '')
    {
        // Hack to force returning a string for now....
        ob_start();
        if (isset($_GET['c24event'])) {
            $this->displayEvent();
            return ob_get_clean();
        }

        if (isset($_GET['c24venue'])) {
            $this->displayVenue();
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
    public function displayEvent()
    {
        $options = array(
            'query_type' => CULTURE24_API_EVENTS
        );

        /** @var $obj Api */
        $obj = $this->getApi()->setOptions($options);
        if ($obj->requestID($_GET['c24event'])) {
            $c24objects = $obj->get_objects();
            foreach ($c24objects as $object) {
                $c24event = $object;
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
    public function displayVenue()
    {
        $venue_id = $_GET['c24venue'];

        $options = array(
            'query_type' => CULTURE24_API_VENUES
        );

        /** @var $obj Api */
        $obj = $this->getApi()->setOptions($options);
        if ($obj->requestID($venue_id)) {
            $c24objects = $obj->get_objects();
            foreach ($c24objects as $object) {
                $c24venue = $object;

                // Get upcoming events for the venue
                $venue_events = false;
                $options = array(
                    'keyfield' => 'venueID',
                    'keyword'  => $venue_id
                );
                $obj = $this->getApi()->setOptions($event_options);
                if ($obj->requestSet()) {
                    $venue_events = $obj->get_objects();
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
        $c24perpage = $this->getAdmin()->getOption('epp');
        $c24objects = array();
        $c24error = $c24debug = false;
        $date_start = $date_end = '';
        $c24regions = $this->getApi()->getRegions();
        $c24audiences = $this->getApi()->getAudiences();
        $c24types = $this->getApi()->getTypes();

        if ($obj->requestSet()) {

            $c24objects = $obj->get_objects();

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

                <?php $this->includeThemeFile('content-event-form.php'); ?>
                <?php $this->displayEvents($c24objects); ?>

                <?php //@TODO get real max number of results ?>
                <div class="pagination">
                    <?php echo $this->pager($obj->get_found(), $c24perpage); ?>
                </div>
                <div class="c24__logoc">
                    <img class="c24__logo" alt="Culture 24" src="/wp-content/plugins/wp-culture24/themes/default-theme/culture24-logo.png">
                    <p class="c24__logotext">Culture24 is the cultural data provider for the First World War Centenary Programme events calendar.</p>
                </div>
            </div>
        <?php

    }

    /**
     * setupListingApi
     *
     * Set up our Api object's options for a listing based on our plugin options
     * and our search form's $_POST values
     *
     * @TODO Remove hard coded tags
     *
     * @return Api
     */
    protected function setupListingApi($options = array())
    {
        $limit = $this->getAdmin()->getOption('epp');
        $tag_exact = $this->getAdmin()->getOption('tag_exact');
        $tag_text = $this->getAdmin()->getOption('tag_text');
        $offset = 0;

        if ($paged) {
            $offset = $paged * $limit;
        }

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
        );
        $options_input = array(
            'date_start' => $_GET['date-start'],
            'date_end'   => $_GET['date-end'],
            'region'     => $_GET['region'],
            'audience'   => $_GET['audience'],
            'type'       => $_GET['type'],
        );

        // Merge all options
        $options = array_replace($options_settings, $options, $options_input);

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
     * @TODO Consider removing this, If the user doesn't want WordPress' pagination they
     * should use a plugin. (Dependant on if we keep pages as shortcodes)
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

}
