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
 * Handle all things admin
 *
 * This class handles saving (and retrieval) of settings from WordPress, as
 * well as the rendering of the settings pages. A copy of this class is passed
 * to the front end Theme so it can quickly retrieve settings also.
 * At present this class has a functions-c24.php, which contains many of the
 * same functions as AbstractTheme. This duplication of code is not a good
 * idea. On the other hand we don't want to merge the two and end up with a
 * 'Super Class' with way too many responsibilities.
 * I'll have a think - 20160422 - LB
 *
 * @category
 * @package
 * @subpackage
 * @author     Leigh Bicknell <leigh@orangeleaf.com>
 * @license    Copyright Orangeleaf Systems Ltd 2013
 * @link       http://orangeleaf.com
 */
class Admin
{
    protected $fallback_theme = '\c24\Themes\DefaultTheme\DefaultTheme';

    /**
     * settings field keys
     *
     * @var array
     */
    public $settings = array('theme', 'url', 'version', 'key', 'tag_text', 'tag_exact', 'epp', 'vfp', 'vpp');

    /**
     * Settings field keys are prefixed with this string
     *
     * @var string
     */
    public $settings_prefix = 'c24api_';

    /**
     * __construct
     *
     *
     * @return void
     * @access public
     */
    public function __construct()
    {
        if (is_admin()) {
            add_action('admin_menu', array($this, 'adminMenu'));
            add_action('admin_init', array($this, 'adminInit'));
        }
    }

    /**
     * adminMenu
     *
     * Called by WordPress action 'admin_menu'.
     * Adds Dashboard pages.
     *
     * @return void
     * @access public
     */
    public function adminMenu()
    {
        add_menu_page('Culture24 Options', 'Culture24', 'manage_options', 'culture24', array($this, 'adminOptions'));
        add_submenu_page('culture24', 'Events', 'Events', 'manage_options', 'events', array($this, 'adminEvents'));
        add_submenu_page('culture24', 'Venues', 'Venues', 'manage_options', 'venues', array($this, 'adminVenues'));
        add_submenu_page('culture24', 'HTML', 'HTML', 'manage_options', 'html', array($this, 'adminHtml'));
        add_submenu_page('culture24', 'Dates', 'Dates', 'manage_options', 'dates', array($this, 'adminDates'));
    }

    /**
     * adminOptions
     *
     * Main options page
     *
     * @return void
     * @access public
     */
    public function adminOptions()
    {
        include('culture24.admin.php');
    }

    /**
     * adminDates
     *
     * Dates test page
     *
     * @return void
     * @access public
     */
    public function adminDates()
    {
        include('culture24.admin.dates.php');
    }

    /**
     * adminHtml
     *
     * Html test page
     *
     * @return void
     * @access public
     */
    public function adminHtml()
    {
        include('culture24.admin.html.php');
    }

    /**
     * adminEvents
     *
     * Events test page
     *
     * @return void
     * @access public
     */
    public function adminEvents()
    {
        include('culture24.admin.events.php');
    }

    /**
     * adminVenues
     *
     * Venue test page
     *
     * @return void
     * @access public
     */
    public function adminVenues()
    {
        include('culture24.admin.venues.php');
    }

    /**
     * adminInit
     *
     * Called by WordPress action 'admin_init'.
     * Adds hooks for Dashboard settings sections and fields.
     *
     * @return void
     * @access public
     */
    public function adminInit()
    {
        register_setting('c24_options_group_api', 'c24', array($this, 'saveSettings'));
        add_settings_section(
            'settings_api', 'API Settings', array($this, 'printSectionInfo'), 'c24-settings-api'
        );
        add_settings_field(
            'c24_api_theme', 'Theme', array($this, 'createFieldTheme'), 'c24-settings-api', 'settings_api'
        );
        add_settings_field(
            'c24_api_url', 'Base URL', array($this, 'createFieldUrl'), 'c24-settings-api', 'settings_api'
        );
        add_settings_field(
            'c24_api_version', 'Version', array($this, 'createFieldVersion'), 'c24-settings-api', 'settings_api'
        );
        add_settings_field(
            'c24_api_key', 'Key', array($this, 'createFieldKey'), 'c24-settings-api', 'settings_api'
        );
        add_settings_field(
            'c24_api_tag_text', 'Listing Tag Text', array($this, 'createFieldTagText'), 'c24-settings-api', 'settings_api'
        );
        add_settings_field(
            'c24_api_tag_exact', 'Listing Tag Exact', array($this, 'createFieldTagExact'), 'c24-settings-api', 'settings_api'
        );
        add_settings_field(
            'c24_api_epp', 'Events per page', array($this, 'createFieldEpp'), 'c24-settings-api', 'settings_api'
        );
        add_settings_field(
            'c24_api_efp', 'Events front page', array($this, 'createFieldEfp'), 'c24-settings-api', 'settings_api'
        );
        add_settings_field(
            'c24_api_vpp', 'Partners per page', array($this, 'createFieldVpp'), 'c24-settings-api', 'settings_api'
        );
        add_settings_field(
            'c24_api_venue_id', 'Venue ID', array($this, 'createFieldVenueId'), 'c24-settings-api', 'settings_api'
        );
        add_settings_field(
            'c24_api_epp', 'Events per page', array($this, 'createFieldEpp'), 'c24-settings-api', 'settings_api'
        );
        add_settings_field(
            'c24_api_efp', 'Event ID front page', array($this, 'createFieldEfp'), 'c24-settings-api', 'settings_api'
        );
        add_settings_field(
            'c24_api_vpp', 'Partners per page', array($this, 'createFieldVpp'), 'c24-settings-api', 'settings_api'
        );
    }

    /**
     * Create the theme selector field
     *
     * @return void
     */
    public function createFieldTheme()
    {
        $current_namespace = $this->getOption('theme', $this->fallback_theme);
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
                    <option value="<?php echo $theme_namespace;
        ?>" <?php echo($theme_namespace == $current_namespace ? 'selected="selected"' : '');
        ?>><?php echo $theme_name;
        ?></option>
                <?php endforeach;
        ?>
            </select>
        <?php

    }

    /**
     * createFieldUrl
     *
     * Create the Api URL field.
     *
     * @return void
     * @access public
     */
    public function createFieldUrl()
    {
        ?>
            <input type="text" id="input_c24api_url" name="c24[url]" value="<?php echo $this->getOption('url', 'http://www.culture24.org.uk/api/rest/v');
        ?>" size="128" />
        <?php

    }

    /**
     * createFieldVersion
     *
     * Create the API version field
     *
     * @return void
     * @access public
     */
    public function createFieldVersion()
    {
        ?>
            <input type="text" id="input_c24api_version" name="c24[version]" value="<?php echo $this->getOption('version', '1');
        ?>" size="2" />
        <?php

    }

    /**
     * createFieldKey
     *
     * Create the API Key field
     *
     * @return void
     * @access public
     */
    public function createFieldKey()
    {
        ?>
            <input type="text" id="input_c24api_key" name="c24[key]" value="<?php echo $this->getOption('key', '');
        ?>" size="32" />
        <?php

    }

    /**
     * createFieldTagText
     *
     * Create the Listing 'TagText' field
     *
     * @return void
     * @access public
     */
    public function createFieldTagText()
    {
        ?>
            <input type="text" id="input_c24api_tag_text" name="c24[tag_text]" value="<?php echo $this->getOption('tag_text', '');
        ?>" size="128" />
        <?php

    }

    /**
     * createFieldTagExact
     *
     * Create the Listing 'TagExact' field
     *
     * @return void
     * @access public
     */
    public function createFieldTagExact()
    {
        ?>
            <input type="text" id="input_c24api_tag_exact" name="c24[tag_exact]" value="<?php echo $this->getOption('tag_exact', '');
        ?>" size="128" />
        <?php

    }

    /**
     * createFieldVenueId
     *
     * Create the Listing 'VenueID' field to limit searches to specific venue.
     *
     * @return void
     * @access public
     */
    public function createFieldVenueId()
    {
        ?>
            <input type="text" id="input_c24api_venue_id" name="c24[venue_id]" value="<?php echo $this->getOption('venue_id', '');
        ?>" size="128" />
        <?php

    }

    /**
     * createFieldEpp
     *
     * Create the Events Per Page field
     *
     * @return void
     * @access public
     */
    public function createFieldEpp()
    {
        ?>
            <input type="text" id="input_c24api_epp" name="c24[epp]" value="<?php echo $this->getOption('epp', '10');
        ?>" size="2" />
        <?php

    }

    /**
     * createFieldEfp
     *
     * Create the Event Front Page field.
     *
     * @return void
     * @access public
     */
    public function createFieldEfp()
    {
        ?>
            <input type="text" id="input_c24api_efp" name="c24[efp]" value="<?php echo $this->getOption('efp', '');
        ?>" size="8" />
        <?php

    }

    /**
     * createFieldVpp
     *
     * Create the Partners per page field (what?)
     *
     * @return void
     * @access public
     */
    public function createFieldVpp()
    {
        ?>
            <input type="text" id="input_c24api_vpp" name="c24[vpp]" value="<?php echo $this->getOption('vpp', '25');
        ?>" size="2" />
        <?php

    }

    /**
     * printSectionInfo
     *
     * Print main options info (Rename/refactor or something
     * 'printSectionInfo' is ambiguous.
     *
     * @return void
     * @access public
     */
    public function printSectionInfo()
    {
        print 'Use shortcode [c24page]. Default tag(s) to filter all searchs. Leave blank, enter one word/phrase
            or comma delimited list of words/phrases.';
    }

    /**
     * saveSettings
     *
     * Called by WordPress when user clicks 'Save Changes' button in Dashboard
     *
     * @param mixed $input
     *
     * @return void
     * @access public
     */
    public function saveSettings($input)
    {
        // Loop through each of our settings and store the values
        foreach ($this->settings as $v) {
            if (isset($input[$v])) {
                if ($this->getOption($v) === false) {
                    $this->addOption($v, $input[$v]);
                } else {
                    $this->updateOption($v, $input[$v]);
                }
            }
        }

        return $input;
    }

    /**
     * getOption
     *
     * Retrieves a setting/option from WordPress automatically adding $this->settings_prefix
     *
     * @param string $option Option key (not including $this->settings_prefix)
     * @param mixed $default (optional)
     *
     * @return mixed
     * @access public
     */
    public function getOption($option, $default=null)
    {
        return get_option($this->settings_prefix . $option, $default);
    }

    /**
     * addOption
     *
     * Wrapper for add_option, automatically adds 'settings_prefix'.
     *
     * @param string $option
     * @param mixed $value
     *
     * @return boolean (success)
     * @access public
     */
    public function addOption($option, $value)
    {
        return add_option($this->settings_prefix . $option, $value);
    }

    /**
     * updateOption
     *
     * Wrapper for WordPress update_option function. Automatically adds
     * 'settings_prefix'.
     *
     * @param string $option
     * @param mixed $value
     *
     * @return bool (true if changed, false if fail or not changed)
     * @access public
     */
    public function updateOption($option, $value)
    {
        return update_option($this->settings_prefix . $option, $value);
    }

    /**
     * getTheme
     *
     * Gets current theme setting, falls back to default if Theme is missing.
     *
     * @return string
     * @access public
     */
    public function getTheme()
    {
        $theme_namespace = $this->getOption('theme', $this->fallback_theme);
        if (!class_exists($theme_namespace)) {
            // @TODO add some sort of admin alert here
            $theme_namespace = $this->fallback_theme;
            update_option($this->settings_prefix . 'theme', $theme_namespace);
        }
        return $theme_namespace;
    }

    /**
     * getThemeNamespace
     *
     * Return the namespace to the current Theme
     *
     * @return string
     * @access public
     */
    public function getThemeNamespace()
    {
        // Get the Theme
        $theme = $this->getTheme();

        // Remove the Theme Class name from the theme to return just the
        // namespace
        // c24\Themes\DefaultTheme\DefaultTheme
        // becomes:
        // c24\ThemesDefaultTheme
        $theme_namespaces = explode('\\', $theme);
        array_pop($theme_namespaces);
        $theme_namespace = implode('\\', $theme_namespaces);
        return $theme_namespace;
    }

    /**
     * viewEventDebug
     *
     * Genuinely not a clue at this moment in time!
     *
     * @param mixed $obj
     * @param mixed $full
     *
     * @return ?
     * @access protected
     */
    protected function viewEventDebug($obj, $full = false)
    {
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
     *
     * @return array of formatted date strings
     */
    protected function formatDates($dates, $format = 'd F Y')
    {
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
     * testDates
     *
     * @param array $date_array
     *
     * @return ?
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

    /**
     * makeTestDateArray
     *
     * @param mixed $dates
     *
     * @return ?
     * @access protected
     */
    protected function makeTestDateArray($dates)
    {
        $result = array();
        foreach ($dates as $v) {
            $result[] = (object) $v;
        }
        return $result;
    }
}
