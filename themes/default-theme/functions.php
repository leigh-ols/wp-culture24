<?php
/**
 * enqueue js script
 *
 * @return void
 */
function c24script()
{
    wp_register_script('c24', '/wp-content/plugins/wp-culture24/js.js', array('jquery', 'jquery-ui-datepicker'));
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_script('c24');
}
add_action('wp_enqueue_scripts', 'c24script');

/**
 * enqueue theme styles
 *
 * @return void
 */
function c24style()
{
    // Styles are held in our theme.
}
add_action('wp_enqueue_styles', 'c24style');

/**
 * Display a page
 *
 * @return void
 */
function c24Page()
{
    if (isset($_GET['c24event'])) {
        c24DisplayEvent();
        return;
    }

    if (isset($_GET['c24venue'])) {
        c24DisplayVenue();
        return;
    }

    $obj = c24SetupListingObj();
    c24DisplayListing($obj);
    return;
}
add_shortcode('c24page', 'c24Page');

/**
 * Output a feed
 *
 * @return void
 */
function c24FeedHook()
{
    if (isset($_GET['c24rawfeed'])) {
        $obj = c24SetupListingObj();
        c24DisplayFeed($obj);
        die();
    }
}
add_filter('init', 'c24FeedHook');

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
 * c24DisplayEvent
 *
 * @return void
 */
function c24DisplayEvent()
{
    global $c24event;
    $options = array(
        'query_type' => CULTURE24_API_EVENTS
    );
    $obj = new Culture24API($options);
    if ($obj->requestID($_GET['c24event'])) {
        $c24objects = $obj->get_objects();
        foreach ($c24objects as $object) {
            $c24event = $object;
            include 'page-event.php';
        }
    } else {
        $c24error = $obj->get_message();
    }
    return;
}

/**
 * c24DisplayVenue
 *
 * @return void
 */
function c24DisplayVenue()
{
    global $c24venue;

    $options = array(
        'query_type' => CULTURE24_API_VENUES
    );

    $obj = new Culture24API($options);
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

/**
 * Enter description here...
 *
 * @return unknown
 * @modified   James G 2/5/2014 swapped tagexact and tagtext to force just East Sussex
 */
function c24SetupListingObj()
{
    global $paged;
    global $c24admin;

    $limit = $c24admin->get_option('epp');
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
    $obj = new Culture24API($options);

    return $obj;
}

/**
 * c24DisplayListing
 *
 * @param Culture24Api $obj Pre-setup Culture24Api object (using c24SetupObject)
 *
 * @return void
 */
function c24DisplayListing($obj)
{
    global $pages;
    global $c24admin;

    $c24perpage = $c24admin->get_option('epp');
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
    // jquery-ui style is included in our own theme css, uncomment if required
    //wp_enqueue_script('jquery-ui-datepicker');
    //wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
    ?>
    <div class="c24">

        <?php include 'content-event-form.php';
    ?>
        <?php c24printevents($c24objects);
    ?>

        <?php //@TODO get real max number of results ?>
        <div class="pagination">
            <?php echo c24pager($obj->get_found(), $c24perpage);
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
 * c24printevents
 *
 * @param mixed $events
 *
 * @return void
 */
function c24printevents($events)
{
    global $c24event;
    echo '<div class="c24events-list">';
    foreach ($events as $object) {
        $c24event = $object;
        include 'content-event.php';
    }
    echo '</div>';
}

/**
 * c24DisplayFeed
 *
 * @param mixed $obj
 *
 * @return void
 */
function c24DisplayFeed($obj)
{
    if ($obj->requestSet()) {
        echo $obj->get_data_raw();
    } else {
        echo $obj->get_message();
    }
}
