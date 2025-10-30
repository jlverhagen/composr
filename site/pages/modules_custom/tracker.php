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
 * Module page class.
 */
class Module_tracker
{
    /**
     * Find details of the module.
     *
     * @return ?array Map of module info (null: module is disabled)
     */
    public function info() : ?array
    {
        $info = [];
        $info['author'] = 'Chris Graham';
        $info['organisation'] = 'Composr';
        $info['hacked_by'] = null;
        $info['hack_version'] = null;
        $info['version'] = 5;
        $info['update_require_upgrade'] = true;
        $info['locked'] = false;
        $info['min_cms_version'] = 11.0;
        $info['addon'] = 'cms_homesite_tracker';
        return $info;
    }

    /**
     * Uninstall the module.
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
     * Install the module.
     *
     * @param  ?integer $upgrade_from What version we're upgrading from (null: new install)
     * @param  ?integer $upgrade_from_hack What hack version we're upgrading from (null: new-install/not-upgrading-from-a-hacked-version)
     */
    public function install(?int $upgrade_from = null, ?int $upgrade_from_hack = null)
    {
        if (($upgrade_from === null) || ($upgrade_from < 5)) { // 11.beta9
            cms_extend_time_limit(TIME_LIMIT_EXTEND__SLOW);
            raise_php_memory_limit();

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
                ['IDENTIFIER', 'DESCRIPTION_TRACKER_CATALOGUE_IDENTIFIER', 'tracker_id', 1, 1, 1, '', 0, 1, 0, ''],
                ['ISSUE_TYPE', 'DESCRIPTION_TRACKER_CATALOGUE_ISSUE_TYPE', 'list', 0, 1, 1, 'display_val=on', 0, 1, 1, 'TRACKER_CATALOGUE_ISSUE_TYPE_DEFAULT'],
                ['TITLE', 'DESCRIPTION_TRACKER_CATALOGUE_TITLE', 'short_text', 0, 1, 1, 'input_size=56', 0, 1, 0, ''],
                ['STATUS', 'DESCRIPTION_TRACKER_CATALOGUE_STATUS', 'list', 0, 1, 1, 'display_val=on,edit_only=1', 0, 1, 1, 'TRACKER_CATALOGUE_STATUS_DEFAULT'],
                ['ISSUE_TAGS', 'DESCRIPTION_TRACKER_CATALOGUE_TAGS', 'list_multi', 0, 0, 1, 'custom_values=multiple,edit_only=1,widget=vertical_checkboxes', 0, 0, 1, ''],
                ['HANDLER', 'DESCRIPTION_TRACKER_CATALOGUE_HANDLER', 'member', 0, 0, 1, 'edit_only=1', 0, 0, 0, ''],
                ['VERSION', 'DESCRIPTION_TRACKER_CATALOGUE_VERSION', 'version', 0, 0, 1, '', 0, 0, 1, ''],
                ['ADDON', 'DESCRIPTION_TRACKER_CATALOGUE_ADDON', 'addon', 0, 0, 1, 'auto_sort=on', 0, 0, 1, ''],
                ['DESCRIPTION', 'DESCRIPTION_TRACKER_CATALOGUE_DESCRIPTION', 'long_trans', 0, 1, 1, '', 1, 0, 0, ''],
                ['STEPS_TO_REPRODUCE', 'DESCRIPTION_TRACKER_CATALOGUE_STEPS_TO_REPRODUCE', 'short_trans_multi', 0, 0, 1, '', 1, 0, 0, ''],
                ['ADDITIONAL_INFORMATION', 'DESCRIPTION_TRACKER_CATALOGUE_ADDITIONAL_INFORMATION', 'long_trans', 0, 0, 1, '', 1, 0, 0, ''],
                ['RELATED_TO', 'DESCRIPTION_TRACKER_CATALOGUE_RELATED_TO', 'cx_tracker', 0, 0, 1, 'edit_only=1', 0, 0, 0, ''],
                ['IS_FUNDED', 'DESCRIPTION_TRACKER_CATALOGUE_IS_FUNDED', 'tick', 0, 1, 1, 'edit_only=1', 0, 1, 1, 'TRACKER_CATALOGUE_IS_FUNDED_DEFAULT'],
                ['RELEASED_IN_VERSION', 'DESCRIPTION_TRACKER_CATALOGUE_RELEASED_IN_VERSION', 'version', 0, 0, 1, 'edit_only=1', 0, 0, 1, ''],
                ['HOTFIX', 'DESCRIPTION_TRACKER_CATALOGUE_HOTFIX', 'upload', 0, 0, 1, 'edit_only=1', 0, 0, 0, ''],
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

        if (($upgrade_from !== null) && ($upgrade_from < 5)) { // LEGACY: 11.beta9
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
                ['IDENTIFIER', 'DESCRIPTION_TRACKER_CATALOGUE_IDENTIFIER', 'tracker_id', 1, 1, 1, '', 0, 1, 0, ''],
                ['ISSUE_TYPE', 'DESCRIPTION_TRACKER_CATALOGUE_ISSUE_TYPE', 'list', 0, 1, 1, 'display_val=on', 0, 1, 1, 'TRACKER_CATALOGUE_ISSUE_TYPE_DEFAULT'],
                ['TITLE', 'DESCRIPTION_TRACKER_CATALOGUE_TITLE', 'short_text', 0, 1, 1, 'input_size=56', 0, 1, 0, ''],
                ['STATUS', 'DESCRIPTION_TRACKER_CATALOGUE_STATUS', 'list', 0, 1, 1, 'display_val=on,edit_only=1', 0, 1, 1, 'TRACKER_CATALOGUE_STATUS_DEFAULT'],
                ['ISSUE_TAGS', 'DESCRIPTION_TRACKER_CATALOGUE_TAGS', 'list_multi', 0, 0, 1, 'custom_values=multiple,edit_only=1,widget=vertical_checkboxes', 0, 0, 1, ''],
                ['HANDLER', 'DESCRIPTION_TRACKER_CATALOGUE_HANDLER', 'member', 0, 0, 1, 'edit_only=1', 0, 0, 0, ''],
                ['VERSION', 'DESCRIPTION_TRACKER_CATALOGUE_VERSION', 'version', 0, 0, 1, '', 0, 0, 1, ''],
                ['ADDON', 'DESCRIPTION_TRACKER_CATALOGUE_ADDON', 'addon', 0, 0, 1, 'auto_sort=on', 0, 0, 1, ''],
                ['DESCRIPTION', 'DESCRIPTION_TRACKER_CATALOGUE_DESCRIPTION', 'long_trans', 0, 1, 1, '', 1, 0, 0, ''],
                ['STEPS_TO_REPRODUCE', 'DESCRIPTION_TRACKER_CATALOGUE_STEPS_TO_REPRODUCE', 'short_trans_multi', 0, 0, 1, '', 1, 0, 0, ''],
                ['ADDITIONAL_INFORMATION', 'DESCRIPTION_TRACKER_CATALOGUE_ADDITIONAL_INFORMATION', 'long_trans', 0, 0, 1, '', 1, 0, 0, ''],
                ['RELATED_TO', 'DESCRIPTION_TRACKER_CATALOGUE_RELATED_TO', 'cx_tracker', 0, 0, 1, 'edit_only=1', 0, 0, 0, ''],
                ['IS_FUNDED', 'DESCRIPTION_TRACKER_CATALOGUE_IS_FUNDED', 'tick', 0, 1, 1, 'edit_only=1', 0, 1, 1, 'TRACKER_CATALOGUE_IS_FUNDED_DEFAULT'],
                ['RELEASED_IN_VERSION', 'DESCRIPTION_TRACKER_CATALOGUE_RELEASED_IN_VERSION', 'version', 0, 0, 1, 'edit_only=1', 0, 0, 1, ''],
                ['HOTFIX', 'DESCRIPTION_TRACKER_CATALOGUE_HOTFIX', 'upload', 0, 0, 1, 'edit_only=1', 0, 0, 0, ''],
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

            // step 5: Migrate Mantis issues, and also re-map points ledger t_type and t_type_id for tracker issues
            set_mass_import_mode(true);
            push_query_limiting(false);

            require_code('cms_homesite_tracker');
            require_lang('tracker');

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
                50 => 'minor',
                60 => 'major',
                95 => 'security',
            ];

            $start = 0;
            $max = 100;
            $rows = [];
            do {
                $rows = $GLOBALS['SITE_DB']->query('SELECT * FROM mantis_bug_table', $max, $start);
                foreach ($rows as $row) {
                    $bug_info = $GLOBALS['SITE_DB']->query_parameterised('SELECT * FROM mantis_bug_text_table WHERE id={id}', ['id' => $row['bug_text_id']]);
                    $category_info = $GLOBALS['SITE_DB']->query_parameterised('SELECT * FROM mantis_category_table WHERE id={id}', ['id' => $row['category_id']]);

                    if (!isset($bug_info[0]) || !isset($category_info[0])) {
                        continue;
                    }

                    $ids = create_tracker_issue(
                        $row['version'],
                        $row['summary'],
                        $severity_map[$row['severity']],
                        $bug_info[0]['description'],
                        $bug_info[0]['additional_information'],
                        $category_info[0]['name'],
                        $project_map[$row['project_id']],
                        (($row['handler_id'] != 0) && (!is_guest($row['handler_id']))) ? $row['handler_id'] : null,
                        $bug_info[0]['steps_to_reproduce'],
                        $resolution_map[$row['resolution']],
                        $row['date_submitted'],
                        $row['reporter_id'],
                        $row['id']
                    );

                    $GLOBALS['SITE_DB']->query_update('points_ledger', ['t_type' => 'catalogue_entry', 't_type_id' => strval($ids[0])], ['t_type' => 'tracker_issue', 't_type_id' => strval($row['id'])]);
                    $GLOBALS['SITE_DB']->query_update('escrow', ['content_type' => 'catalogue_entry', 'content_id' => strval($ids[0])], ['content_type' => 'tracker_issue', 'content_id' => strval($row['id'])]);
                }

                $start += $max;
            } while (count($rows) > 0);

            set_mass_import_mode(false);

            // TODO step 6: Migrate issue relations (needs testing)
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

            pop_query_limiting();
            // TODO step 7: Migrate bug notes as comments
            // TODO step 8: Migrate monitor status
            // TODO step 9: Migrate tags
            // TODO step 10: Migrate sponsorships
            // TODO: Modify or create notification types for the tracker
            // TODO: be sure to add a contentious override that throws an error when a non-staff who did not create an issue tries to view a type security issue.
            // TODO: add custom templates for the catalogue
            // TODO: make a sponsorship block and include it on viewing an issue (catalogue entry) template
            // TODO: Modify and rename the mantis API; make sure it also works with the endpoints

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
            $GLOBALS['SITE_DB']->query('DROP TABLE IF EXISTS ' . implode(',' , $tables));

            require_code('files');
            deldir_contents(get_file_base() . '/tracker', false, true);
        }
    }

    /**
     * Find entry-points available within this module.
     *
     * @param  boolean $check_perms Whether to check permissions
     * @param  ?MEMBER $member_id The member to check permissions as (null: current user)
     * @param  boolean $support_crosslinks Whether to allow cross links to other modules (identifiable via a full-page-link rather than a screen-name)
     * @param  boolean $be_deferential Whether to avoid any entry-point (or even return null to disable the page in the Sitemap) if we know another module, or page_group, is going to link to that entry-point. Note that "!" and "browse" entry points are automatically merged with container page nodes (likely called by page-groupings) as appropriate.
     * @return ?array A map of entry points (screen-name=>language-code/string or screen-name=>[language-code/string, icon-theme-image]) (null: disabled)
     */
    public function get_entry_points(bool $check_perms = true, ?int $member_id = null, bool $support_crosslinks = true, bool $be_deferential = false) : ?array
    {
        require_lang('tracker');

        if (!addon_installed('cms_homesite_tracker')) {
            return null;
        }

        return [
            'browse' => ['TRACKER', 'menu/rich_content/catalogues/catalogues'], // TODO: custom icon
        ];
    }

    public $title;

    /**
     * Module pre-run function. Allows us to know metadata for <head> before we start streaming output.
     *
     * @return ?Tempcode Tempcode indicating some kind of exceptional output (null: none)
     */
    public function pre_run() : ?object
    {
        i_solemnly_declare(I_UNDERSTAND_SQL_INJECTION | I_UNDERSTAND_XSS | I_UNDERSTAND_PATH_INJECTION);

        $error_msg = new Tempcode();
        if (!addon_installed__messaged('cms_homesite_tracker', $error_msg)) {
            return $error_msg;
        }

        require_lang('tracker');

        $type = get_param_string('type', 'browse');

        if ($type == 'browse') {
            require_lang('tracker');
            $this->title = get_screen_title('MANTIS_TRACKER_PAGE_TITLE');
        }

        return null;
    }

    /**
     * Execute the module.
     *
     * @return Tempcode The result of execution
     */
    public function run() : object
    {
        $type = get_param_string('type', 'browse');

        // Decide what to do
        if ($type == 'browse') {
            return $this->tracker(); // NB: This may be skipped, if blocks were used to access
        }

        return new Tempcode();
    }

    /**
     * UI for showing the issue tracker.
     *
     * @return Tempcode The result of execution
     */
    public function tracker() : object
    {
        if (!addon_installed('cms_homesite_tracker')) {
            warn_exit(do_lang_tempcode('MISSING_ADDON', escape_html('cms_homesite_tracker')));
        }

        require_lang('tracker');

        $GLOBALS['SITE_INFO']['block_url_schemes'] = '1';

        $content = paragraph(do_lang_tempcode('MANTIS_TRACKER_PAGE_TEXT'));
        $content->attach(do_block('main_mantis_tracker', ['sort' => 'sponsorships DESC']));

        // Display
        return do_template('STANDALONE_HTML_WRAP', ['_GUID' => '11f8625ed8fc9cf4fa6f466f08b7be57', 'FRAME' => true, 'NOINDEX' => true, 'TARGET' => '_blank', 'TITLE' => 'Tracker', 'CONTENT' => $content]);
    }
}
