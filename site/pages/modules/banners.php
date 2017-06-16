<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2016

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    banners
 */

/**
 * Module page class.
 */
class Module_banners
{
    /**
     * Find details of the module.
     *
     * @return ?array Map of module info (null: module is disabled).
     */
    public function info()
    {
        $info = array();
        $info['author'] = 'Chris Graham';
        $info['organisation'] = 'ocProducts';
        $info['hacked_by'] = null;
        $info['hack_version'] = null;
        $info['version'] = 7;
        $info['locked'] = true;
        $info['update_require_upgrade'] = true;
        return $info;
    }

    /**
     * Uninstall the module.
     */
    public function uninstall()
    {
        $GLOBALS['SITE_DB']->drop_table_if_exists('banners');
        $GLOBALS['SITE_DB']->drop_table_if_exists('banners_types');
        $GLOBALS['SITE_DB']->drop_table_if_exists('banner_types');
        $GLOBALS['SITE_DB']->drop_table_if_exists('banner_clicks');

        $GLOBALS['SITE_DB']->query_delete('group_category_access', array('module_the_name' => 'banners'));

        delete_privilege('full_banner_setup');
        delete_privilege('view_anyones_banner_stats');
        delete_privilege('banner_free');
        delete_privilege('use_html_banner');
        delete_privilege('use_php_banner');

        require_code('files');
        if (!$GLOBALS['DEV_MODE']) {
            deldir_contents(get_custom_file_base() . '/uploads/banners', true);
        }
    }

    /**
     * Install the module.
     *
     * @param  ?integer $upgrade_from What version we're upgrading from (null: new install)
     * @param  ?integer $upgrade_from_hack What hack version we're upgrading from (null: new-install/not-upgrading-from-a-hacked-version)
     */
    public function install($upgrade_from = null, $upgrade_from_hack = null)
    {
        if ($upgrade_from === null) {
            require_lang('banners');
            require_code('banners');
            require_code('lang3');

            $GLOBALS['SITE_DB']->create_table('banners', array(
                'name' => '*ID_TEXT',
                'expiry_date' => '?TIME',
                'submitter' => 'MEMBER',
                'img_url' => 'URLPATH',
                'the_type' => 'SHORT_INTEGER', // a BANNER_* constant
                'b_title_text' => 'SHORT_TEXT',
                'caption' => 'SHORT_TRANS__COMCODE',
                'b_direct_code' => 'LONG_TEXT',
                'campaign_remaining' => 'INTEGER',
                'site_url' => 'URLPATH',
                'hits_from' => 'INTEGER',
                'views_from' => 'INTEGER',
                'hits_to' => 'INTEGER',
                'views_to' => 'INTEGER',
                'importance_modulus' => 'INTEGER',
                'notes' => 'LONG_TEXT',
                'validated' => 'BINARY',
                'add_date' => 'TIME',
                'edit_date' => '?TIME',
                'b_type' => 'ID_TEXT'
            ));

            $GLOBALS['SITE_DB']->create_index('banners', 'banner_child_find', array('b_type'));
            $GLOBALS['SITE_DB']->create_index('banners', 'the_type', array('the_type'));
            $GLOBALS['SITE_DB']->create_index('banners', 'expiry_date', array('expiry_date'));
            $GLOBALS['SITE_DB']->create_index('banners', 'badd_date', array('add_date'));
            $GLOBALS['SITE_DB']->create_index('banners', 'topsites', array('hits_from', 'hits_to'));
            $GLOBALS['SITE_DB']->create_index('banners', 'campaign_remaining', array('campaign_remaining'));
            $GLOBALS['SITE_DB']->create_index('banners', 'bvalidated', array('validated'));

            $map = array(
                'name' => 'advertise_here',
                'b_title_text' => '',
                'b_direct_code' => '',
                'the_type' => BANNER_FALLBACK,
                'img_url' => 'data/images/advertise_here.png',
                'campaign_remaining' => 0,
                'site_url' => get_base_url() . '/index.php?page=advertise',
                'hits_from' => 0,
                'views_from' => 0,
                'hits_to' => 0,
                'views_to' => 0,
                'importance_modulus' => 10,
                'notes' => 'Provided as a default. This is a fallback banner (it shows when others are not available).',
                'validated' => 1,
                'add_date' => time(),
                'submitter' => $GLOBALS['FORUM_DRIVER']->get_guest_id(),
                'b_type' => '',
                'expiry_date' => null,
                'edit_date' => null,
            );
            $map += lang_code_to_default_content('caption', 'ADVERTISE_HERE', true, 1);
            $GLOBALS['SITE_DB']->query_insert('banners', $map);
            $banner_a = 'advertise_here';

            $map = array(
                'name' => 'donate',
                'b_title_text' => '',
                'b_direct_code' => '',
                'the_type' => BANNER_PERMANENT,
                'img_url' => 'data/images/donate.png',
                'campaign_remaining' => 0,
                'site_url' => get_base_url() . '/index.php?page=donate',
                'hits_from' => 0,
                'views_from' => 0,
                'hits_to' => 0,
                'views_to' => 0,
                'importance_modulus' => 30,
                'notes' => 'Provided as a default.',
                'validated' => 1,
                'add_date' => time(),
                'submitter' => $GLOBALS['FORUM_DRIVER']->get_guest_id(),
                'b_type' => '',
                'expiry_date' => null,
                'edit_date' => null,
            );
            $map += lang_code_to_default_content('caption', 'DONATION', true, 1);
            $GLOBALS['SITE_DB']->query_insert('banners', $map);
            $banner_b = 'donate';

            require_code('permissions2');
            set_global_category_access('banners', $banner_a);
            set_global_category_access('banners', $banner_b);

            add_privilege('BANNERS', 'full_banner_setup', false);
            add_privilege('BANNERS', 'view_anyones_banner_stats', false);

            $GLOBALS['SITE_DB']->create_table('banner_types', array(
                'id' => '*ID_TEXT',
                't_is_textual' => 'BINARY',
                't_image_width' => 'INTEGER',
                't_image_height' => 'INTEGER',
                't_max_file_size' => 'INTEGER',
                't_comcode_inline' => 'BINARY'
            ));

            $GLOBALS['SITE_DB']->create_index('banner_types', 'hottext', array('t_comcode_inline'));

            $GLOBALS['SITE_DB']->query_insert('banner_types', array(
                'id' => '',
                't_is_textual' => 0,
                't_image_width' => 728,
                't_image_height' => 90,
                't_max_file_size' => 150,
                't_comcode_inline' => 0
            ));

            $GLOBALS['SITE_DB']->create_table('banner_clicks', array(
                'id' => '*AUTO',
                'c_date_and_time' => 'TIME',
                'c_member_id' => 'MEMBER',
                'c_ip_address' => 'IP',
                'c_source' => 'ID_TEXT',
                'c_banner_id' => 'ID_TEXT'
            ));
            $GLOBALS['SITE_DB']->create_index('banner_clicks', 'clicker_ip', array('c_ip_address'));

            add_privilege('BANNERS', 'banner_free', false);
        }

        if (($upgrade_from !== null) && ($upgrade_from < 6)) {
            $GLOBALS['SITE_DB']->add_table_field('banners', 'b_direct_code', 'LONG_TEXT');
            delete_config_option('money_ad_code');
            delete_config_option('advert_chance');
            delete_config_option('is_on_banners');
        }

        if (($upgrade_from === null) || ($upgrade_from < 6)) {
            add_privilege('BANNERS', 'use_html_banner', false);
            add_privilege('BANNERS', 'use_php_banner', false, true);
        }

        if (($upgrade_from === null) || ($upgrade_from < 7)) {
            $GLOBALS['SITE_DB']->create_table('banners_types', array(
                'name' => '*ID_TEXT',
                'b_type' => '*ID_TEXT',
            ));

            $GLOBALS['SITE_DB']->create_index('banner_clicks', 'c_banner_id', array('c_banner_id'));
        }
    }

    /**
     * Find entry-points available within this module.
     *
     * @param  boolean $check_perms Whether to check permissions.
     * @param  ?MEMBER $member_id The member to check permissions as (null: current user).
     * @param  boolean $support_crosslinks Whether to allow cross links to other modules (identifiable via a full-page-link rather than a screen-name).
     * @param  boolean $be_deferential Whether to avoid any entry-point (or even return null to disable the page in the Sitemap) if we know another module, or page_group, is going to link to that entry-point. Note that "!" and "browse" entry points are automatically merged with container page nodes (likely called by page-groupings) as appropriate.
     * @return ?array A map of entry points (screen-name=>language-code/string or screen-name=>[language-code/string, icon-theme-image]) (null: disabled).
     */
    public function get_entry_points($check_perms = true, $member_id = null, $support_crosslinks = true, $be_deferential = false)
    {
        if ($check_perms) {
            if (is_guest($member_id)) {
                return array();
            }

            if ($member_id === null) {
                $member_id = get_member();
            }
            if (!has_zone_access($member_id, 'adminzone')) {
                $num_banners_owned = $GLOBALS['SITE_DB']->query_select_value('banners', 'COUNT(*)', array('submitter' => $member_id));
                if ($num_banners_owned == 0) {
                    return null;
                }
            }
        }

        return array(
            'browse' => array('BANNERS', 'menu/cms/banners'),
        );
    }

    public $title;
    public $source;
    public $myrow;

    /**
     * Module pre-run function. Allows us to know metadata for <head> before we start streaming output.
     *
     * @return ?Tempcode Tempcode indicating some kind of exceptional output (null: none).
     */
    public function pre_run()
    {
        require_lang('banners');
        $type = get_param_string('type', 'browse');

        require_lang('banners');

        if ($type == 'browse') {
            $this->title = get_screen_title('BANNERS');
        }

        if ($type == 'view') {
            inform_non_canonical_parameter('sort');

            $source = get_param_string('source');

            $rows = $GLOBALS['SITE_DB']->query_select('banners', array('*'), array('name' => $source), '', 1);
            if (!array_key_exists(0, $rows)) {
                warn_exit(do_lang_tempcode('BANNER_MISSING_SOURCE'));
            }
            $myrow = $rows[0];

            set_extra_request_metadata(array(
                'title' => get_translated_text($myrow['caption']), // Different from CMA hook
                'identifier' => '_SEARCH:banners:view:' . $source,
                'description' => '', // Different from CMA hook
            ), $myrow, 'banner', $source);

            breadcrumb_set_parents(array(array('_SELF:_SELF:browse', do_lang_tempcode('BANNERS'))));

            $this->title = get_screen_title('BANNER_INFORMATION');

            $this->source = $source;
            $this->myrow = $myrow;
        }

        return null;
    }

    /**
     * Execute the module.
     *
     * @return Tempcode The result of execution.
     */
    public function run()
    {
        if ($GLOBALS['CURRENT_SHARE_USER'] !== null) {
            warn_exit(do_lang_tempcode('SHARED_INSTALL_PROHIBIT'));
        }

        require_code('banners');

        // Decide what we're doing
        $type = get_param_string('type', 'browse');

        if ($type == 'browse') {
            return $this->choose_banner();
        }
        if ($type == 'view') {
            return $this->view_banner();
        }
        if ($type == 'reset') {
            return $this->reset_banner();
        }

        return new Tempcode();
    }

    /**
     * The UI to choose a banner to view.
     *
     * @return Tempcode The UI
     */
    public function choose_banner()
    {
        require_code('templates_results_table');

        $current_ordering = get_param_string('sort', 'name ASC', INPUT_FILTER_GET_COMPLEX);
        if (strpos($current_ordering, ' ') === false) {
            warn_exit(do_lang_tempcode('INTERNAL_ERROR'));
        }
        list($sortable, $sort_order) = explode(' ', $current_ordering, 2);
        $sortables = array(
            'name' => do_lang_tempcode('CODENAME'),
            'b_type' => do_lang_tempcode('BANNER_TYPE'),
            'the_type' => do_lang_tempcode('DEPLOYMENT_AGREEMENT'),
            //'campaign_remaining' => do_lang_tempcode('HITS_ALLOCATED'),
            'importance_modulus' => do_lang_tempcode('IMPORTANCE_MODULUS'),
            'expiry_date' => do_lang_tempcode('EXPIRY_DATE'),
            'add_date' => do_lang_tempcode('ADDED'),
        );
        if (addon_installed('unvalidated')) {
            $sortables['validated'] = do_lang_tempcode('VALIDATED');
        }
        if (((strtoupper($sort_order) != 'ASC') && (strtoupper($sort_order) != 'DESC')) || (!array_key_exists($sortable, $sortables))) {
            log_hack_attack_and_exit('ORDERBY_HACK');
        }

        $hr = array(
            do_lang_tempcode('CODENAME'),
            do_lang_tempcode('BANNER_TYPE'),
            //do_lang_tempcode('DEPLOYMENT_AGREEMENT'),
            //do_lang_tempcode('HITS_ALLOCATED'),
            do_lang_tempcode('_IMPORTANCE_MODULUS'),
            do_lang_tempcode('EXPIRY_DATE'),
            do_lang_tempcode('ADDED'),
        );
        if (addon_installed('unvalidated')) {
            $hr[] = protect_from_escaping(do_template('COMCODE_ABBR', array('_GUID' => '77d1bbbfc8e6847ecdce12489913a96a', 'TITLE' => do_lang_tempcode('VALIDATED'), 'CONTENT' => do_lang_tempcode('VALIDATED_SHORT'))));
        }
        $hr[] = do_lang_tempcode('ACTIONS');
        $header_row = results_field_title($hr, $sortables, 'sort', $sortable . ' ' . $sort_order);

        $fields = new Tempcode();

        $url_map = array('page' => '_SELF', 'type' => 'view');

        require_code('form_templates');
        $only_owned = has_privilege(get_member(), 'edit_midrange_content', 'cms_banners') ? null : get_member();
        $max_rows = $GLOBALS['SITE_DB']->query_select_value('banners', 'COUNT(*)', ($only_owned === null) ? null : array('submitter' => $only_owned));
        if ($max_rows == 0) {
            inform_exit(do_lang_tempcode('NO_ENTRIES', 'banner'));
        }
        $max = get_param_integer('banner_max', 20);
        $start = get_param_integer('banner_start', 0);
        $rows = $GLOBALS['SITE_DB']->query_select('banners', array('*'), ($only_owned === null) ? null : array('submitter' => $only_owned), 'ORDER BY ' . $current_ordering, $max, $start);
        foreach ($rows as $row) {
            $view_url = build_url($url_map + array('source' => $row['name']), '_SELF');

            $deployment_agreement = new Tempcode();
            switch ($row['the_type']) {
                case BANNER_PERMANENT:
                    $deployment_agreement = do_lang_tempcode('BANNER_PERMANENT');
                    break;
                case BANNER_CAMPAIGN:
                    $deployment_agreement = do_lang_tempcode('BANNER_CAMPAIGN');
                    break;
                case BANNER_FALLBACK:
                    $deployment_agreement = do_lang_tempcode('BANNER_FALLBACK');
                    break;
            }

            $fr = array(
                do_template('COMCODE_TELETYPE', array('_GUID' => '4ac291a8c2eabc304cd26f7d6b4bf8a2', 'CONTENT' => escape_html($row['name']))),
                ($row['b_type'] == '') ? do_lang('_DEFAULT') : $row['b_type'],
                //$deployment_agreement,  Too much detail
                //integer_format($row['campaign_remaining']),  Too much detail
                strval($row['importance_modulus']),
                ($row['expiry_date'] === null) ? protect_from_escaping(do_lang_tempcode('NA_EM')) : make_string_tempcode(get_timezoned_date_time($row['expiry_date'])),
                get_timezoned_date($row['add_date']),
            );
            if (addon_installed('unvalidated')) {
                $fr[] = ($row['validated'] == 1) ? do_lang_tempcode('YES') : do_lang_tempcode('NO');
            }
            $fr[] = protect_from_escaping(hyperlink($view_url, do_lang_tempcode('VIEW'), false, true, $row['name']));

            $fields->attach(results_entry($fr, true));
        }

        $table = results_table(do_lang('BANNERS'), $start, 'banner_start', $max, 'banner_max', $max_rows, $header_row, $fields, $sortables, $sortable, $sort_order);

        $text = do_lang_tempcode('CHOOSE_VIEW_LIST');

        $tpl = do_template('COLUMNED_TABLE_SCREEN', array('_GUID' => 'be5248da379faeead5a18d9f2b62bd6b', 'TITLE' => $this->title, 'TEXT' => $text, 'TABLE' => $table, 'SUBMIT_ICON' => 'buttons__proceed', 'SUBMIT_NAME' => null, 'POST_URL' => get_self_url()));

        require_code('templates_internalise_screen');
        return internalise_own_screen($tpl);
    }

    /**
     * The UI to view a banner.
     *
     * @return Tempcode The UI
     */
    public function view_banner()
    {
        $source = $this->source;

        $myrow = $this->myrow;

        if ((is_guest($myrow['submitter'])) || ($myrow['submitter'] != get_member())) {
            check_privilege('view_anyones_banner_stats');
        }

        // Banner details table...

        switch ($myrow['the_type']) {
            case BANNER_PERMANENT:
                $type = do_lang_tempcode('BANNER_PERMANENT');
                break;
            case BANNER_CAMPAIGN:
                $type = do_lang_tempcode('BANNER_HITS_LEFT', do_lang_tempcode('BANNER_CAMPAIGN'), make_string_tempcode(integer_format($myrow['campaign_remaining'])));
                break;
            case BANNER_FALLBACK:
                $type = do_lang_tempcode('BANNER_FALLBACK');
                break;
        }

        if ($myrow['site_url'] == '') {
            $click_through = do_lang_tempcode('CANT_TRACK');
        } else {
            if ($myrow['views_to'] != 0) {
                $click_through = protect_from_escaping(escape_html(float_format(round(100.0 * ($myrow['hits_to'] / $myrow['views_to']))) . '%'));
            } else {
                $click_through = do_lang_tempcode('NA_EM');
            }
        }

        $has_banner_network = $GLOBALS['SITE_DB']->query_select_value('banners', 'SUM(views_from)') != 0.0;

        $fields = new Tempcode();
        require_code('templates_map_table');
        $fields->attach(map_table_field(do_lang_tempcode('TYPE'), $type));

        $fields->attach(map_table_field(do_lang_tempcode('BANNER_TYPE'), ($myrow['b_type'] == '') ? do_lang('_DEFAULT') : $myrow['b_type']));

        $banner_types = implode(', ', collapse_1d_complexity('b_type', $GLOBALS['SITE_DB']->query_select('banners_types', array('b_type'), array('name' => $myrow['name']))));
        $fields->attach(map_table_field(do_lang_tempcode('SECONDARY_CATEGORIES'), ($banner_types == '') ? do_lang_tempcode('NA_EM') : protect_from_escaping(escape_html($banner_types))));

        if (addon_installed('stats')) {
            $banners_regions = implode(', ', collapse_1d_complexity('region', $GLOBALS['SITE_DB']->query_select('content_regions', array('region'), array('content_type' => 'banner', 'content_id' => $myrow['name']))));
            $fields->attach(map_table_field(do_lang_tempcode('FILTER_REGIONS'), ($banners_regions == '') ? do_lang_tempcode('ALL_EM') : protect_from_escaping(escape_html($banners_regions))));
        }

        $fields->attach(map_table_field(do_lang_tempcode('ADDED'), get_timezoned_date_time($myrow['add_date'])));

        $expiry_date = ($myrow['expiry_date'] === null) ? do_lang_tempcode('NA_EM') : make_string_tempcode(escape_html(get_timezoned_date_time($myrow['expiry_date'])));
        $fields->attach(map_table_field(do_lang_tempcode('EXPIRY_DATE'), $expiry_date));

        if ($has_banner_network) {
            $fields->attach(map_table_field(do_lang_tempcode('BANNER_HITS_FROM'), escape_html(integer_format($myrow['hits_from'])), false, 'hits_from'));
            $fields->attach(map_table_field(do_lang_tempcode('BANNER_VIEWS_FROM'), escape_html(integer_format($myrow['views_from'])), false, 'views_from'));
        }
        $fields->attach(map_table_field(do_lang_tempcode('BANNER_HITS_TO'), ($myrow['site_url'] == '') ? do_lang_tempcode('CANT_TRACK') : protect_from_escaping(escape_html(integer_format($myrow['hits_to']))), false, 'hits_to'));
        $fields->attach(map_table_field(do_lang_tempcode('BANNER_VIEWS_TO'), ($myrow['site_url'] == '') ? do_lang_tempcode('CANT_TRACK') : protect_from_escaping(escape_html(integer_format($myrow['views_to']))), false, 'views_to'));
        $fields->attach(map_table_field(do_lang_tempcode('BANNER_CLICKTHROUGH'), $click_through));

        $username = $GLOBALS['FORUM_DRIVER']->member_profile_hyperlink($myrow['submitter']);
        $fields->attach(map_table_field(do_lang_tempcode('SUBMITTER'), $username, true));

        $map_table = do_template('MAP_TABLE', array('_GUID' => 'eb97a46d8e9813da7081991d5beed270', 'WIDTH' => '300', 'FIELDS' => $fields));

        $banner = show_banner($myrow['name'], $myrow['b_title_text'], get_translated_tempcode('banners', $myrow, 'caption'), $myrow['b_direct_code'], $myrow['img_url'], $source, $myrow['site_url'], $myrow['b_type'], $myrow['submitter']);

        $edit_url = new Tempcode();
        if ((has_actual_page_access(null, 'cms_banners', null, null)) && (has_edit_permission('mid', get_member(), $myrow['submitter'], 'cms_banners'))) {
            $edit_url = build_url(array('page' => 'cms_banners', 'type' => '_edit', 'id' => $source), get_module_zone('cms_banners'));
        }

        // Results table...

        if ($myrow['site_url'] != '') {
            require_lang('dates');

            require_code('templates_results_table');

            $current_ordering = get_param_string('sort', 'month ASC', INPUT_FILTER_GET_COMPLEX);
            if (strpos($current_ordering, ' ') === false) {
                warn_exit(do_lang_tempcode('INTERNAL_ERROR'));
            }
            list($sortable, $sort_order) = explode(' ', $current_ordering, 2);
            $sortables = array(
                'day' => do_lang_tempcode('DAY'),
                'month' => do_lang_tempcode('MONTH'),
            );
            if (((strtoupper($sort_order) != 'ASC') && (strtoupper($sort_order) != 'DESC')) || (!array_key_exists($sortable, $sortables))) {
                log_hack_attack_and_exit('ORDERBY_HACK');
            }

            $hr = array(
                do_lang_tempcode('DATE'),
                do_lang_tempcode('BANNER_HITS_TO'),
            );
            $header_row = results_field_title($hr, $sortables, 'sort', $sortable . ' ' . $sort_order);

            $max = get_param_integer('max', 50);
            $start = get_param_integer('start', 0);
            $rows = $GLOBALS['SITE_DB']->query_select('banner_clicks', array('c_date_and_time'), array('c_banner_id' => $source), 'ORDER BY c_date_and_time ' . $sort_order, 10000);
            $tally_sets = array();
            foreach ($rows as $row) {
                if ($sortable == 'day') {
                    $period = get_timezoned_date($row['c_date_and_time']);
                } else {
                    $period = cms_strftime('%B %Y', $row['c_date_and_time']);
                }

                if (!isset($tally_sets[$period])) {
                    $tally_sets[$period] = 0;
                }
                $tally_sets[$period]++;
            }

            $fields = new Tempcode();
            foreach ($tally_sets as $period => $hits) {
                $fr = array(
                    $period,
                    integer_format($hits),
                );

                $fields->attach(results_entry($fr, true));
            }

            $results_table = results_table(do_lang('BANNER_HITS_TO'), get_param_integer('start', 0), 'start', get_param_integer('max', 20), 'max', count($tally_sets), $header_row, $fields, $sortables, $sortable, $sort_order);
        } else {
            $results_table = new Tempcode();
        }

        // Reset feature...

        $reset_url = new Tempcode();
        if (has_actual_page_access(get_member(), 'admin_banners')) {
            $reset_url = build_url(array('page' => '_SELF', 'type' => 'reset', 'source' => $source), '_SELF');
        }

        // ---

        return do_template('BANNER_VIEW_SCREEN', array(
            '_GUID' => 'ed923ae0682c6ed679c0efda688c49ea',
            'TITLE' => $this->title,
            'EDIT_URL' => $edit_url,
            'MAP_TABLE' => $map_table,
            'BANNER' => $banner,
            'RESULTS_TABLE' => $results_table,
            'RESET_URL' => $reset_url,
            'NAME' => $source,
        ));
    }

    /**
     * The actualiser to reset a banner.
     *
     * @return Tempcode The UI
     */
    public function reset_banner()
    {
        $title = get_screen_title('RESET_BANNER_STATS');

        post_param_string('confirm'); // Just to confirm it is a POST request, i.e. not a CSRF

        $source = get_param_string('source');

        if (!has_actual_page_access(get_member(), 'admin_banners')) {
            access_denied('I_ERROR');
        }

        $GLOBALS['SITE_DB']->query_delete('banner_clicks', array('c_banner_id' => $source));
        $GLOBALS['SITE_DB']->query_update('banners', array('hits_from' => 0, 'hits_to' => 0, 'views_from' => 0, 'views_to' => 0), array('name' => $source), '', 1);

        $url = build_url(array('page' => '_SELF', 'type' => 'view', 'source' => $source), '_SELF');
        return redirect_screen($title, $url, do_lang_tempcode('SUCCESS'));
    }
}
