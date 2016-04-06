<?php
/**
 * Admin.php
 *
 * PHP Version 5.4
 *
 * @category
 * @package
 * @subpackage
 * @author     Leigh Bicknell <leigh@orangeleaf.com>
 * @license    Copyright Orangeleaf Systems Ltd 2013
 * @link       http://orangeleaf.com
 */

namespace c24\Admin;

/**
 * Class Admin
 *
 * @category
 * @package
 * @subpackage
 * @author     Leigh Bicknell <leigh@orangeleaf.com>
 * @license    Copyright Orangeleaf Systems Ltd 2013
 * @link       http://orangeleaf.com
 */
class Admin {
    protected $fallback_theme = '\c24\Themes\DefaultTheme\DefaultTheme';

    public $settings = array('theme', 'url', 'version', 'key', 'tag_text', 'tag_exact', 'epp', 'vfp', 'vpp');
    public $settings_prefix = 'c24api_';

    public function __construct() {
        if (is_admin()) {
            add_action('admin_menu', array($this, 'admin_menu'));
            add_action('admin_init', array($this, 'admin_init'));
        }
    }

    public function admin_menu() {
        add_menu_page('Culture24 Options', 'Culture24', 'manage_options', 'culture24', array($this, 'admin_options'));
        add_submenu_page('culture24', 'Events', 'Events', 'manage_options', 'events', array($this, 'admin_events'));
        add_submenu_page('culture24', 'Venues', 'Venues', 'manage_options', 'venues', array($this, 'admin_venues'));
        add_submenu_page('culture24', 'HTML', 'HTML', 'manage_options', 'html', array($this, 'admin_html'));
        add_submenu_page('culture24', 'Dates', 'Dates', 'manage_options', 'dates', array($this, 'admin_dates'));
    }

    public function admin_options() {
        include('culture24.admin.php');
    }

    public function admin_dates() {
        include('culture24.admin.dates.php');
    }

    public function admin_html() {
        include('culture24.admin.html.php');
    }

    public function admin_events() {
        include('culture24.admin.events.php');
    }

    public function admin_venues() {
        include('culture24.admin.venues.php');
    }

    public function admin_init() {
        register_setting('c24_options_group_api', 'c24', array($this, 'saveSettings'));
        add_settings_section(
            'settings_api', 'API Settings', array($this, 'print_section_info'), 'c24-settings-api'
        );
        add_settings_field(
            'c24_api_theme', 'Theme', array($this, 'create_field_api_theme'), 'c24-settings-api', 'settings_api'
        );
        add_settings_field(
            'c24_api_url', 'Base URL', array($this, 'create_field_api_url'), 'c24-settings-api', 'settings_api'
        );
        add_settings_field(
            'c24_api_version', 'Version', array($this, 'create_field_api_version'), 'c24-settings-api', 'settings_api'
        );
        add_settings_field(
            'c24_api_key', 'Key', array($this, 'create_field_api_key'), 'c24-settings-api', 'settings_api'
        );
        add_settings_field(
            'c24_api_tag_text', 'Listing Tag Text', array($this, 'create_field_api_tag_text'), 'c24-settings-api', 'settings_api'
        );
        add_settings_field(
            'c24_api_tag_exact', 'Listing Tag Exact', array($this, 'create_field_api_tag_exact'), 'c24-settings-api', 'settings_api'
        );
        add_settings_field(
            'c24_api_epp', 'Events per page', array($this, 'create_field_api_epp'), 'c24-settings-api', 'settings_api'
        );
        add_settings_field(
            'c24_api_efp', 'Events front page', array($this, 'create_field_api_efp'), 'c24-settings-api', 'settings_api'
        );
        add_settings_field(
            'c24_api_vpp', 'Partners per page', array($this, 'create_field_api_vpp'), 'c24-settings-api', 'settings_api'
        );
        add_settings_field(
            'c24_api_venue_id', 'Venue ID', array($this, 'create_field_api_venue_id'), 'c24-settings-api', 'settings_api'
        );
        add_settings_field(
            'c24_api_epp', 'Events per page', array($this, 'create_field_api_epp'), 'c24-settings-api', 'settings_api'
        );
        add_settings_field(
            'c24_api_efp', 'Event ID front page', array($this, 'create_field_api_efp'), 'c24-settings-api', 'settings_api'
        );
        add_settings_field(
            'c24_api_vpp', 'Partners per page', array($this, 'create_field_api_vpp'), 'c24-settings-api', 'settings_api'
        );
    }

    /**
     * Create the theme selector field
     *
     * @return void
     */
    public function create_field_api_theme() {
        $current_namespace = get_option('c24api_theme', $this->fallback_theme);
        $theme_paths = glob(CULTURE24__CONNECTOR_PATH . '/Themes/*', GLOB_ONLYDIR);

        $themes = array();
        foreach ($theme_paths as $theme) {
            $theme = basename($theme);
            $themes[$theme] = '\\c24\\Themes\\'.$theme.'\\'.$theme;
        }
        ?>
            <select name="c24[theme]">
                <?php foreach ($themes as $theme_name => $theme_namespace) : ?>
                    <?php //$theme = basename($theme); ?>
                    <option value="<?php echo $theme_namespace; ?>" <?php echo ($theme_namespace == $current_namespace ? 'selected="selected"' : ''); ?>><?php echo $theme_name; ?></option>
                <?php endforeach; ?>
            </select>
        <?php
    }

    public function create_field_api_url() {
        ?>
            <input type="text" id="input_c24api_url" name="c24[url]" value="<?php echo get_option('c24api_url', 'http://www.culture24.org.uk/api/rest/v'); ?>" size="128" />
        <?php
    }

    public function create_field_api_version() {
        ?>
            <input type="text" id="input_c24api_version" name="c24[version]" value="<?php echo get_option('c24api_version', '1'); ?>" size="2" />
        <?php
    }

    public function create_field_api_key() {
        ?>
            <input type="text" id="input_c24api_key" name="c24[key]" value="<?php echo get_option('c24api_key', ''); ?>" size="32" />
        <?php
    }

    public function create_field_api_tag_text() {
        ?>
            <input type="text" id="input_c24api_tag_text" name="c24[tag_text]" value="<?php echo get_option('c24api_tag_text', ''); ?>" size="128" />
        <?php
    }

    public function create_field_api_tag_exact() {
        ?>
            <input type="text" id="input_c24api_tag_exact" name="c24[tag_exact]" value="<?php echo get_option('c24api_tag_exact', ''); ?>" size="128" />
        <?php
    }

    public function create_field_api_venue_id() {
        ?>
            <input type="text" id="input_c24api_venue_id" name="c24[venue_id]" value="<?php echo get_option('c24api_venue_id', ''); ?>" size="128" />
        <?php
    }

    public function create_field_api_epp() {
        ?>
            <input type="text" id="input_c24api_epp" name="c24[epp]" value="<?php echo get_option('c24api_epp', '10'); ?>" size="2" />
        <?php
    }

    public function create_field_api_efp() {
        ?>
            <input type="text" id="input_c24api_efp" name="c24[efp]" value="<?php echo get_option('c24api_efp', ''); ?>" size="8" />
        <?php
    }

    public function create_field_api_vpp() {
        ?>
            <input type="text" id="input_c24api_vpp" name="c24[vpp]" value="<?php echo get_option('c24api_vpp', '25'); ?>" size="2" />
        <?php
    }

    public function print_section_info() {
        print 'Use shortcode [c24page]. Default tag(s) to filter all searchs. Leave blank, enter one word/phrase
            or comma delimited list of words/phrases.';
    }

    public function saveSettings($input) {
        $prefix = $this->settings_prefix;
        foreach ($this->settings as $v) {
            if (isset($input[$v])) {
                if (get_option($prefix . $v) === FALSE) {
                    add_option($prefix . $v, $input[$v]);
                } else {
                    update_option($prefix . $v, $input[$v]);
                }
            }
        }
        if (isset($input['venue_id'])) {
            if (get_option('c24api_venue_id') === FALSE) {
                add_option('c24api_venue_id', $input['venue_id']);
            } else {
                update_option('c24api_venue_id', $input['venue_id']);
            }
        }
        if (isset($input['epp'])) {
            if (get_option('c24api_epp') === FALSE) {
                add_option('c24api_epp', $input['epp']);
            } else {
                update_option('c24api_epp', $input['epp']);
            }
        }
        if (isset($input['efp'])) {
            if (get_option('c24api_efp') === FALSE) {
                add_option('c24api_efp', $input['efp']);
            } else {
                update_option('c24api_efp', $input['efp']);
            }
        }
        if (isset($input['vpp'])) {
            if (get_option('c24api_vpp') === FALSE) {
                add_option('c24api_vpp', $input['vpp']);
            } else {
                update_option('c24api_vpp', $input['vpp']);
            }
        }
        return $input;
    }

    public function get_option($option, $default=NULL) {
        return get_option($this->settings_prefix . $option, $default);
    }

    public function getTheme()
    {
        $theme_namespace = $this->get_option('theme', $this->fallback_theme);
        if (!class_exists($theme_namespace)) {
            $theme_namespace = $this->fallback_theme;
            update_option($this->settings_prefix . 'theme', $theme_namespace);
        }
        return $theme_namespace;
    }


    protected function viewEventDebug($obj, $full = FALSE) {
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
    protected function formatDates($dates, $format = 'd F Y') {
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
     *
     * @param array $date_array
     */
    protected function testDates()
    {
        $result = array();

        $dates = array(
            0 => array('startDate' => '29/07/2013', 'startTime' => '11:00', 'endDate' => '29/07/2013', 'endTime' => '15:00')
        );
        $arg = $this->makeTestDateArray($dates);
        $result[] = array(
            'name' => 'Single date with times',
            'dates' => $dates,
            'result' => $this->formatDates($arg),
        );

        $dates = array(
            0 => array('startDate' => '29/07/2013', 'startTime' => '10:00', 'endDate' => '29/07/2013', 'endTime' => '11:00'),
            1 => array('startDate' => '29/07/2013', 'startTime' => '12:00', 'endDate' => '29/07/2013', 'endTime' => '13:00'),
        );
        $arg = $this->makeTestDateArray($dates);
        $result[] = array(
            'name' => 'Single date with multiple times',
            'dates' => $dates,
            'result' => $this->formatDates($arg),
        );

        $dates = array(
            0 => array('startDate' => '29/07/2013', 'startTime' => '', 'endDate' => '29/07/2013', 'endTime' => ''),
        );
        $dates = $this->makeTestDateArray($dates);
        $result[] = array(
            'name' => 'Single date without times',
            'dates' => $dates,
            'result' => $this->formatDates($arg),
        );

        $dates = array(
            0 => array('startDate' => '26/07/2013', 'startTime' => '11:00', 'endDate' => '29/07/2013', 'endTime' => '15:00'),
        );
        $arg = $this->makeTestDateArray($dates);
        $result[] = array(
            'name' => 'Single date range with times',
            'dates' => $dates,
            'result' => $this->formatDates($arg),
        );

        $dates = array(
            0 => array('startDate' => '26/07/2013', 'startTime' => '', 'endDate' => '29/07/2013', 'endTime' => ''),
        );
        $arg = $this->makeTestDateArray($dates);
        $result[] = array(
            'name' => 'Single date range without times',
            'dates' => $dates,
            'result' => $this->formatDates($arg),
        );

        $dates = array(
            1 => array('startDate' => '20/07/2013', 'startTime' => '15:00', 'endDate' => '20/07/2013', 'endTime' => '15:30'),
            2 => array('startDate' => '27/07/2013', 'startTime' => '15:00', 'endDate' => '27/07/2013', 'endTime' => '15:30'),
        );
        $arg = $this->makeTestDateArray($dates);
        $result[] = array(
            'name' => 'Multiple single dates with times',
            'dates' => $dates,
            'result' => $this->formatDates($arg),
        );

        $dates = array(
            1 => array('startDate' => '05/07/2013', 'startTime' => '', 'endDate' => '05/07/2013', 'endTime' => ''),
            2 => array('startDate' => '12/07/2013', 'startTime' => '', 'endDate' => '12/07/2013', 'endTime' => ''),
        );
        $arg = $this->makeTestDateArray($dates);
        $result[] = array(
            'name' => 'Multiple single dates without times',
            'dates' => $dates,
            'result' => $this->formatDates($arg),
        );

        $dates = array(
            1 => array('startDate' => '05/07/2013', 'startTime' => '15:00', 'endDate' => '12/07/2013', 'endTime' => '15:30'),
            2 => array('startDate' => '18/07/2013', 'startTime' => '15:00', 'endDate' => '25/07/2013', 'endTime' => '15:30'),
        );
        $arg = $this->makeTestDateArray($dates);
        $result[] = array(
            'name' => 'Multiple date ranges with times',
            'dates' => $dates,
            'result' => $this->formatDates($arg),
        );

        $dates = array(
            1 => array('startDate' => '05/07/2013', 'startTime' => '', 'endDate' => '12/07/2013', 'endTime' => ''),
            2 => array('startDate' => '18/07/2013', 'startTime' => '', 'endDate' => '25/07/2013', 'endTime' => ''),
        );
        $arg = $this->makeTestDateArray($dates);
        $result[] = array(
            'name' => 'Multiple date ranges without times',
            'dates' => $dates,
            'result' => $this->formatDates($arg),
        );

        return $result;
    }

    protected function makeTestDateArray($dates)
    {
        $result = array();
        foreach ($dates as $v) {
            $result[] = (object) $v;
        }
        return $result;
    }

}
