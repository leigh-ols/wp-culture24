<?php
/**
 * content-event.php
 *
 * PHP Version 5.2
 *
 * @category
 * @package
 * @subpackage
 * @author     Leigh Bicknell <leigh@orangeleaf.com>
 * @license    Copyright Orangeleaf Systems Ltd 2013
 * @link       http://orangeleaf.com
 */

global $c24event;
$c24img = $c24event->get_image_url();
/**
 * JAMES woz here 2/5/14 - thumbnails are shite
 */
if (strstr($c24img, 'thumb')) {
    $c24img = str_replace('thumb', 'medium', $c24img);
}

$c24title = $c24event->get_name();
//$c24url = $c24event->get_url();
$c24url = "?c24event=".$c24event->get_event_id();
$c24venue = $c24event->get_venue_name();
$c24type = $c24event->get_type();
$c24location = $c24event->get_location_string();
//@TODO check up on this 'instance' mallarky,.. when is there more than one?
$c24sdate = $c24event->get_date_start(0);
$c24edate = $c24event->get_date_end(0);
$c24date = $c24sdate . ' - ' . $c24edate;
$c24charges = trim($c24event->get_charges());
if (!$c24charges && $c24event->get_free()) {
    $c24charges = 'Free';
    $c24paid = false;
} else {
    $c24paid = true;
}
?>
<div class="content-block c24event">
    <?php if (!empty($c24img)) : ?>
        <div class="c24event__image">
            <img src="<?php echo $c24img; ?>" alt="image" />
        </div>
    <?php endif; ?>

    <div class="c24event__details">
        <span class="c24event__venue-name"><?php echo $c24venue; ?></span><br/>
        <span class="c24event__title"><?php echo $c24title; ?></span><br/>
        <span class="c24event__location"><?php echo $c24location; ?></span><br/>

        <span class="c24event__type"><span class="c24event__key">Type:</span> <?php echo $c24type; ?></span><br/>
        <span class="c24event__date"><span class="c24event__key">Date:</span> <?php echo $c24date; ?></span><br/>
        <span class="c24event__charges"><?php echo $c24charges; ?></span>
        <?php if ($c24url) : ?>
            <a class="c24event__go" href="<?php echo $c24url; ?>"><?php echo($c24paid ? 'Paid' : 'Free'); ?></a>
        <?php endif; ?>
    </div>
</div>
