<?php

namespace c24;

define('C24_SLUG', '/events');

// Remove page title 'events' from single event listing.
function c24RemoveTitleFromSingleEvent($title)
{
    if (isset($_GET['c24event'])) {
        if ($title == 'Events') {
            // This also removes title from menus etc!
            $title = 'Event';
        }
    }
    return $title;
}
add_filter('the_title', 'c24\c24RemoveTitleFromSingleEvent');

function c24Page()
{
    global $c24event;
    global $c24perpage;
    $c24perpage = get_option('c24api_epp');

    ob_start();
    if (isset($_GET['c24event'])) {
        c24DisplayEvent();
        return ob_get_clean();
    }
    if (isset($_GET['c24venue'])) {
        //@TODO consider venues (maybe own shortcode/page)
        c24DisplayVenue();
        return ob_get_clean();
    }
    $obj = c24SetupListingObj($c24perpage);
    c24DisplayListing($obj);
    return ob_get_clean();
}
add_shortcode('c24page', 'c24\c24Page');

function c24HomePage()
{
    ob_start();
    $event = get_option('c24api_efp');
    c24DisplayEvent($event);
    return ob_get_clean();
}
add_shortcode('c24homepage', 'c24\c24HomePage');

// This function requires ol-wp-theme
function c24Slider($args=array())
{
    ob_start();
    global $c24event;
    global $c24perpage;
    $c24perpage = get_option('c24api_epp');
    $obj = c24SetupListingObj(6);
    return c24DisplaySlider($obj);
}
add_shortcode('c24slider', 'c24\c24Slider');

function c24FeedHook()
{
    if (isset($_GET['c24rawfeed'])) {
        $obj = c24SetupListingObj($_GET['limit']);
        c24DisplayFeed($obj);
        die();
    }

    // c24prefetch == true means We need to get the data before the shortcode has been parsed
    // Think map pod on dams_connector plugin
    $c24prefetch = false;
    $c24prefetch = apply_filters('c24prefetch', $c24prefetch);
    if ($c24prefetch) {
        $obj = c24SetupListingObj();
        $obj->requestSet();
        $c24obj = $obj;
        do_action('c24obj', $c24obj);
    }
}
add_filter('init', 'c24\c24FeedHook', 99);

/**
 * Compile a WP pagination string that can be printed in a template
 *
 * Example usage:
 * global $c24pager
 * $c24pager = c24_pager($c24obj->get_found(), $_POST['limit']);
 *
 * @param type $total_items
 * @param type $per_page
 * @return string
 */
function c24pager($total_items, $per_page = 10)
{
    ob_start();
    OLWPT_PAGE_echoPagination(ceil($total_items / $per_page));
    return ob_get_clean();


    $max = ceil($total_items / $per_page);
    $pages = '';
    if (!$current = get_query_var('paged')) {
        if (!$current = get_query_var('page')) {
            $current = 1;
        }
    }
    $a['base'] = str_replace(999999999, '%#%', get_pagenum_link(999999999))."#c24events";
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

function c24DisplaySlider($obj)
{
    global $__slider_count;
    global $c24admin;
    $__slider_count++;
    $op = '';
    $content = '';
    $defaults = array();

    $c24 = $obj;
    $c24pages = 0;
    $c24perpage = 6;
    $c24objects = array();
    if ($c24->requestSet()) {
        $c24pages = (int)floor(($c24->get_found() / $c24perpage) + 1);

        $c24objects = $c24->get_objects();

        if ($date_range = $c24->get_dates()) {
            $date_start = str_replace(
                '/', '-', substr($date_range, 0, strpos($date_range, ','))
            );
            $date_end = str_replace(
                '/', '-', substr($date_range, strpos($date_range, ',') + 1)
            );
        }
    } else {
        $c24error = $c24->get_message();
    }

    $slides = array();


    if (empty($c24objects) && is_admin()) {
        $content='<div class="grid">
            <div class="inner">
            <div class="section-placeholder">
            <h1>No events</h1>
            <p>No events found!<br/>Note: Only admins can see this text.</p>
            </div>
            </div>
            </div>';
    }

    foreach ($c24objects as $k => $slide) {
        $c24event = $slide;
        $cur_date = time();
        foreach ($c24event->get_date_array() as $k => $v) {
            $c24sdate = $c24event->get_date_start($k);
            $c24edate = $c24event->get_date_end($k);
            $c24stime = $c24event->get_time_start($k);
            $c24etime = $c24event->get_time_end($k);
            $date_start = strtotime(str_replace('/', '-', $c24sdate));
            $date_end = strtotime(str_replace('/', '-', $c24edate));
            if ($date_end >= $cur_date) {
                if ($date_start == $date_end) {
                    $c24date = date('d F Y', $date_start).'<br/>';
                } else {
                    $c24date = date('d F Y', $date_start) . ' - ' . date('d F Y', $date_end) .'<br/>';
                }
                break;
            }
        }
        $linkopen = '<a href="'.C24_SLUG.'?c24event='.$slide->get_event_id().'">';
        $linkclose = '</a>';

        $content.='<li id="slide-'.$__slider_count.'-'.$k.'" class="slide clearfix fix"><div class="slide-cont fix">';
        $content.= '<div class="feat-img" data-fit="0" data-velocity="-.15">';
        $content.= $linkopen;

        $img_url = '/wp-content/plugins/wp-culture24/themes/default-theme/default.png';
        if ($slide->get_image_url()) {
            $img_url = $slide->get_image_url();
        }
        $content.='<img class="feat__image scrolly" src="'.$img_url.'" alt="'.$slide->get_name().'"/>';
        $content.= $linkclose;

        $content.='</div>';

        $content.= '<div class="feat-text">';

        $content.='<h2 class="feat-title">';
        $content.= $linkopen;
        $content.= $slide->get_name();
        $content.= $linkclose;
        $content.='</h2>';

        $content.='<span class="feat-meta">'.$c24date.'</span>';
        $content.='<p class="feat-excerpt">'.$slide->get_description_short().'</p>';
        $content.='<span class="morelink">'.$linkopen.'Find out more <span class="fa-stack fa-lg"><i class="fa fa-circle fa-stack-1x"></i><i style="color:#fff;" class="fa fa-angle-right fa-stack-1x"></i>'.$linkclose.'</span>';
        $content.='</div>';

        $content.='</div></li>';
    }

    if (isset($content)) {
        $op.= c24getFeatureHead();
        $op.= $content;
        $op.= c24getFeatureFoot();
    }

    return $op;
}
/**
 * c24getFeatureHead
 *
 * @return
 */
function c24getFeatureHead()
{
    global $__slider_count;
    return '<div id="c24flexslider-'.$__slider_count.'" class="flexslider c24">
        <ul class="slides">';
}

/**
 * c24getFeatureFoot
 *
 * @return string
 */
function c24getFeatureFoot()
{
    global $__slider_count;
    return '</ul>
        </div>
        <script> $("#c24flexslider-'.$__slider_count.'").flexslider({ animation: "slide" }); </script>';
}

function c24DisplaySliderFoundation($obj)
{
    $obj->requestSet();
    $c24objects = $obj->get_objects();

    //Feat head
    $op= '<div id="flexslider-events" class="flexslider events">
        <ul data-orbit class="slides">';


    // Slides
    foreach ($c24objects as $c24event) {
        //Slide head
        $op.= '<li data-orbit-slide="slide-event-'.$c24event->get_event_id().'" class="slide clearfix fix"><div class="slide-cont fix">';

        $c24img = $c24event->get_image_url_large();
        $c24title = $c24event->get_name();
        $c24url = "/events/?c24event=".$c24event->get_event_id().'#c24events';
        $c24venue = $c24event->get_venue_name();
        $c24type = $c24event->get_type();
        $c24location = $c24event->get_location_string();
        //@TODO check up on this 'instance' mallarky,.. when is there more than one?
        $c24sdate = $c24event->get_date_start(0);
        $c24edate = $c24event->get_date_end(0);
        $c24date = $c24sdate;
        if ($c24sdate != $c24edate) {
            $c24date = $c24sdate. ' - ' . $c24edate;
        }
        $c24charges = trim($c24event->get_charges());

        if (!$c24charges && $c24event->get_free()) {
            $c24charges = 'Free';
        }
        // Slide content
        $op.= '<div class="feat-img" data-fit="0" data-velocity="-.15">';
        if ($c24url) {
            $op.='<a href="'.$c24url.'" title="event">';
        }
        $op.='<img class="feat__image scrolly" src="'.$c24img.'" alt="'.$c24title.'"/>';
        if ($c24url) {
            $op.='</a>';
        }
        $op.='</div>';
        $op.= '<div class="feat-text">
            <div class="c24event__details">';
        $op.='<h2>Upcoming event...</h2>';
        if ($c24url) {
            $op.='<a href="'.$c24url.'" title="event">';
        }
        $op.='<h3 class="c24event__title">'.$c24title.'</h3>';
        if ($c24url) {
            $op.='</a>';
        }
        $op.='<!-- <h4 class="c24event__venue-name">'.$c24venue.'</h4> -->

            <h3 class="c24event__date">'.$c24date.'</h3>
            <h3 class="c24event__location">'.$c24location.'</h3>
            <span class="c24event__type">'.$c24type.'</span>
            <span class="c24event__charges">'.$c24charges.'</span>
            </div>';
        // Slide foot
        $op.='</div></div></li>';
    }
    $op.='
        </ul>
        </div>';
    return $op;
}

function c24DisplayEvent($eventID='')
{
    global $__c24,$c24event;
    $template = 'page-event.php';
    if (is_front_page()) {
        $template = 'page-front.php';
    }
    if (!$eventID) {
        $eventID = $_GET['c24event'];
    }
    // Temporary until we can inject this object
    $obj = $__c24->getService('Culture24Api')->setOptions($options);
    if ($obj->requestID($eventID)) {
        $c24objects = $obj->get_objects();
        foreach ($c24objects as $object) {
            $c24event = $object;
            include $template;
        }
    } else {
        $c24error = $obj->get_message();
    }
    return;
}

function c24DisplayVenue()
{
    global $__c24,$c24venue;

    $options = array(
        'query_type' => CULTURE24_API_VENUES
    );
    // Temporary until we can inject this object
    $obj = $__c24->getService('Culture24Api')->setOptions($options);
    if ($obj->requestID($_GET['c24venue'])) {
        $c24objects = $obj->get_objects();
        foreach ($c24objects as $object) {
            $c24venue = $object;
            include 'page-venue.php';
        }
    } else {
        $c24error = $obj->get_message();
    }

    return;
}

function c24SetupListingObj($limit=999)
{
    global $__c24;
    global $paged;
    global $c24event;
    global $c24perpage;
    global $c24pages;
    $offset = 0;
    $c24objects = array();

    if (!$paged = get_query_var('paged')) {
        if (!$paged = get_query_var('page')) {
            $paged = 1;
        }
    }

    $offset = ($paged - 1) * $c24perpage;

    $c24 = get_option('c24');


    $options = array(
        'query_type' => CULTURE24_API_EVENTS,
        'date_start' => @$_GET['date-start'],
        'date_end' => @$_GET['date-end'],
        'limit' => $limit,
        'offset' => (int)$offset,
        'tag' => $c24['tag'],
        'venueID' => $c24['venue_id'],
        //'tagExact'=>'doncaster',
        //'tagText'=>'',
        //'elements' => @$_GET['elements'],
        //'keywords' => @$_GET['keywords'],
        //'keyfield' => @$_GET['keyfield'],
        'region' => @$_GET['region'],
        'audience' => @$_GET['audience'],
        'type' => @$_GET['type'],
        'sort' => 'date',
    );
    // Temporary until we can inject this object
    $obj = $__c24->getService('Culture24Api')->setOptions($options);

    return $obj;
}


function c24DisplayListing($obj)
{
    global $c24perpage;
    global $c24pages;
    global $c24objects;

    $c24objects = array();
    $c24pager = '';
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
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
?>

    <div class="c24">

<?php
    include 'content-event-form.php';
    c24printevents($c24objects);

    //@TODO get real max number of results
    echo '<div class="pagination">';
    echo c24pager($obj->get_found(), $c24perpage);
    echo '</div>';

?>
    </div>
<?php

}

function c24printevents($events)
{
    global $c24event;
    echo '<div id="c24events" class="c24events-list row">';
    foreach ($events as $object) {
        $c24event = $object;
        include 'content-event.php';
    }
    echo '</div>';
}

function c24DisplayFeed($obj)
{
    if ($obj->requestSet()) {
        echo $obj->get_data_raw();
    } else {
        echo $obj->get_message();
    }
}
