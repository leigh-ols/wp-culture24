<script>
    jQuery(function() {
        jQuery( ".datepicker" ).datepicker();
    });
    $('#c24form__reset').click(function() {
        $('#c24form').reset();
    });
</script>
<form id='c24form' class="form-wrap" action="<?php print strtok($_SERVER['REQUEST_URI'], '?'); ?>" method="GET">
    <div class="c24form__datestartc">
        <label for="date-start" class="c24form__startlabel">Date Start</label>
        <input id="date-start" class="c24form__datestart datepicker" name="date-start" type="text" value="<?php echo $date_start; ?>" />
    </div>
    <div class="c24form__dateendc">
        <label for="date-end" class="c24form__endlabel">Date End</label>
        <input id="date-end" class="c24form__dateend datepicker" name="date-end" type="text" value="<?php echo $date_end; ?>" />
    </div>

    <div class="c24form__audiencec">
        <label for="audience" class="c24form__audiencelabel">Audience</label>
        <select id="audience" class="c24form__audience" name="audience">
            <option value="">Audience</option>
            <?php foreach ($c24audiences as $v) : ?>
                <option value="<?php echo $v; ?>" <?php echo($_GET['audience'] == $v ? 'selected' : ''); ?> ><?php echo $v; ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="c24form__typec">
        <label for="type" class="c24form__typelabel">Type</label>
        <select id="type" class="c24form__type" name="type">
            <option value="">Type</option>
            <?php foreach ($c24types as $v) : ?>
                <option value="<?php echo $v; ?>" <?php echo($_GET['type'] == $v ? 'selected' : ''); ?> ><?php echo $v; ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="c24form__submitc">
        <button class="c24form__submit" type="submit" name="c24_btn_fetch" id="c24_btn_fetch">Search</button>
    </div>
    <div class="c24form__resetc">
        <button type="reset" id="c24form__reset" class="c24form__reset">Reset</button>
    </div>
</form><!-- id="c24_form" -->
