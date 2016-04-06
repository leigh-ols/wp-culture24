<?php

$c24dates = array();
if (isset($_POST['c24'])) {
    if (!wp_verify_nonce($_POST['c24'], 'c24-test')) {
        die('Security check');
    }
    $c24dates = $this->testDates();
}
?>
<div class="wrap">
  <h2>Culture24 Dates Test Page</h2>
  <div id="c24-admin">
    <form id='c24-form' class="form-wrap" action="<?php print $_SERVER['REQUEST_URI']; ?>" method="POST">
<?php
if (function_exists('wp_nonce_field')) {
    wp_nonce_field('c24-test', 'c24');
}
?>
      <input class="button-primary" type="submit" name="c24_btn_test" id="c24_btn_test" value="Test" />
    </form>
    <div>
<?php
foreach ($c24dates as $test) {
?>
        <div>
        <p><strong><?php print_r($test['name']);
?></strong></p>
    <p><?php print_r($test['dates']);
?></p>
    <p><?php print_r($test['result']);
?></p>
          ============================================
        </div>
<?php

}
?>
    </div>
  </div>  <!--id="c24_admin"-->
</div>   <!--class="wrap" -->
