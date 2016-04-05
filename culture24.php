<?php

$pwd = dirname(__FILE__);
//require_once $pwd . '/culture24.api.php';
//require_once $pwd . '/culture24.class.php';
//require_once $pwd . '/culture24.event.php';
//require_once $pwd . '/culture24.venue.php';

// LB
//require_once $pwd . '/ol-functions.php';

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

