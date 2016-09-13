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
 * @package    core_cns
 */

/**
 * Standard code module initialisation function.
 *
 * @ignore
 */
function init__cns_groups()
{
    global $USER_GROUPS_CACHED;
    $USER_GROUPS_CACHED = array();

    global $GROUP_MEMBERS_CACHE;
    $GROUP_MEMBERS_CACHE = array();

    global $PROBATION_GROUP_CACHE;
    $PROBATION_GROUP_CACHE = null;

    global $ALL_DEFAULT_GROUPS_CACHE;
    $ALL_DEFAULT_GROUPS_CACHE = array();
}

/**
 * Render a usergroup box.
 *
 * @param  array $row Usergroup row
 * @param  ID_TEXT $zone Zone to link through to
 * @param  boolean $give_context Whether to include context (i.e. say WHAT this is, not just show the actual content)
 * @param  ID_TEXT $guid Overridden GUID to send to templates (blank: none)
 * @return Tempcode The usergroup box
 */
function render_group_box($row, $zone = '_SEARCH', $give_context = true, $guid = '')
{
    if (is_null($row)) { // Should never happen, but we need to be defensive
        return new Tempcode();
    }

    require_lang('cns');

    $url = build_url(array('page' => 'groups', 'type' => 'view', 'id' => $row['id']), get_module_zone('groups'));

    $_title = cns_get_group_name($row['id']);
    $title = $give_context ? do_lang('CONTENT_IS_OF_TYPE', do_lang('GROUP'), $_title) : $_title;

    $summary = get_translated_text($row['g_name'], $GLOBALS['FORUM_DB']);

    require_code('cns_groups2');
    $num_members = cns_get_group_members_raw_count($row['id']);
    $entry_details = do_lang_tempcode('GROUP_NUM_MEMBERS', escape_html(integer_format($num_members)));

    return do_template('SIMPLE_PREVIEW_BOX', array(
        '_GUID' => ($guid != '') ? $guid : 'efeac1c8465974edd27bb0d805c4fbe0',
        'ID' => strval($row['id']),
        'TITLE' => $title,
        'TITLE_PLAIN' => $_title,
        'SUMMARY' => $summary,
        'ENTRY_DETAILS' => $entry_details,
        'URL' => $url,
        'FRACTIONAL_EDIT_FIELD_NAME' => $give_context ? null : 'name',
        'FRACTIONAL_EDIT_FIELD_URL' => $give_context ? null : '_SEARCH:admin_cns_groups:__edit:' . strval($row['id']),
        'RESOURCE_TYPE' => 'group',
    ));
}

/**
 * Get a nice list for selection from the usergroups. Suitable for admin use only (does not check hidden status).
 *
 * @param  ?AUTO_LINK $it Usergroup selected by default (null: no specific default).
 * @param  boolean $allow_guest_group Allow the guest usergroup to be in the list.
 * @return Tempcode The list.
 */
function cns_create_selection_list_usergroups($it = null, $allow_guest_group = true)
{
    $group_count = $GLOBALS['FORUM_DB']->query_select_value('f_groups', 'COUNT(*)');
    $_m = $GLOBALS['FORUM_DB']->query_select('f_groups', array('id', 'g_name'), ($group_count > 200) ? array('g_is_private_club' => 0) : null, 'ORDER BY g_order,' . $GLOBALS['FORUM_DB']->translate_field_ref('g_name'));
    $entries = new Tempcode();
    foreach ($_m as $m) {
        if (!$allow_guest_group && $m['id'] == db_get_first_id()) {
            continue;
        }

        $entries->attach(form_input_list_entry(strval($m['id']), $it === $m['id'], get_translated_text($m['g_name'], $GLOBALS['FORUM_DB'])));
    }

    return $entries;
}

/**
 * Find the first default group.
 *
 * @return GROUP The first default group.
 */
function get_first_default_group()
{
    $default_groups = cns_get_all_default_groups(true);
    return array_pop($default_groups);
}

/**
 * Get a list of the default usergroups (the usergroups a member is put in when they join).
 *
 * @param  boolean $include_primary Whether to include the default primary (at the end of the list).
 * @param  boolean $include_all_configured_default_groups The functionality does not usually consider configured default groups [unless there's just one], because this is a layer of uncertainity (the user PICKS one of these). If you want to return all configured default groups, set this parameter to true.
 * @return array The list of default IDs.
 */
function cns_get_all_default_groups($include_primary = false, $include_all_configured_default_groups = false)
{
    if ((!$include_primary) && ($include_all_configured_default_groups)) {
        warn_exit(do_lang_tempcode('INTERNAL_ERROR'));
    }

    global $ALL_DEFAULT_GROUPS_CACHE;
    if (array_key_exists($include_primary ? 1 : 0, $ALL_DEFAULT_GROUPS_CACHE)) {
        return $ALL_DEFAULT_GROUPS_CACHE[$include_primary ? 1 : 0];
    }

    $rows = $GLOBALS['FORUM_DB']->query_select('f_groups', array('id', 'g_name'), array('g_is_default' => 1, 'g_is_presented_at_install' => 0), 'ORDER BY g_order,' . $GLOBALS['FORUM_DB']->translate_field_ref('g_name'));
    $groups = collapse_1d_complexity('id', $rows);

    if ($include_primary) {
        $rows = $GLOBALS['FORUM_DB']->query_select('f_groups', array('id', 'g_name'), array('g_is_presented_at_install' => 1), 'ORDER BY g_order,' . $GLOBALS['FORUM_DB']->translate_field_ref('g_name'));
        if (($include_all_configured_default_groups) || (count($rows) == 1) || (get_option('show_first_join_page') == '0')) { // If just 1 then we won't have presented a choice on the join form, so should inject that 1 as the default group as it is implied
            $groups = array_merge($groups, collapse_1d_complexity('id', $rows));
        }

        if (count($rows) == 0) {
            $test = $GLOBALS['FORUM_DB']->query_select_value_if_there('f_groups', 'id', array('id' => db_get_first_id() + 8));
            if (!is_null($test)) {
                $groups[] = db_get_first_id() + 8;
            }
        }
    }

    $ALL_DEFAULT_GROUPS_CACHE[$include_primary ? 1 : 0] = $groups;
    return $groups;
}

/**
 * Ensure a list of usergroups are cached in memory.
 *
 * @param  mixed $groups The list of usergroups (array) or '*'.
 */
function cns_ensure_groups_cached($groups)
{
    global $USER_GROUPS_CACHED;

    if ($groups === '*') {
        $group_count = $GLOBALS['FORUM_DB']->query_select_value('f_groups', 'COUNT(*)');
        $rows = $GLOBALS['FORUM_DB']->query_select('f_groups', array('*'), ($group_count > 200) ? array('g_is_private_club' => 0) : null);
        foreach ($rows as $row) {
            $row['g__name'] = get_translated_text($row['g_name'], $GLOBALS['FORUM_DB']);
            $row['g__title'] = get_translated_text($row['g_title'], $GLOBALS['FORUM_DB']);
            $USER_GROUPS_CACHED[$row['id']] = $row;
        }
        return;
    }

    $count = persistent_cache_get('GROUPS_COUNT');

    $groups_to_load = '';
    $counter = 0;
    foreach ($groups as $group) {
        if (!array_key_exists($group, $USER_GROUPS_CACHED)) {
            if (($count !== null) && ($count < 100)) {
                $USER_GROUPS_CACHED[$group] = persistent_cache_get('GROUP_' . strval($group));
                if ($USER_GROUPS_CACHED[$group] !== null) {
                    continue;
                }
            }

            if ($groups_to_load != '') {
                $groups_to_load .= ' OR ';
            }
            $groups_to_load .= 'g.id=' . strval($group);
            $counter++;
        }
    }
    if ($counter == 0) {
        return;
    }
    $extra_groups = $GLOBALS['FORUM_DB']->query('SELECT g.* FROM ' . $GLOBALS['FORUM_DB']->get_table_prefix() . 'f_groups g WHERE ' . $groups_to_load, null, null, false, true, array('g_name' => 'SHORT_TRANS', 'g_title' => 'SHORT_TRANS'));

    if (count($extra_groups) != $counter) {
        warn_exit(do_lang_tempcode('MISSING_RESOURCE', 'group'));
    }

    foreach ($extra_groups as $extra_group) {
        if (function_exists('get_translated_text')) {
            $extra_group['g__name'] = get_translated_text($extra_group['g_name'], $GLOBALS['FORUM_DB']);
            $extra_group['g__title'] = get_translated_text($extra_group['g_title'], $GLOBALS['FORUM_DB']);
        }

        $USER_GROUPS_CACHED[$extra_group['id']] = $extra_group;

        if (($count !== null) && ($count < 100)) {
            persistent_cache_set('GROUP_' . strval($extra_group['id']), $extra_group);
        }
    }
}

/**
 * Get a rendered link to a usergroup.
 *
 * @param  GROUP $id The ID of the group.
 * @param  boolean $hide_hidden Whether to hide the name if it is a hidden group.
 * @return Tempcode The link.
 */
function cns_get_group_link($id, $hide_hidden = true)
{
    $_row = $GLOBALS['FORUM_DB']->query_select('f_groups', array('*'), array('id' => $id), '', 1);
    if (!array_key_exists(0, $_row)) {
        return make_string_tempcode(do_lang('UNKNOWN'));
    }
    $row = $_row[0];

    if ($row['id'] == db_get_first_id()) {
        return make_string_tempcode(escape_html(get_translated_text($row['g_name'], $GLOBALS['FORUM_DB'])));
    }

    $name = cns_get_group_name($row['id'], $hide_hidden);

    $members_groups = $GLOBALS['CNS_DRIVER']->get_members_groups(get_member());

    $see_hidden = has_privilege(get_member(), 'see_hidden_groups');
    if ((!$see_hidden) && ($row['g_hidden'] == 1) && (!in_array($id, $members_groups))) {
        return make_string_tempcode(escape_html($name));
    }

    return hyperlink(build_url(array('page' => 'groups', 'type' => 'view', 'id' => $row['id']), get_module_zone('groups')), $name, false, true);
}

/**
 * Get a usergroup name.
 *
 * @param  GROUP $group The ID of the group.
 * @param  boolean $hide_hidden Whether to hide the name if it is a hidden group.
 * @return string The usergroup name.
 */
function cns_get_group_name($group, $hide_hidden = true)
{
    $name = cns_get_group_property($group, 'name', $hide_hidden);
    if (is_string($name)) {
        return $name;
    }
    return get_translated_text($name, $GLOBALS['FORUM_DB']);
}

/**
 * Get a certain property of a certain.
 *
 * @param  GROUP $group The ID of the group.
 * @param  ID_TEXT $property The identifier of the property.
 * @param  boolean $hide_hidden Whether to hide the name if it is a hidden group.
 * @return mixed The property value.
 */
function cns_get_group_property($group, $property, $hide_hidden = true)
{
    cns_ensure_groups_cached(array($group));
    global $USER_GROUPS_CACHED;

    if ($hide_hidden) {
        $members_groups = $GLOBALS['CNS_DRIVER']->get_members_groups(get_member());

        if (
            ($property == 'name') &&
            ($USER_GROUPS_CACHED[$group]['g_hidden'] == 1) &&
            (!has_privilege(get_member(), 'see_hidden_groups')) &&
            (!in_array($group, $members_groups))
        ) {
            return do_lang('UNKNOWN');
        }
    }

    return $USER_GROUPS_CACHED[$group]['g_' . $property];
}

/**
 * Get the best value of all values of a property for a member (due to members being in multiple usergroups).
 *
 * @param  MEMBER $member_id The ID of the member.
 * @param  ID_TEXT $property The identifier of the property.
 * @return mixed The property value.
 */
function cns_get_member_best_group_property($member_id, $property)
{
    return cns_get_best_group_property($GLOBALS['CNS_DRIVER']->get_members_groups($member_id, false, true), $property);
}

/**
 * Get the best value of all values of a property for a list of usergroups.
 *
 * @param  array $groups The list of usergroups.
 * @param  ID_TEXT $property The identifier of the property.
 * @return mixed The best property value ('best' is dependant on the property we are looking at).
 */
function cns_get_best_group_property($groups, $property)
{
    $big_is_better = array('gift_points_per_day', 'gift_points_base', 'enquire_on_new_ips', 'is_super_admin', 'is_super_moderator', 'max_daily_upload_mb', 'max_attachments_per_post', 'max_avatar_width', 'max_avatar_height', 'max_post_length_comcode', 'max_sig_length_comcode');
    //$small_and_perfectly_formed = array('flood_control_submit_secs', 'flood_control_access_secs'); Not needed by elimination, but nice to have here as a note

    $go_super_size = in_array($property, $big_is_better);

    global $USER_GROUPS_CACHED;
    cns_ensure_groups_cached($groups);
    $best_value_so_far = 0; // Initialise type to integer
    $best_value_so_far = null;
    foreach ($groups as $group) {
        $this_value = $USER_GROUPS_CACHED[$group]['g_' . $property];
        if ((is_null($best_value_so_far)) ||
            (($best_value_so_far < $this_value) && ($go_super_size)) ||
            (($best_value_so_far > $this_value) && (!$go_super_size))
        ) {
            $best_value_so_far = $this_value;
        }
    }
    return $best_value_so_far;
}

/**
 * Get a list of the usergroups a member is in (keys say the usergroups, values are irrelevant).
 *
 * @param  ?MEMBER $member_id The member to find the usergroups of (null: current member).
 * @param  boolean $skip_secret Whether to skip looking at secret usergroups.
 * @param  boolean $handle_probation Whether to take probation into account
 * @param  boolean $include_implicit Whether to include implicit groups
 * @return array Reverse list (e.g. array(1=>true,2=>true,3=>true) for someone in (1,2,3)).
 */
function cns_get_members_groups($member_id = null, $skip_secret = false, $handle_probation = true, $include_implicit = true)
{
    if (is_guest($member_id)) {
        $ret = array();
        $ret[db_get_first_id()] = true;
        return $ret;
    }

    if (is_null($member_id)) {
        $member_id = get_member();
    }

    if (($handle_probation) && ((!$GLOBALS['IS_VIA_BACKDOOR']) || ($member_id != get_member()))) {
        $opt = $GLOBALS['FORUM_DRIVER']->get_member_row_field($member_id, 'm_on_probation_until');
        if ((!is_null($opt)) && ($opt > time())) {
            global $PROBATION_GROUP_CACHE;
            if (is_null($PROBATION_GROUP_CACHE)) {
                $probation_group = get_option('probation_usergroup');
                $PROBATION_GROUP_CACHE = $GLOBALS['FORUM_DB']->query_select_value_if_there('f_groups', 'id', array($GLOBALS['FORUM_DB']->translate_field_ref('g_name') => $probation_group));
                if (is_null($PROBATION_GROUP_CACHE)) {
                    $PROBATION_GROUP_CACHE = false;
                }
            }
            if ($PROBATION_GROUP_CACHE !== false) {
                if ($member_id == get_member() && running_script('index')) {
                    static $given_message = false;
                    if (!$given_message) {
                        require_lang('cns');
                        require_code('site');
                        attach_message(do_lang_tempcode('IN_PROBATION', escape_html(get_timezoned_date($opt))), 'notice');
                        $given_message = true;
                    }
                }

                return array($PROBATION_GROUP_CACHE => 1);
            }
        }
    }

    $skip_secret = (
        ($skip_secret) &&
        ((/*For installer*/!function_exists('get_member')) || ($member_id != get_member())) &&
        ((!function_exists('has_privilege')) || (!has_privilege(get_member(), 'see_hidden_groups')))
    );

    global $GROUP_MEMBERS_CACHE;
    if (isset($GROUP_MEMBERS_CACHE[$member_id][$skip_secret][$handle_probation])) {
        return $GROUP_MEMBERS_CACHE[$member_id][$skip_secret][$handle_probation];
    }

    $groups = array();

    // Now implicit usergroup hooks
    if ($include_implicit) {
        $hooks = find_all_hooks('systems', 'cns_implicit_usergroups');
        foreach (array_keys($hooks) as $hook) {
            require_code('hooks/systems/cns_implicit_usergroups/' . $hook);
            $ob = object_factory('Hook_implicit_usergroups_' . $hook);
            $group_ids = $ob->get_bound_group_ids();
            foreach ($group_ids as $group_id) {
                if ($ob->is_member_within($member_id, $group_id)) {
                    $groups[$group_id] = true;
                }
            }
        }
    }

    require_code('cns_members');
    if ((!function_exists('cns_is_ldap_member')/*can happen if said in safe mode and detecting safe mode when choosing whether to avoid a custom file via admin permission which requires this function to run*/) || (!cns_is_ldap_member($member_id))) {
        if (!isset($GLOBALS['CNS_DRIVER'])) { // We didn't init fully (MICRO_BOOTUP), but now we dug a hole - get out of it
            if (method_exists($GLOBALS['FORUM_DRIVER'], 'forum_layer_initialise')) {
                $GLOBALS['FORUM_DRIVER']->forum_layer_initialise();
            }
        }
        $primary_group = $GLOBALS['CNS_DRIVER']->get_member_row_field($member_id, 'm_primary_group');
        if (is_null($primary_group)) {
            $primary_group = db_get_first_id();
        }
        $groups[$primary_group] = true;
        foreach (array_keys($groups) as $group_id) {
            $groups[$group_id] = true;
        }

        $_groups = $GLOBALS['FORUM_DB']->query_select('f_group_members m LEFT JOIN ' . $GLOBALS['FORUM_DB']->get_table_prefix() . 'f_groups g ON g.id=m.gm_group_id', array('gm_group_id', 'g_hidden'), array('gm_member_id' => $member_id, 'gm_validated' => 1), 'ORDER BY g.g_order');
        foreach ($_groups as $group) {
            $groups[$group['gm_group_id']] = true;
        }

        $GROUP_MEMBERS_CACHE[$member_id][false][$handle_probation] = $groups;
        $groups2 = $groups;
        foreach ($_groups as $group) { // For each secondary group
            if ($group['g_hidden'] == 1) {
                unset($groups2[$group['gm_group_id']]);
            }
        }
        $GROUP_MEMBERS_CACHE[$member_id][true][$handle_probation] = $groups2;
        if ($skip_secret) {
            $groups = $groups2;
        }
    } else {
        $groups = cns_get_members_groups_ldap($member_id);
        $GROUP_MEMBERS_CACHE[$member_id][false][$handle_probation] = $groups;
        $GROUP_MEMBERS_CACHE[$member_id][true][$handle_probation] = $groups;

        // Mirror to f_group_members table, so direct queries will also get it (we need to do listings of group members, for instance)
        $GLOBALS['FORUM_DB']->query_delete('f_group_members', array('gm_member_id' => $member_id));
        foreach (array_keys($groups) as $group_id) {
            $GLOBALS['FORUM_DB']->query_delete('f_group_members', array('gm_member_id' => $member_id, 'gm_group_id' => $group_id), '', 1);
            $GLOBALS['FORUM_DB']->query_insert('f_group_members', array(
                'gm_group_id' => $group_id,
                'gm_member_id' => $member_id,
                'gm_validated' => 1
            ));
        }
    }

    return $groups;
}

/**
 * Get the ID for a usergroup if we only know the title. Warning: Only use this with custom code, never core code! It assumes a single language and that usergroups aren't renamed.
 *
 * @param  SHORT_TEXT $title The title.
 * @return ?AUTO_LINK The ID (null: could not find).
 */
function find_usergroup_id($title)
{
    $usergroups = $GLOBALS['FORUM_DRIVER']->get_usergroup_list();
    foreach ($usergroups as $id => $usergroup) {
        if ($usergroup == $title) {
            return $id;
        }
    }
    return null;
}
