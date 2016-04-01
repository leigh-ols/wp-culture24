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
     * admin
     * Private to prevent user themes from replacing with anything but an Admin class
     * Use getAdmin() and setAdmin() for access in implementations/themes
     *
     * @var Admin
     */
    private $admin;

    /**
     * api
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
    protected function includeThemeFile($file)
    {
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
        if (isset($_GET['c24event'])) {
            $this->displayEvent();
            return;
        }

        if (isset($_GET['c24venue'])) {
            $this->displayVenue();
            return;
        }

        $this->displayListing();
        return;
    }
    // @TODO refactor below here


    /**
     * displayEvent
     *
     * @TODO remove global $c24event, this is being used by the templates...
     * When we have proper pages instead of shortcode, we can just pass this as
     * a param to page constructor
     *
     * @return void
     */
    public function displayEvent()
    {
        global $c24event;
        $options = array(
            'query_type' => CULTURE24_API_EVENTS
        );

        /** @var $obj Api */
        $obj = $this->getApi()->setOptions($options);
        if ($obj->requestID($_GET['c24event'])) {
            $c24objects = $obj->get_objects();
            foreach ($c24objects as $object) {
                $c24event = $object;
                $this->includeThemeFile('page-event.php');
            }
        } else {
            $c24error = $obj->get_message();
        }
        return;
    }

    /**
     * displayVenue
     *
     * @TODO Remove c24venue (See displayEvent docblock for details)
     *
     * @return void
     */
    public function displayVenue()
    {
        global $c24venue;

        $options = array(
            'query_type' => CULTURE24_API_VENUES
        );

        /** @var $obj Api */
        $obj = $this->getApi()->setOptions($options);
        if ($obj->requestID($_GET['c24venue'])) {
            $c24objects = $obj->get_objects();
            foreach ($c24objects as $object) {
                $c24venue = $object;
                $this->includeThemeFile('page-venue.php');
            }
        } else {
            $c24error = $obj->get_message();
        }

        return;
    }

    /**
     * displayListing
     *
     * @TODO remove global $pages, refactor global calls
     * c24_regions/audiences/types etc
     *
     * @return void
     */
    public function displayListing()
    {
        global $pages;

        $obj = $this->setupListingApi();
        $c24perpage = $this->getAdmin()->get_option('epp');
        $c24objects = array();
        $c24error = $c24debug = false;
        $date_start = $date_end = '';
        $c24regions = c24_regions();
        $c24audiences = c24_audiences();
        $c24types = c24_types();

        if ($obj->requestSet()) {
            $c24pages = (int)floor(($obj->get_found() / $c24perpage) + 1);

            $c24objects = $obj->get_objects();

            if ($date_range = $obj->get_dates()) {
                $date_start = str_replace('/', '-', substr($date_range, 0, strpos($date_range, ',')));
                $date_end = str_replace('/', '-', substr($date_range, strpos($date_range, ',') + 1));
            }
        } else {
            $c24error = $obj->get_message();
        }
?>
    <div class="c24">

<?php $this->includeThemeFile('content-event-form.php');
?>
<?php $this->displayEvents($c24objects);
?>

        <?php //@TODO get real max number of results ?>
        <div class="pagination">
<?php echo $this->pager($obj->get_found(), $c24perpage);
?>
<?php $pages = $obj->get_found() / $c24perpage;
?>
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
     * Set up our Api object's options for a listing based on our plugin options
     * and our search form's $_POST values
     *
     *
     * @return Api
     * @modified   James G 2/5/2014 swapped tagexact and tagtext to force just East Sussex
     */
    protected function setupListingApi()
    {
        $limit = $this->getAdmin()->get_option('epp');
        $offset = 0;

        if ($paged) {
            $offset = $paged * $limit;
        }

        if ($paged) {
            $offset = ($paged - 1) * $limit;
        }

        $options = array(
            'query_type' => CULTURE24_API_EVENTS,
            'date_start' => @$_GET['date-start'],
            'date_end' => @$_GET['date-end'],
            'limit' => $limit,
            'offset' => (int)$offset,
            //'tag' => 'mytag',
            'tagText'=>'First+World+War+Centenary',
            'tagExact'=>'East+Sussex',
            //'elements' => @$_GET['elements'],
            //'keywords' => @$_GET['keywords'],
            //'keyfield' => @$_GET['keyfield'],
            'region' => @$_GET['region'],
            'audience' => @$_GET['audience'],
            'type' => @$_GET['type'],
            'sort' => 'date',
        );

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
        global $c24event;
        echo '<div class="c24events-list">';
        foreach ($events as $object) {
            $c24event = $object;
            $this->includeThemeFile('content-event.php');
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
     * global $c24pager
     * $c24pager = c24_pager($c24obj->get_found(), $_POST['limit']);
     *
     * @TODO Remove this, If the user doesn't want WordPress' pagination they
     * should use a plugin
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
