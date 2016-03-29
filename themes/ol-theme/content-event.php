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

$c24img = $c24event->get_image_url_large();
$c24title = $c24event->get_name();
$c24url = "?c24event=".$c24event->get_event_id().'#c24events';
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
}
$class='';
if (!empty($c24img)) {
    $class='has-thumb has-post-thumbnail';
}
?>
<article class="c24event media event type-event hentry <?php echo $class; ?>">
    <?php if (!empty($c24img)) : ?>
        <a href="<?php echo $c24url; ?>" title="<?php echo $c24title; ?>">
            <img src="<?php echo $c24img; ?>" alt="event image" class="attachment-post-thumbnail wp-post-image" />
        </a>
    <?php endif; ?>
    <header>
        <h2 class="entry-title"><a href="<?php echo $c24url;?>" title="View event"><?php echo $c24title; ?></a></h2>
    </header>
        <div class="c24event__details entry-content">
            <!-- <h4 class="c24event__venue-name"><?php echo $c24venue; ?></h4> -->
            <span class="c24event__date"><?php echo $c24date; ?></span>
            <span class="c24event__location"><?php echo $c24location; ?></span>
            <span class="c24event__charges"><?php echo $c24charges; ?></span>
        </div>
    <footer></footer>
</article>
