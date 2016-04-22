<?php
/**
 * page-venue.php
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
namespace c24;


$c24img = $c24venue->get_image_url();
$c24title = $c24venue->get_name();
$c24description = $c24venue->get_description();
$c24url = $c24venue->get_url();
$c24location = $c24venue->get_location_string();
//@TODO check up on this 'instance' mallarky,.. when is there more than one?
$c24charges = $c24venue->get_charges();

if (!$c24charges && !$c24venue->get_free()) {
    $c24charges = 'Free';
}
?>
<div class="c24venue">
    <div class="entry-header content-block">
        <h1 class="c24venue__title"><?php echo $c24title ?></h1>
    </div>
    <div class="entry-content content-block">
        <?php if (!empty($c24img)) : ?>
            <img src="<?php echo $c24img; ?>" alt="venue image" />
        <?php endif; ?>

        <h3 class="c24venue__heading">Information</h3>

        <dl>
            <dt>Address</dt>
            <dd><?php echo $c24location; ?></dd>
            <dt>Fee's</dt>
            <dd><?php echo $c24charges; ?></dd>
        </dl>
    </div>
    <div class="entry-content content-block">
        <p><?php echo $c24description; ?></p>
    </div>
    <div class="entry-content content-block">
        <h3 class="c24venue__heading">Links</h3>
        <?php
        foreach ($c24url as $url) {
            echo '<a href="'.$url.'">'.$url.'</a>';
        }
        ?>
    </div>

    <?php if ($venue_events) : ?>
        <div class="entry-content content-block">
            <h3 class="c24venue__heading">Upcoming Events</h3>
        </div>
        <?php //c24printevents($c24objects); ?>
        <?php // @TODO We have an array of events... just need to display them ?>
    <?php endif; ?>
</div>
