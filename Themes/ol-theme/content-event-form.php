<script>
    jQuery(function() {
        jQuery( ".datepicker" ).datepicker();
    });
    $('#c24form__reset').click(function() {
        $('#c24form').reset();
    });
</script>
<div class="c24listoptions">
    <ul>
        <li><a href="/events/" title="Events list view"><i class="fa fa-list"></i> List View</a></li>
        <li><a href="/events/events-map/"><i class="fa fa-globe"></i> Map View</a></li>
    </ul>
</div>
<form id='c24form' class="form-wrap row pod searchform" action="<?php print strtok($_SERVER['REQUEST_URI'], '?'); ?>" method="GET">
    <div class="small-24">
        <div class="c24form__datestartc small-24 medium-12 columns">
            <label for="date-start" class="c24form__startlabel">Date Start</label>
            <input id="date-start" class="radius c24form__datestart datepicker" name="date-start" type="text" value="<?php echo $date_start; ?>" />
        </div>
        <div class="c24form__dateendc small-24 medium-12 columns">
            <label for="date-end" class="c24form__endlabel">Date End</label>
            <input id="date-end" class="radius c24form__dateend datepicker" name="date-end" type="text" value="<?php echo $date_end; ?>" />
        </div>

        <div class="c24form__audiencec small-24 medium-12 large-6 columns">
            <select id="audience" class="radius c24form__audience" name="audience">
                <option value="">Audience</option>
                <?php foreach ($c24audiences as $v) : ?>
                    <option value="<?php echo $v; ?>" <?php echo($_GET['audience'] == $v ? 'selected' : ''); ?> ><?php echo $v; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="c24form__typec small-24 medium-12 large-6 columns">
            <select id="type" class="radius c24form__type" name="type">
                <option value="">Type</option>
                <?php foreach ($c24types as $v) : ?>
                    <option value="<?php echo $v; ?>" <?php echo($_GET['type'] == $v ? 'selected' : ''); ?> ><?php echo $v; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="c24form__submitc small-24 medium-12 large-6 columns">
            <button class="radius prefix fake-prefix c24form__submit" style="border-radius:6px!important" type="submit" name="c24_btn_fetch" id="c24_btn_fetch">Search</button>
        </div>
        <div class="c24form__resetc small-24 medium-12 large-6 columns">
            <button class="radius prefix fake-prefix c24form__reset" style="border-radius:6px!important" type="reset" id="c24form__reset">Reset</button>
        </div>
    </div>
</form><!-- id="c24_form" -->
