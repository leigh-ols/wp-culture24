<?php
namespace c24;

global $__c24, $c24objects, $c24pager, $c24error, $c24debug;

$c24objects = array();
$c24pager = '';
$c24error = $c24debug = false;
$date_start = $date_end = '';
$c24regions = $__c24->getApi()->getRegions();
$c24audiences = $__c24->getApi()->getAudiences();
$c24types = $__c24->getApi()->getTypes();

if (isset($_POST['c24'])) {
    if (!wp_verify_nonce($_POST['c24'], 'c24-test')) {
        die('Security check');
    }

    define('C24EVENTS_DEBUG', (isset($_REQUEST['debug']) ? 1 : 0));

    $options = array(
        'query_type' => CULTURE24_API_EVENTS,
        'date_start' => $_POST['date-start'],
        'date_end' => $_POST['date-end'],
        'offset' => $_POST['offset'],
        'limit' => $_POST['limit'],
        'tag' => $_POST['tag'],
        'elements' => $_POST['elements'],
        'keywords' => $_POST['keywords'],
        'keyfield' => $_POST['keyfield'],
        'region' => $_POST['region'],
        'audience' => $_POST['audience'],
        'type' => $_POST['type'],
        'sort' => 'date',
    );
    $obj = $__c24->getService('Culture24Api')->setOptions($options);

    if ($obj->requestSet()) {
        $c24objects = $obj->get_objects();

        if ($date_range = $obj->get_dates()) {
            $date_start = str_replace('/', '-', substr($date_range, 0, strpos($date_range, ',')));
            $date_end = str_replace('/', '-', substr($date_range, strpos($date_range, ',') + 1));
        }
    } else {
        $c24error = $obj->get_message();
    }
    $c24debug = $this->viewEventDebug($obj, isset($_REQUEST['debug-raw']));
}

wp_enqueue_script('jquery-ui-datepicker');
wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
?>

<div class="wrap">
  <div id="icon-options-general" class="icon32"><br /></div>
  <div id="c24-admin">
    <h2>Culture24 EVENTS</h2>
    <ul style="list-style-type: circle;">
      <li>Displayed in date sort order, as returned by the Culture24 API.</li>
      <li>If the elements list is empty, all data elements are specified in the query, including linked elements.</li>
      <li>If the elements list is not empty, uniqueID will be appended if it is not already specified.</li>
      <li>If both Date End and Date Start are empty, date range is not used by the query.</li>
      <li>If Date End is specified without Date Start, Date Start defaults to today.</li>
      <li>If Date Start is specified without Date End, Date End defaults to date-start+7 days.</li>
      <li>Tag and Keyword queries are case-insensitive.</li>
      <li>Search multiple Tags and Keywords by separating the phrases with commas,
        e.g. Tags: World War I,school+trips.</li>
      <li>As of v1.1 of the Culture24 API, query elements (Keywords Fields)
        are restricted to those shown in the drop down box. The address fields and
        descriptions are not searchable.</li>
      <li>Debug summary displays event validation details for fields:
        ID, name, description, type, tags, dates and all address-prefixed fields.</li>
    </ul>
<?php
include('theme/content-event-form.php');

if ($_REQUEST['map'] == '') {
    if ($c24debug) {
        include('theme/content-event-debug.php');
    }
} else {
    if (file_exists(WP_CONTENT_DIR . '/plugins/culture24/theme/gmap3.js')) {
        global $c24mapsize;
        $c24mapsize = $_REQUEST['map'];
        include('theme/functions-c24.php');
        include('theme/content-event-map.php');
    } else {
?>

        <h3>Javascript file <a href="https://github.com/jbdemonte/gmap3" target="_blank">gmap3.js</a> not found in culture24/theme folder</h3>

<?php

    }
}
?>

  </div> <!-- id="c24_admin" -->
</div>  <!-- class="wrap" -->
<script type="text/javascript">

jQuery(document).ready(function() {
    jQuery('.datepicker').datepicker({
        dateFormat: 'dd-mm-yy'
    });

});

</script>
