<?php
global $c24event;
//var_dump($c24event);
$c24img = $c24event->get_image_url();
$c24title = $c24event->get_name();
$c24url = "?c24event=".$c24event->get_event_id();
$c24venue = $c24event->get_venue_name();
$c24venueurlopen = '<a href="?c24venue='.$c24event->get_venue_id().'">';
$c24venueurlclose = '</a>';
$c24description = $c24event->get_description();
$c24type = $c24event->get_type();
$c24location = $c24event->get_location_string();
//@TODO check up on this 'instance' mallarky,.. when is there more than one?
$c24sdate = $c24event->get_date_start(0);
$c24edate = $c24event->get_date_end(0);
$c24date = $c24sdate . ' - ' . $c24edate;
$c24charges = $c24event->get_charges();
if(!$c24charges && !$c24event->get_free())
{
    $c24charges = 'Free';
}
$c24description = $c24event->get_description();
if(!$c24description)
{
    $c24description = 'N/A';
}
$c24audience = $c24event->get_audience();
if(!$c24audience)
{
    $c24audience = 'N/A';
}
$c24charges = $c24event->get_charges();
if(!$c24charges && !$c24event->get_free())
{
    $c24charges = 'Free';
}
$c24registration = $c24event->get_registration();
if(!$c24registration)
{
    $c24registration = 'N/A';
}
$c24concessions = $c24event->get_concessions();
if(!$c24concessions)
{
    $c24concessions = 'N/A';
}
$c24eventurl = $c24event->get_url();
if(!$c24eventurl)
{
    $c24eventurl = 'N/A';
}
else
{
    $c24eventurl = '<a href="'.$c24eventurl.'">'.$c24eventurl.'</a>';
}
?>
<div class="c24fullevent">
    <div class="content-block">
        <h1 class="c24event__title"><?php echo $c24title; ?></h1>
    </div>
    <div class="content-block c24fullevent__details">
        <h3 class="c24event__venuetitle"><?php echo $c24venueurlopen.$c24venue.$c24venueurlclose; ?></h3>
        <h3 class="c24event__title"><?php echo $c24title; ?></h3>
        <dl>
            <dt>Type</dt>
            <dd><?php echo $c24type; ?></dd>
            <dt>Location</dt>
            <dd><?php echo $c24location; ?></dd>
            <dt>Date</dt>
            <dd><?php echo $c24date; ?></dd>
        </dl>
    </div>
    <div class="content-block c24fullevent__description"><p><?php echo $c24description; ?></p></div>
</div>

<div class="content-block c24fullevent__further">
    <dl>
        <dt>Suitable for</dt>
        <dd><?php echo $c24audience; ?></dd>
        <dt>Admission</dt>
        <dd><?php echo $c24charges; ?></dd>
        <dt>Registration</dt>
        <dd><?php echo $c24registration; ?></dd>
        <dt>Concessions</dt>
        <dd><?php echo $c24concessions; ?></dd>
        <dt>Website</dt>
        <dd><?php echo $c24eventurl; ?></dd>
    </dl>
</div>
