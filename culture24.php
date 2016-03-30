<?php

$pwd = dirname(__FILE__);
//require_once $pwd . '/culture24.api.php';
//require_once $pwd . '/culture24.class.php';
//require_once $pwd . '/culture24.event.php';
//require_once $pwd . '/culture24.venue.php';

// LB
//require_once $pwd . '/ol-functions.php';

/**
 *
 */
function culture24_check_admin() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
}

/**
 *
 * @param type $filename
 */
function culture24_include_file($filename) {
    if (file_exists(dirname(__FILE__) . '/' . $filename)) {
        include( dirname(__FILE__) . '/' . $filename );
    } else {
        _e('<p>Failed to find ' . $filename . '</p>', 'culture24');
    }
}

// MISC USEFUL FUNCS

function c24_view_event_debug($obj, $full = FALSE) {
    $result = $obj->get_last_request() . '<br />'
        . 'status: ' . $obj->get_message() . '<br />'
        . 'total: ' . $obj->get_found() . '<br />'
        . 'validation errors: ' . print_r($obj->get_validation_errors(), 1) . '<br />';
    if ($full) {
        $result .= print_r($obj->get_records(), 1) . '<br /><br />';
    }
    return $result;
}

/**
 * Format dates
 *
 * @param array $date_array
 * @return array of formatted date strings
 */
function c24_format_dates($dates, $format = 'd F Y') {
    if (empty($dates)) {
        return '';
    } else {
        $unique = array();
        foreach ($dates as $k => $v) {
            $start = strtotime(str_replace('/', '-', $v->startDate));
            $end = strtotime(str_replace('/', '-', $v->endDate));
            if ($start == $end) {
                if (!in_array($start, $unique)) {
                    $dates[$k] = date($format, $start);
                    $unique[] = $start;
                } else {
                    unset($dates[$k]);
                }
            } else {
                $dates[$k] = date($format, $start) . ' - ' . date($format, $end);
            }
        }
    }
    return $dates;
}

/**
 * Is it a daily event?
 *
 * @param array $dates
 * @return boolean
 */
function c24_is_date_daily($dates) {
    if (isset($dates['value2']) && $dates['value2'] !== $dates['value']) {
        return TRUE;
    }
    return FALSE;
}

/**
 * Is it a one-day event?
 *
 * @param array $dates
 * @return boolean
 */
function c24_is_date_single($dates) {
    if (!isset($dates['value2']) || $dates['value2'] == $dates['value']) {
        if (!isset($dates['rrule'])) {
            return TRUE;
        }
    }
    return FALSE;
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
function c24_regions() {
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
function c24_foreignparts() {
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
function c24_audiences() {
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
function c24_types() {
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
