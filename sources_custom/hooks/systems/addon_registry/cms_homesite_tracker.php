<?php /*

 The contents of this file are subject to the Common Public Attribution License Version 1.0 (the "License");
 you may not use this file except in compliance with the License.
 You may obtain a copy of the License at http://opensource.org/licenses/cpal_1.0.

 Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 See the License for the specific language governing rights and limitations under the License.

 The Original Code is Composr CMS.

 The Original Developer is the Initial Developer.

 The Initial Developer of the Original Code is Chris Graham.
 All portions of the code written by Chris Graham are Copyright (c) Christopher Graham. All Rights Reserved.

 See docs/LICENSE.md for full licensing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  Christopher Graham
 * @package    cms_homesite_tracker
 */

/**
 * Hook class.
 */
class Hook_addon_registry_cms_homesite_tracker
{
    /**
     * Get a list of file permissions to set.
     *
     * @param  boolean $runtime Whether to include wildcards represented runtime-created chmoddable files
     * @return array File permissions to set
     */
    public function get_chmod_array(bool $runtime = false) : array
    {
        return [];
    }

    /**
     * Get the current version of this addon (usually software major, software minor, addon build).
     * Put the comment "// addon_version_auto_update" to the right of the return if you want release tools to automatically update this according to software version and find_addon_effective_md5.
     *
     * @return SHORT_TEXT Version number
     */
    public function get_version() : string
    {
        return '11.0.2'; // addon_version_auto_update 4a744aee38dce93f9fcc1f98f10eac86
    }

    /**
     * Get the minimum required version of the website software needed to use this addon.
     *
     * @return float Minimum required website software version
     */
    public function get_min_cms_version() : float
    {
        return 11.0;
    }

    /**
     * Get the maximum compatible version of the website software to use this addon.
     *
     * @return ?float Maximum compatible website software version (null: no maximum version currently)
     */
    public function get_max_cms_version() : ?float
    {
        return 11.9;
    }

    /**
     * Get the addon category.
     *
     * @return string The category
     */
    public function get_category() : string
    {
        return 'Development';
    }

    /**
     * Get the addon author.
     *
     * @return string The author
     */
    public function get_author() : string
    {
        return 'Chris Graham';
    }

    /**
     * Find other authors.
     *
     * @return array A list of co-authors that should be attributed
     */
    public function get_copyright_attribution() : array
    {
        return [
            'Incorporates MantisBT',
        ];
    }

    /**
     * Get the addon licence (one-line summary only).
     *
     * @return string The licence
     */
    public function get_licence() : string
    {
        return 'Licensed on the same terms as Composr / MantisBT under GPL';
    }

    /**
     * Get the description of the addon.
     *
     * @return string Description of the addon
     */
    public function get_description() : string
    {
        return "Implements a custom version of the Mantis bug tracker for use on the homesite.";
    }

    /**
     * Get a list of tutorials that apply to this addon.
     *
     * @return array List of tutorials
     */
    public function get_applicable_tutorials() : array
    {
        return [];
    }

    /**
     * Get a mapping of dependency types.
     *
     * @return array A structure specifying dependency information
     */
    public function get_dependencies() : array
    {
        return [
            'requires' => [
                //'tickets',
                //'ecommerce',
                //'points',
                //'Conversr',
                //'core_all_icons',
                'cms_homesite',
            ],
            'recommends' => [
                'stats',
                'cms_release_build',
                'composr_tutorials',
                'tickets',
                'points',
            ],
            'conflicts_with' => [],
            'previously_in_addon' => ['composr_homesite_support_credits'],
        ];
    }

    /**
     * Explicitly say which icon should be used.
     *
     * @return URLPATH Icon
     */
    public function get_default_icon() : string
    {
        return 'themes/default/images/icons/admin/component.svg';
    }

    /**
     * Get a list of files that belong to this addon.
     *
     * @return array List of files
     */
    public function get_file_list() : array
    {
        return [
        ];
    }

    /**
     * Uninstall the addon.
     */
    public function uninstall()
    {
        // MANTIS TABLE DELETION

        if (strpos(get_db_type(), 'mysql') === false) {
            return;
        }

        $tables = [
            'mantis_api_token_table',
            'mantis_bug_file_table',
            'mantis_bug_history_table',
            'mantis_bug_monitor_table',
            'mantis_bug_relationship_table',
            'mantis_bug_revision_table',
            'mantis_bug_table',
            'mantis_bug_tag_table',
            'mantis_bug_text_table',
            'mantis_bugnote_table',
            'mantis_bugnote_text_table',
            'mantis_category_table',
            'mantis_config_table',
            'mantis_custom_field_project_table',
            'mantis_custom_field_string_table',
            'mantis_custom_field_table',
            'mantis_email_table',
            'mantis_filters_table',
            'mantis_news_table',
            'mantis_plugin_table',
            'mantis_project_file_table',
            'mantis_project_hierarchy_table',
            'mantis_project_table',
            'mantis_project_user_list_table',
            'mantis_project_version_table',
            'mantis_sponsorship_table',
            'mantis_tag_table',
            'mantis_tokens_table',
            'mantis_user_pref_table',
            'mantis_user_print_pref_table',
            'mantis_user_profile_table',
            'mantis_user_table',
        ];
        $GLOBALS['SITE_DB']->query('DROP TABLE IF EXISTS ' . implode(',' , $tables));
    }

    /**
     * Install the addon.
     *
     * @param  ?float $upgrade_major_minor From what major/minor version we are upgrading (null: new install)
     * @param  ?integer $upgrade_patch From what patch version of $upgrade_major_minor we are upgrading (null: new install)
     */
    public function install(?float $upgrade_major_minor = null, ?int $upgrade_patch = null)
    {
        return; // TODO: not safe to run this automatically

        if (($upgrade_major_minor === null) || version_compare(float_to_raw_string($upgrade_major_minor, 1) . '.' . strval($upgrade_patch), '11.0.3', '<')) { // 11.beta9
            // DO NOT FORGET TO RENAME TRACKER/UPLOADS TO TRACKER_LEGACY/UPLOADS BEFORE GIT PULL

            // Make sure our new tracker zone is present in htaccess
            require_code('zones2');
            sync_htaccess_with_zones();

            require_lang('catalogues');
            require_lang('tracker');
            require_lang('addons');
            require_code('permissions2');
            require_code('catalogues');
            require_code('catalogues2');
            require_code('lang3');
            require_code('cns_groups');

            $admin_groups = $GLOBALS['FORUM_DRIVER']->get_super_admin_groups();
            $mod_groups = $GLOBALS['FORUM_DRIVER']->get_moderator_groups();
            $probation_groups = [get_probation_group()];
            $guest_groups = [$GLOBALS['FORUM_DRIVER']->get_guest_id()];
            $groups = array_diff(array_keys($GLOBALS['FORUM_DRIVER']->get_usergroup_list(false, true, true)), $guest_groups); // Never include guests
            $non_staff_groups = array_diff($groups, $admin_groups, $mod_groups);

            // Step 1: Create the tracker catalogue.
            actual_add_catalogue(
                'tracker',
                lang_code_to_default_content('c_title', 'TRACKER', false, 2),
                lang_code_to_default_content('c_description', 'DESCRIPTION_TRACKER_CATALOGUE', true, 3),
                C_DT_TABULAR,
                0,
                do_lang('TRACKER_CATALOGUE_NOTES'),
                0 // Points are assigned when an issue gets resolved
            );

            // Step 2. Set catalogue permissions.
            set_global_category_access('catalogues_catalogue', 'tracker');

            foreach ($non_staff_groups as $group) {
                // However we must reject the ability to edit own entries except for staff
                $GLOBALS['SITE_DB']->query_insert('group_privileges', [
                    'group_id' => $group,
                    'privilege' => 'edit_own_midrange_content',
                    'the_page' => '',
                    'module_the_name' => 'catalogues_catalogue',
                    'category_name' => 'tracker',
                    'the_value' => 0,
                ]);

                // However we must reject the ability to delete own entries except for staff
                $GLOBALS['SITE_DB']->query_insert('group_privileges', [
                    'group_id' => $group,
                    'privilege' => 'delete_own_midrange_content',
                    'the_page' => '',
                    'module_the_name' => 'catalogues_catalogue',
                    'category_name' => 'tracker',
                    'the_value' => 0,
                ]);
            }

            foreach (array_diff($groups, $probation_groups) as $group) {
                // However we must allow bypassing validation (except for probation) so anyone can quickly report issues
                $GLOBALS['SITE_DB']->query_insert('group_privileges', [
                    'group_id' => $group,
                    'privilege' => 'bypass_validation_midrange_content',
                    'the_page' => '',
                    'module_the_name' => 'catalogues_catalogue',
                    'category_name' => 'tracker',
                    'the_value' => 1,
                ]);
            }

            // Step 3: create the fields.
            $fields = [
                // Name, description, type, defines order, required, visible, options, sensitive, put in category / search, sortable, default (language string)
                ['IDENTIFIER', 'DESCRIPTION_TRACKER_CATALOGUE_IDENTIFIER', 'tracker_id', 1, 1, 1, '', 0, 1, 1, ''],
                ['ISSUE_TYPE', 'DESCRIPTION_TRACKER_CATALOGUE_ISSUE_TYPE', 'list', 0, 1, 1, 'display_val=on', 0, 1, 1, 'TRACKER_CATALOGUE_ISSUE_TYPE_DEFAULT'],
                ['TITLE', 'DESCRIPTION_TRACKER_CATALOGUE_TITLE', 'short_text', 0, 1, 1, 'input_size=56', 0, 1, 0, ''],
                ['STATUS', 'DESCRIPTION_TRACKER_CATALOGUE_STATUS', 'list', 0, 1, 1, 'display_val=on,edit_only=1', 0, 1, 1, 'TRACKER_CATALOGUE_STATUS_DEFAULT'],
                ['ISSUE_TAGS', 'DESCRIPTION_TRACKER_CATALOGUE_TAGS', 'list_multi', 0, 0, 1, 'custom_values=multiple,edit_only=1,widget=vertical_checkboxes', 0, 0, 0, ''],
                ['HANDLER', 'DESCRIPTION_TRACKER_CATALOGUE_HANDLER', 'member', 0, 0, 1, 'edit_only=1', 0, 0, 0, ''],
                ['VERSION', 'DESCRIPTION_TRACKER_CATALOGUE_VERSION', 'version', 0, 0, 1, '', 0, 0, 0, ''],
                ['ADDON', 'DESCRIPTION_TRACKER_CATALOGUE_ADDON', 'addon', 0, 0, 1, 'auto_sort=on', 0, 0, 1, ''],
                ['DESCRIPTION', 'DESCRIPTION_TRACKER_CATALOGUE_DESCRIPTION', 'long_trans', 0, 1, 1, '', 1, 0, 0, ''],
                ['STEPS_TO_REPRODUCE', 'DESCRIPTION_TRACKER_CATALOGUE_STEPS_TO_REPRODUCE', 'short_trans_multi', 0, 0, 1, '', 1, 0, 0, ''],
                ['ADDITIONAL_INFORMATION', 'DESCRIPTION_TRACKER_CATALOGUE_ADDITIONAL_INFORMATION', 'long_trans', 0, 0, 1, '', 1, 0, 0, ''],
                ['RELATED_TO', 'DESCRIPTION_TRACKER_CATALOGUE_RELATED_TO', 'cx_tracker', 0, 0, 1, 'edit_only=1', 0, 0, 0, ''],
                ['IS_FUNDED', 'DESCRIPTION_TRACKER_CATALOGUE_IS_FUNDED', 'tick', 0, 1, 1, 'edit_only=1', 0, 1, 1, 'TRACKER_CATALOGUE_IS_FUNDED_DEFAULT'],
                ['RELEASED_IN_VERSION', 'DESCRIPTION_TRACKER_CATALOGUE_RELEASED_IN_VERSION', 'version', 0, 0, 1, 'edit_only=1', 0, 0, 0, ''],
                ['HOTFIXES', 'DESCRIPTION_TRACKER_CATALOGUE_HOTFIXES', 'upload_multi', 0, 0, 1, 'edit_only=1', 0, 0, 0, ''],
                ['COMMITS', 'DESCRIPTION_TRACKER_CATALOGUE_COMMITS', 'url_multi', 0, 0, 1, 'edit_only=1', 0, 0, 0, ''],
            ];
            foreach ($fields as $i => $field) {
                $default = '';

                // Values for default can be mapped by additional language strings
                if ($field[10] != '') {
                    $values = explode('|', do_lang($field[10]));
                    foreach ($values as $j => $value) {
                        if ($j > 0) {
                            $default .= '|';
                        }

                        $default .= $value;
                        $remap = do_lang($field[10] . '_' . filter_naughty_harsh($value, true), null, null, null, null, false);
                        if ($remap !== null) {
                            $default .= '=' . $remap;
                        }
                    }
                }

                actual_add_catalogue_field(
                    'tracker', // $c_name
                    lang_code_to_default_content('cf_name', $field[0], false, 2), // $name
                    lang_code_to_default_content('cf_description', $field[1], false, 3), // $description
                    $field[2], // $type
                    $i, // $order
                    $field[3], // $defines_order
                    $field[5], // $visible
                    $field[7], // $sensitive
                    $default, // $default
                    $field[4], // $required
                    $field[9],
                    1,
                    0,
                    $field[8], // $put_in_category
                    $field[8], // $put_in_search
                    $field[6] // $options
                );
            }

            // Step 4: Create the categories and their privileges
            for ($i = 1; $i <= 6; $i++) {
                $cat_id = actual_add_catalogue_category('tracker', lang_code_to_default_content('cc_title', 'TRACKER_CATALOGUE_CATEGORY_' . strval($i), false, 2), lang_code_to_default_content('cc_description', 'DESCRIPTION_TRACKER_CATALOGUE_CATEGORY_' . strval($i), true, 3), '', null, '');
                set_global_category_access('catalogues_category', $cat_id);

                foreach ($non_staff_groups as $group) {
                    // However we must reject the ability to edit own entries except for staff
                    $GLOBALS['SITE_DB']->query_insert('group_privileges', [
                        'group_id' => $group,
                        'privilege' => 'edit_own_midrange_content',
                        'the_page' => '',
                        'module_the_name' => 'catalogues_category',
                        'category_name' => strval($cat_id),
                        'the_value' => 0,
                    ]);

                    // However we must reject the ability to delete own entries except for staff
                    $GLOBALS['SITE_DB']->query_insert('group_privileges', [
                        'group_id' => $group,
                        'privilege' => 'delete_own_midrange_content',
                        'the_page' => '',
                        'module_the_name' => 'catalogues_category',
                        'category_name' => strval($cat_id),
                        'the_value' => 0,
                    ]);
                }

                foreach (array_diff($groups, $probation_groups) as $group) {
                    // However we must allow bypassing validation (except for probation) so anyone can quickly report issues
                    $GLOBALS['SITE_DB']->query_insert('group_privileges', [
                        'group_id' => $group,
                        'privilege' => 'bypass_validation_midrange_content',
                        'the_page' => '',
                        'module_the_name' => 'catalogues_category',
                        'category_name' => strval($cat_id),
                        'the_value' => 1,
                    ]);
                }
            }
        }

        if (($upgrade_major_minor !== null) && version_compare(float_to_raw_string($upgrade_major_minor, 1) . '.' . strval($upgrade_patch), '11.0.3', '<')) { // LEGACY: 11.beta9
            cms_extend_time_limit(TIME_LIMIT_EXTEND__CRAWL);
            raise_php_memory_limit();

            set_mass_import_mode(true);
            push_query_limiting(false);
            global $NOTIFICATIONS_ON;
            $NOTIFICATIONS_ON = false;

            require_code('permissions2');
            require_code('catalogues');
            require_code('catalogues2');
            require_code('lang3');
            require_code('cns_groups');
            require_code('feedback');
            require_code('files2');
            require_code('cms_homesite_tracker');
            require_code('uploads');
            require_code('comcode');
            require_code('comcode_renderer');
            require_code('notifications');
            if (addon_installed('filedump')) {
                require_code('filedump');
            }

            require_lang('catalogues');
            require_lang('tracker');
            require_lang('addons');
            require_lang('tracker');

            // step 5: Migrate Mantis issues, and also re-map points ledger and escrow t_type and t_type_id for tracker issues
            $_category_rows = $GLOBALS['SITE_DB']->query_select('catalogue_categories', ['id', 'cc_title'], ['c_name' => 'tracker']);
            $category_rows = [];
            foreach ($_category_rows as $crow) {
                $category_rows[get_translated_text($crow['cc_title'])] = $crow['id'];
            }

            $project_map = [
                1 => $category_rows[do_lang('TRACKER_CATALOGUE_CATEGORY_1')],
                10 => $category_rows[do_lang('TRACKER_CATALOGUE_CATEGORY_5')],
                8 => $category_rows[do_lang('TRACKER_CATALOGUE_CATEGORY_2')],
                7 => $category_rows[do_lang('TRACKER_CATALOGUE_CATEGORY_3')],
                5 => $category_rows[do_lang('TRACKER_CATALOGUE_CATEGORY_2')],
                9 => $category_rows[do_lang('TRACKER_CATALOGUE_CATEGORY_5')],
                3 => $category_rows[do_lang('TRACKER_CATALOGUE_CATEGORY_4')],
                4 => $category_rows[do_lang('TRACKER_CATALOGUE_CATEGORY_2')],
            ];

            $resolution_map = [
                10 => 'open',
                20 => 'completed',
                30 => 'open',
                40 => 'closed_noreproduce',
                50 => 'closed_nofix',
                60 => 'closed_duplicate',
                70 => 'closed_nochange',
                80 => 'closed_stale',
                90 => 'closed_rejected',
            ];

            $severity_map = [
                10 => 'feature',
                20 => 'trivial',
                30 => 'trivial', // Guess
                40 => 'trivial', // Guess
                50 => 'minor',
                60 => 'major',
                70 => 'major', // Guess
                80 => 'major', // Guess
                90 => 'security', // Guess
                95 => 'security',
            ];

            $start = 0;
            $max = 100;
            $rows = [];
            do {
                $rows = $GLOBALS['SITE_DB']->query('SELECT * FROM mantis_bug_table', $max, $start);
                foreach ($rows as $row) {
                    // LEGACY: These issues from the homesite Mantis tracker will break the site if imported; skip them
                    $exceptions = [
                        2538, // Description contains highly complex serialized data that corrupts Tempcode
                    ];
                    if (in_array($row['id'], $exceptions)) {
                        continue;
                    }

                    $bug_info = $GLOBALS['SITE_DB']->query_parameterised('SELECT * FROM mantis_bug_text_table WHERE id={id}', ['id' => $row['bug_text_id']]);
                    $category_info = $GLOBALS['SITE_DB']->query_parameterised('SELECT * FROM mantis_category_table WHERE id={id}', ['id' => $row['category_id']]);

                    if (!isset($bug_info[0]) || !isset($category_info[0])) {
                        continue;
                    }

                    $steps_to_reproduce = '';
                    foreach (explode("\n", $bug_info[0]['steps_to_reproduce']) as $i => $step_to_reproduce) {
                        if ($i > 0) {
                            $steps_to_reproduce .= "\n";
                        }
                        $steps_to_reproduce .= $step_to_reproduce . '{$,page hint: render_raw}';
                    }

                    $ids = create_tracker_issue(
                        $row['version'],
                        $row['summary'],
                        isset($severity_map[$row['severity']]) ? $severity_map[$row['severity']] : 'feature',
                        $bug_info[0]['description'] . '{$,page hint: render_raw}',
                        $bug_info[0]['additional_information'] . '{$,page hint: render_raw}',
                        $category_info[0]['name'],
                        isset($project_map[$row['project_id']]) ? $project_map[$row['project_id']] : $project_map[1],
                        (($row['handler_id'] != 0) && (!is_guest($row['handler_id']))) ? $row['handler_id'] : null,
                        $steps_to_reproduce,
                        isset($resolution_map[$row['resolution']]) ? $resolution_map[$row['resolution']] : 'completed',
                        $row['date_submitted'],
                        $row['reporter_id'],
                        $row['id']
                    );

                    $GLOBALS['SITE_DB']->query_update('points_ledger', ['t_type' => 'catalogue_entry', 't_type_id' => strval($ids[0])], ['t_type' => 'tracker_issue', 't_type_id' => strval($row['id'])]);
                    $GLOBALS['SITE_DB']->query_update('escrow', ['content_type' => 'catalogue_entry', 'content_id' => strval($ids[0])], ['content_type' => 'tracker_issue', 'content_id' => strval($row['id'])]);
                }

                $start += $max;
            } while (count($rows) > 0);

            // step 6: Migrate issue relations
            $identifier_field = $GLOBALS['SITE_DB']->query_select_value('catalogue_fields', 'id', ['c_name' => 'tracker', $GLOBALS['SITE_DB']->translate_field_ref('cf_name') => do_lang('IDENTIFIER')]);
            $relationship_field = $GLOBALS['SITE_DB']->query_select_value('catalogue_fields', 'id', ['c_name' => 'tracker', $GLOBALS['SITE_DB']->translate_field_ref('cf_name') => do_lang('RELATED_TO')]);

            $start = 0;
            $max = 100;
            $rows = [];
            do {
                $rows = $GLOBALS['SITE_DB']->query('SELECT * FROM mantis_bug_relationship_table', $max, $start);
                foreach ($rows as $row) {
                    $source_entry = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_efv_integer', 'ce_id', ['cf_id' => $identifier_field, 'cv_value' => $row['source_bug_id']]);
                    $destination_entry = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_efv_integer', 'ce_id', ['cf_id' => $identifier_field, 'cv_value' => $row['destination_bug_id']]);

                    if (($source_entry === null) || ($destination_entry === null)) {
                        continue;
                    }

                    $new_field = false;
                    $current_relationships = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_efv_long', 'cv_value', ['cf_id' => $relationship_field, 'ce_id' => $source_entry]);
                    if ($current_relationships === null) {
                        $current_relationships = '';
                        $new_field = true;
                    } elseif (trim($current_relationships != '')) {
                        $current_relationships .= "\n";
                    }

                    $current_relationships .= strval($destination_entry);

                    if ($new_field) {
                        $GLOBALS['SITE_DB']->query_insert('catalogue_efv_long', ['cf_id' => $relationship_field, 'ce_id' => $source_entry, 'cv_value' => strval($current_relationships)]);
                    } else {
                        $GLOBALS['SITE_DB']->query_update('catalogue_efv_long', ['cv_value' => strval($current_relationships)], ['cf_id' => $relationship_field, 'ce_id' => $source_entry]);
                    }
                }

                $start += $max;
            } while (count($rows) > 0);

            // step 7: Migrate bug notes as comments
            $identifier_field = $GLOBALS['SITE_DB']->query_select_value('catalogue_fields', 'id', ['c_name' => 'tracker', $GLOBALS['SITE_DB']->translate_field_ref('cf_name') => do_lang('IDENTIFIER')]);
            $title_field = $GLOBALS['SITE_DB']->query_select_value('catalogue_fields', 'id', ['c_name' => 'tracker', $GLOBALS['SITE_DB']->translate_field_ref('cf_name') => do_lang('TITLE')]);
            $commit_field = $GLOBALS['SITE_DB']->query_select_value('catalogue_fields', 'id', ['c_name' => 'tracker', $GLOBALS['SITE_DB']->translate_field_ref('cf_name') => do_lang('COMMITS')]);

            $start = 0;
            $max = 100;
            $rows = [];
            do {
                $rows = $GLOBALS['SITE_DB']->query('SELECT * FROM mantis_bugnote_table ORDER BY date_submitted ASC', $max, $start);
                foreach ($rows as $row) {
                    $entry_id = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_efv_integer', 'ce_id', ['cf_id' => $identifier_field, 'cv_value' => $row['bug_id']]);
                    if ($entry_id === null) {
                        continue;
                    }

                    $title = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_efv_short', 'cv_value', ['cf_id' => $title_field, 'ce_id' => $entry_id]);
                    if ($title === null) {
                        $title = do_lang('NA');
                    }

                    $_text = $GLOBALS['SITE_DB']->query_parameterised('SELECT note FROM mantis_bugnote_text_table WHERE id={id}', ['id' => $row['bugnote_text_id']]);
                    if (array_key_exists(0, $_text)) {
                        $text = $_text[0]['note'] . '{$,page hint: render_raw}';
                    } else {
                        $text = '{$,page hint: render_raw}';
                    }

                    // Skip automated messages about using the wizard
                    if (strpos($text, 'Automated message: This issue was created using the Report Issue Wizard on the homesite.') !== false) {
                        continue;
                    }

                    // Actually, this is a commit message; put URL in the commit field instead of making a comment
                    $matches = [];
                    if (preg_match('#^Fixed\s+in\s+Git\s+commit\s+[a-fA-F0-9]+\s+\(([^)\s]+).*?\)#i', $text, $matches) > 0) {
                        $new_field = false;
                        $current_commits = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_efv_long', 'cv_value', ['cf_id' => $commit_field, 'ce_id' => $entry_id]);
                        if ($current_commits === null) {
                            $current_commits = '';
                            $new_field = true;
                        } elseif (trim($current_commits != '')) {
                            $current_commits .= "\n";
                        }

                        $current_commits .= $matches[1];

                        if ($new_field) {
                            $GLOBALS['SITE_DB']->query_insert('catalogue_efv_long', ['cf_id' => $commit_field, 'ce_id' => $entry_id, 'cv_value' => strval($current_commits)]);
                        } else {
                            $GLOBALS['SITE_DB']->query_update('catalogue_efv_long', ['cv_value' => strval($current_commits)], ['cf_id' => $commit_field, 'ce_id' => $entry_id]);
                        }

                        continue;
                    }

                    actualise_post_comment(
                        true,
                        'catalogue_entry',
                        strval($entry_id),
                        build_url(['page' => 'catalogues', 'type' => 'entry', 'id' => $entry_id], get_module_zone('catalogues')),
                        '#' . strval($row['bug_id']) . ' - ' . $title,
                        null,
                        false,
                        1,
                        true,
                        false,
                        true, // Mass import, so we do not want notifications
                        '', // Not supported in Mantis, and null means use POST, so use a blank string
                        $text,
                        $row['date_submitted'],
                        $row['reporter_id'],
                        ($row['view_state'] == 50)
                    );
                }

                $start += $max;
            } while (count($rows) > 0);

            // step 8: Migrate bug files as their own comments
            $identifier_field = $GLOBALS['SITE_DB']->query_select_value('catalogue_fields', 'id', ['c_name' => 'tracker', $GLOBALS['SITE_DB']->translate_field_ref('cf_name') => do_lang('IDENTIFIER')]);
            $title_field = $GLOBALS['SITE_DB']->query_select_value('catalogue_fields', 'id', ['c_name' => 'tracker', $GLOBALS['SITE_DB']->translate_field_ref('cf_name') => do_lang('TITLE')]);
            $hotfix_field = $GLOBALS['SITE_DB']->query_select_value('catalogue_fields', 'id', ['c_name' => 'tracker', $GLOBALS['SITE_DB']->translate_field_ref('cf_name') => do_lang('HOTFIXES')]);

            $start = 0;
            $max = 100;
            $files = [];
            do {
                $files = $GLOBALS['SITE_DB']->query('SELECT * FROM mantis_bug_file_table', $max, $start);
                foreach ($files as $i => $file) {
                    $filename = $file['diskfile'];
                    $relativepath = 'tracker_legacy/uploads/' . $file['diskfile'];
                    $realpath = get_custom_file_base() . '/tracker_legacy/uploads/' . $file['diskfile'];
                    if (!is_file($realpath)) {
                        continue;
                    }

                    // We must rename to the actual file name so extension checks work correctly
                    if ($file['diskfile'] != $file['filename']) {
                        @copy($realpath, get_file_base() . '/tracker_legacy/uploads/' . $file['filename']);
                        $filename = $file['filename'];
                        $relativepath = 'tracker_legacy/uploads/' . $file['filename'];
                        $realpath = get_custom_file_base() . '/tracker_legacy/uploads/' . $file['filename'];
                        if (!is_file($realpath)) {
                            continue;
                        }
                    }

                    // Check the file extension; we must do this outside of Comcode renderer as we have a different way of checking Mantis files
                    $fake_path = $relativepath;
                    if (!check_extension($fake_path, false, null, true, $file['user_id'])) {
                        continue;
                    }

                    $entry_id = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_efv_integer', 'ce_id', ['cf_id' => $identifier_field, 'cv_value' => $file['bug_id']]);
                    if ($entry_id === null) {
                        continue;
                    }

                    $title = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_efv_short', 'cv_value', ['cf_id' => $title_field, 'ce_id' => $entry_id]);
                    if ($title === null) {
                        $title = do_lang('NA');
                    }

                    // Actually, this is a hotfix. Add to the hotfix field instead of as a media file comment.
                    if ((strpos($file['filename'], 'hotfix-') === 0) && (substr($file['filename'], -4) === '.tar')) {
                        $_POST['hotfix_' . strval($i)] = get_base_url() . '/' . $relativepath; // FUDGE

                        $temp = get_url('hotfix_' . strval($i), '', 'uploads/catalogues', OBFUSCATE_LEAVE_SUFFIX, CMS_UPLOAD_ANYTHING, false, '', '', true, true);
                        if ($temp[0] == '') {
                            continue;
                        }

                        $field_value = $temp[0] . '::' . $file['filename'];

                        $old_hotfix = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_efv_long', 'cv_value', ['cf_id' => $hotfix_field, 'ce_id' => $entry_id]);
                        if ($old_hotfix === null) {
                            $GLOBALS['SITE_DB']->query_insert('catalogue_efv_long', ['cf_id' => $hotfix_field, 'ce_id' => $entry_id, 'cv_value' => $field_value]);
                        } else {
                            $GLOBALS['SITE_DB']->query_update('catalogue_efv_long', ['cv_value' => $old_hotfix . "\n" . $field_value], ['cf_id' => $hotfix_field, 'ce_id' => $entry_id]);
                        }

                        reorganise_uploads__catalogue_entries(['ce_id' => $entry_id], true);
                        continue;
                    }

                    // Prefer media file, else use an attachment
                    if (addon_installed('filedump')) {
                        add_filedump_file('/tracker_legacy/', $filename, $realpath, $file['description'], false, false, 'rename');
                        $text = '[media]uploads/filedump/tracker_legacy/' . $filename . '[/media]';
                    } else {
                        $_POST['file' . strval($i)] = get_base_url() . '/' . $relativepath;
                        $text = '[attachment thumb="1" description="' . comcode_escape($file['description']) . '" filename="' . comcode_escape($file['filename']) . '"]post_' . strval($i) . '[/attachment]';
                    }

                    actualise_post_comment(
                        true,
                        'catalogue_entry',
                        strval($entry_id),
                        build_url(['page' => 'catalogues', 'type' => 'entry', 'id' => $entry_id], get_module_zone('catalogues')),
                        '#' . strval($file['bug_id']) . ' - ' . $title,
                        null,
                        false,
                        1,
                        true,
                        false,
                        true, // Mass import, so we do not want notifications
                        '', // Not supported in Mantis, and null means use POST, so use a blank string
                        $text,
                        $file['date_added'],
                        $file['user_id'],
                        false, // No way to tell because it is not attached to a bug note
                    );
                }

                $start += $max;
            } while (count($files) > 0);

            // step 9: Migrate tags
            $rows = $GLOBALS['SITE_DB']->query('SELECT `id`,`name` FROM mantis_tag_table');
            $tags = collapse_2d_complexity('id', 'name', $rows);

            $identifier_field = $GLOBALS['SITE_DB']->query_select_value('catalogue_fields', 'id', ['c_name' => 'tracker', $GLOBALS['SITE_DB']->translate_field_ref('cf_name') => do_lang('IDENTIFIER')]);
            $tag_field = $GLOBALS['SITE_DB']->query_select_value('catalogue_fields', 'id', ['c_name' => 'tracker', $GLOBALS['SITE_DB']->translate_field_ref('cf_name') => do_lang('ISSUE_TAGS')]);

            $start = 0;
            $max = 100;
            $rows = [];
            do {
                $rows = $GLOBALS['SITE_DB']->query('SELECT * FROM mantis_bug_tag_table', $max, $start);
                foreach ($rows as $row) {
                    $source_entry = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_efv_integer', 'ce_id', ['cf_id' => $identifier_field, 'cv_value' => $row['bug_id']]);

                    if (($source_entry === null) || (!isset($tags[$row['tag_id']]))) {
                        continue;
                    }

                    $new_field = false;
                    $current_tags = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_efv_long', 'cv_value', ['cf_id' => $tag_field, 'ce_id' => $source_entry]);
                    if ($current_tags === null) {
                        $current_tags = '';
                        $new_field = true;
                    } elseif (trim($current_tags != '')) {
                        $current_tags .= "\n";
                    }

                    $current_tags .= strval($tags[$row['tag_id']]);

                    if ($new_field) {
                        $GLOBALS['SITE_DB']->query_insert('catalogue_efv_long', ['cf_id' => $tag_field, 'ce_id' => $source_entry, 'cv_value' => strval($current_tags)]);
                    } else {
                        $GLOBALS['SITE_DB']->query_update('catalogue_efv_long', ['cv_value' => strval($current_tags)], ['cf_id' => $tag_field, 'ce_id' => $source_entry]);
                    }
                }

                $start += $max;
            } while (count($rows) > 0);

            // step 10: Migrate monitoring (as comment_posted notifications)
            $identifier_field = $GLOBALS['SITE_DB']->query_select_value('catalogue_fields', 'id', ['c_name' => 'tracker', $GLOBALS['SITE_DB']->translate_field_ref('cf_name') => do_lang('IDENTIFIER')]);
            $start = 0;
            $max = 100;
            $rows = [];
            do {
                $rows = $GLOBALS['SITE_DB']->query('SELECT * FROM mantis_bug_monitor_table', $max, $start);
                foreach ($rows as $row) {
                    $source_entry = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_efv_integer', 'ce_id', ['cf_id' => $identifier_field, 'cv_value' => $row['bug_id']]);
                    if ($source_entry === null) {
                        continue;
                    }

                    set_notifications('comment_posted', 'catalogue_entry_' . strval($source_entry), $row['user_id']);
                }

                $start += $max;
            } while (count($rows) > 0);


            $NOTIFICATIONS_ON = true;
            set_mass_import_mode(false);
            pop_query_limiting();
            // TODO: Modify or create notification types for the tracker
            // TODO: be sure to add a contentious override that throws an error when a non-staff who did not create an issue tries to view a type security issue.
            // TODO: add custom templates for the catalogue
            // TODO: make a sponsorship block and include it on viewing an issue (catalogue entry) template
            // TODO: Modify and rename the mantis API; make sure it also works with the endpoints
            // TODO: Hook into comments so that they are sorted by oldest first on tracker entries. Or, better yet, make them display as a topic.
            // TODO: Fix everything that has Mantis (hooks, blocks, etc)

            // Step x: Uninstall Mantis
            $tables = [
                'mantis_api_token_table',
                'mantis_bug_file_table',
                'mantis_bug_history_table',
                'mantis_bug_monitor_table',
                'mantis_bug_relationship_table',
                'mantis_bug_revision_table',
                'mantis_bug_table',
                'mantis_bug_tag_table',
                'mantis_bug_text_table',
                'mantis_bugnote_table',
                'mantis_bugnote_text_table',
                'mantis_category_table',
                'mantis_config_table',
                'mantis_custom_field_project_table',
                'mantis_custom_field_string_table',
                'mantis_custom_field_table',
                'mantis_email_table',
                'mantis_filters_table',
                'mantis_news_table',
                'mantis_plugin_table',
                'mantis_project_file_table',
                'mantis_project_hierarchy_table',
                'mantis_project_table',
                'mantis_project_user_list_table',
                'mantis_project_version_table',
                'mantis_sponsorship_table',
                'mantis_tag_table',
                'mantis_tokens_table',
                'mantis_user_pref_table',
                'mantis_user_print_pref_table',
                'mantis_user_profile_table',
                'mantis_user_table',
            ];
            $GLOBALS['SITE_DB']->query('DROP TABLE IF EXISTS ' . implode(',', $tables));
        }
    }
}
