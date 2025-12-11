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
 * Get rows of tracker issues from Mantis.
 * TODO: implement for native tracker.
 *
 * @param  array $ids Array of tracker IDs to get
 * @param  ?ID_TEXT $version Limit to issues fixed in this version (null: do not limit)
 * @param  ?ID_TEXT $previous_version Limit to issues reported in the given version (null: do not limit)
 * @return array Array of issues found
 */
function get_tracker_issues(array $ids, ?string $version = null, ?string $previous_version = null) : array
{
    if ((empty($ids)) && ($version === null) && ($previous_version === null)) {
        return [];
    }

    $sql = 'SELECT id,summary,view_state,(SELECT username FROM mantis_user_table u WHERE u.id=m.reporter_id) AS reporter,(SELECT username FROM mantis_user_table u WHERE u.id=m.handler_id) AS handler,(SELECT name FROM mantis_category_table c WHERE c.id=m.category_id) AS category FROM mantis_bug_table m WHERE ';

    if (empty($ids)) {
        $sql .= '1=0';
    } else {
        $_ids = @implode(',', $ids);
        $where_ids = 'id IN (' . $_ids . ')';
        $sql .= $where_ids;
    }

    if ($version !== null) {
        $where_version = 'status=80 AND ' . db_string_equal_to('fixed_in_version', $version);
        $sql .= ' OR ' . $where_version;
    }

    if ($previous_version !== null) {
        $where_version = 'status=80 AND ' . db_string_equal_to('version', $previous_version) . ' AND ' . db_string_equal_to('fixed_in_version', '') . ' AND severity<>10';
        $sql .= ' OR ' . $where_version;
    }

    $issue_titles = [];
    $issues = $GLOBALS['SITE_DB']->query($sql);
    foreach ($issues as $issue) {
        $summary = $issue['summary'];
        $summary .= ' [' . $issue['category'] . ']';
        if ($issue['view_state'] != 10) {
            $summary .= ' [*private issue*]';
        }
        if (!array_key_exists($issue['category'], $issue_titles)) {
            $issue_titles[$issue['category']] = [];
        }
        $reporter = $issue['reporter'];
        $handler = $issue['handler'];
        $issue_titles[$issue['category']][$issue['id']] = [$summary, $reporter, $handler];
    }
    ksort($issue_titles);

    $_issue_titles = [];
    foreach ($issue_titles as $category_id => $issues) {
        foreach ($issues as $issue_id => $data) {
            $_issue_titles['_' . strval($issue_id)] = $data;
        }
    }

    return $_issue_titles;
}

/**
 * Create a new tracker issue programmatically.
 * This should only be used for issue importing and for creating issues through endpoints.
 *
 * @param  ID_TEXT $version The version in which this issue occurs (blank: not applicable)
 * @param  SHORT_TEXT $tracker_title The title for this issue
 * @param  ID_TEXT $tracker_type The type (severity) of issue
 * @param  LONG_TEXT $tracker_description The description of the issue
 * @param  LONG_TEXT $tracker_additional Additional information
 * @param  ID_TEXT $tracker_addon The relevant addon name (blank: not applicable)
 * @param  AUTO_LINK $tracker_category The catalogue category in which to place this issue
 * @param  ?MEMBER $handler_id The member handling this issue (null: none)
 * @param  LONG_TEXT $steps_to_reproduce Steps to reproduce this issue, delimited by a new line
 * @param  ID_TEXT $status Issue status identifier
 * @param  ?TIME $add_time The time this issue was added (null: now)
 * @param  ?MEMBER $submitter The member who submitted this issue (null: current member)
 * @param  ?integer $identifier The tracker issue ID (null: assign one automatically)
 * @return array Double: Catalogue entry ID, tracker issue ID
 */
function create_tracker_issue(string $version, string $tracker_title, string $tracker_type, string $tracker_description, string $tracker_additional, string $tracker_addon, int $tracker_category, ?int $handler_id = null, string $steps_to_reproduce = '', string $status = 'open', ?int $add_time = null, ?int $submitter = null, ?int $identifier = null) : array
{
    if (!addon_installed('cms_homesite_tracker') || !addon_installed('catalogues')) {
        warn_exit(do_lang_tempcode('INTERNAL_ERROR', escape_html('d07ad2b1177b59b19b7f554da41284cc')));
    }

    require_code('catalogues2');
    require_code('content2');
    require_code('fields');

    require_lang('tracker');
    require_lang('addons');

    // Map field names to catalogue field IDs
    $fields = $GLOBALS['SITE_DB']->query_select('catalogue_fields', ['*'], ['c_name' => 'tracker']);
    if (count($fields) == 0) {
        warn_exit(do_lang_tempcode('INTERNAL_ERROR', escape_html('24b19fdc9688512abbec179b1bc94f04')));
    }
    $field_map = [];
    foreach ($fields as $field) {
        $field_map[get_translated_text($field['cf_name'])] = $field;
    }

    // These fields could be null passed in, but they cannot be null when creating the catalogue entry
    if ($handler_id === null) {
        $field = $field_map[do_lang('HANDLER')];
        $object = get_fields_hook($field['cf_type']);
        $handler_id = intval($object->inputted_to_field_value(false, $field, 'uploads/catalogues', null));
    }
    if ($identifier === null) {
        $field = $field_map[do_lang('IDENTIFIER')];
        $object = get_fields_hook($field['cf_type']);
        $identifier = intval($object->inputted_to_field_value(false, $field, 'uploads/catalogues', null));
    }

    // Build our catalogue entry map
    $map = [
        $field_map[do_lang('VERSION')]['id'] => $version,
        $field_map[do_lang('TITLE')]['id'] => $tracker_title,
        $field_map[do_lang('ISSUE_TYPE')]['id'] => $tracker_type,
        $field_map[do_lang('DESCRIPTION')]['id'] => $tracker_description,
        $field_map[do_lang('ADDITIONAL_INFORMATION')]['id'] => $tracker_additional,
        $field_map[do_lang('ADDON')]['id'] => $tracker_addon,
        $field_map[do_lang('HANDLER')]['id'] => strval($handler_id),
        $field_map[do_lang('STEPS_TO_REPRODUCE')]['id'] => $steps_to_reproduce,
        $field_map[do_lang('STATUS')]['id'] => $status,
        $field_map[do_lang('IDENTIFIER')]['id'] => strval($identifier),
    ];

    // We only want to use title in SEO because everything else could contain garbage text (example code) or sensitive information
    list($imp, $description) = _seo_meta_find_data([$tracker_title], $tracker_title);

    // Create the issue (which also triggers form handler hooks to do additional maintenance)
    $entry_id = actual_add_catalogue_entry(
        $tracker_category,
        1,
        do_lang('TRACKER_ISSUE_AUTOMATIC'),
        1,
        1,
        0,
        $map,
        $add_time,
        $submitter,
        null,
        0,
        null,
        $imp,
        $description
    );

    return [$entry_id, $identifier];
}

/**
 * Update details on a tracker issue (and set the handler to the current member).
 *
 * @param  AUTO_LINK $tracker_id The tracker issue we are editing
 * @param  ?ID_TEXT $version The issue reported version (null: do not change)
 * @param  ?ID_TEXT $tracker_type The type (severity) identifier (null: do not change)
 * @param  ?ID_TEXT $tracker_addon The addon (null: do not change)
 * @param  ?AUTO_LINK $tracker_category The category (null: do not change)
 */
function update_tracker_issue(int $tracker_id, ?string $version = null, ?string $tracker_type = null, ?string $tracker_addon = null, ?int $tracker_category = null)
{
    $out = new Tempcode();
    if (!addon_installed__messaged('cms_homesite_tracker', $out)) {
        warn_exit($out);
    }
    if (!addon_installed__messaged('cms_homesite', $out)) {
        warn_exit($out);
    }
    if (!addon_installed__messaged('catalogues', $out)) {
        warn_exit($out);
    }

    require_code('catalogues');
    require_code('catalogues2');
    require_lang('tracker');

    // Get catalogue field IDs
    $identifier_field = $GLOBALS['SITE_DB']->query_select_value('catalogue_fields', 'id', ['c_name' => 'tracker', $GLOBALS['SITE_DB']->translate_field_ref('cf_name') => do_lang('IDENTIFIER')]);
    $version_field = $GLOBALS['SITE_DB']->query_select_value('catalogue_fields', 'id', ['c_name' => 'tracker', $GLOBALS['SITE_DB']->translate_field_ref('cf_name') => do_lang('VERSION')]); // short
    $type_field = $GLOBALS['SITE_DB']->query_select_value('catalogue_fields', 'id', ['c_name' => 'tracker', $GLOBALS['SITE_DB']->translate_field_ref('cf_name') => do_lang('ISSUE_TYPE')]); // short
    $addon_field = $GLOBALS['SITE_DB']->query_select_value('catalogue_fields', 'id', ['c_name' => 'tracker', $GLOBALS['SITE_DB']->translate_field_ref('cf_name') => do_lang('ADDON')]); // short

    // Get the catalogue entry ID
    $entry_id = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_efv_integer', 'cv_value', ['cf_id' => $identifier_field, 'ce_id' => $tracker_id]);
    if ($entry_id === null) {
        return false;
    }

    $_entry_row = $GLOBALS['SITE_DB']->query_select('catalogue_entries', ['*'], ['id' => $entry_id], '', 1);
    if (!array_key_exists(0, $_entry_row)) {
        return false;
    }
    $entry_row = $_entry_row[0];

    // Get its field values
    $_current_map = get_catalogue_entry_field_values('tracker', $entry_id);
    $current_map = [];
    foreach ($_current_map as $map_item) {
        $current_map[$map_item['id']] = $map_item['effective_value_pure'];
    }

    // Apply edits
    if ($version !== null) {
        $current_map[$version_field] = $version;
    }
    if ($tracker_type !== null) {
        $current_map[$type_field] = $tracker_type;
    }
    if ($tracker_addon !== null) {
        $current_map[$addon_field] = $tracker_addon;
    }
    if ($tracker_category === null) {
        $tracker_category = $entry_row['cc_id'];
    }

    actual_edit_catalogue_entry($entry_id, $tracker_category, 1, '', 1, 1, 1, $current_map);

    return true;
}

/**
 * Add a hotfix file to a tracker issue.
 *
 * @param  AUTO_LINK $tracker_id The tracker ID on which to upload a file
 * @param  ID_TEXT $upload The name of the $_FILES uploaded
 * @return boolean Whether it was successful
 */
function add_hotfix_to_tracker_issue(int $tracker_id, string $upload) : bool
{
    $out = new Tempcode();
    if (!addon_installed__messaged('cms_homesite_tracker', $out)) {
        warn_exit($out);
    }
    if (!addon_installed__messaged('cms_homesite', $out)) {
        warn_exit($out);
    }
    if (!addon_installed__messaged('catalogues', $out)) {
        warn_exit($out);
    }

    require_code('cms_homesite');
    require_code('catalogues');
    require_code('catalogues2');
    require_lang('tracker');

    // Get catalogue field IDs
    $identifier_field = $GLOBALS['SITE_DB']->query_select_value('catalogue_fields', 'id', ['c_name' => 'tracker', $GLOBALS['SITE_DB']->translate_field_ref('cf_name') => do_lang('IDENTIFIER')]);
    $hotfix_field = $GLOBALS['SITE_DB']->query_select_value('catalogue_fields', 'id', ['c_name' => 'tracker', $GLOBALS['SITE_DB']->translate_field_ref('cf_name') => do_lang('HOTFIXES')]);

    // Get the catalogue entry
    $entry_id = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_efv_integer', 'cv_value', ['cf_id' => $identifier_field, 'ce_id' => $tracker_id]);
    if ($entry_id === null) {
        return false;
    }
    $_entry_row = $GLOBALS['SITE_DB']->query_select('catalogue_entries', ['*'], ['id' => $entry_id], '', 1);
    if (!array_key_exists(0, $_entry_row)) {
        return false;
    }
    $entry_row = $_entry_row[0];

    // Get its field values
    $_current_map = get_catalogue_entry_field_values('tracker', $entry_id);
    $current_map = [];
    foreach ($_current_map as $map_item) {
        $current_map[$map_item['id']] = $map_item['effective_value_pure'];
    }

    // Process upload
    require_code('uploads');
    list($url, $thumb_url, $original_url, $original_thumb) = get_url('', $upload, 'uploads/catalogues', OBFUSCATE_NEVER, CMS_UPLOAD_ANYTHING, false, '', '', true, false, true);

    // Add hotfix to the field
    if (trim($current_map[$hotfix_field]) != '') {
        $current_map[$hotfix_field] .= "\n";
    }
    $current_map[$hotfix_field] .= $url . '::' . $original_url;

    actual_edit_catalogue_entry($entry_id, $entry_row['cc_id'], 1, '', 1, 1, 1, $current_map);

    return true;
}

/**
 * Create a comment on a tracker issue.
 *
 * @param  integer $tracker_id The tracker ID on which to post the comment
 * @param  LONG_TEXT $tracker_comment_message The message to post
 * @param  boolean $is_private Whether this comment should only be visible to staff
 * @return boolean Whether we created the comment
 */
function create_tracker_comment(int $tracker_id, string $tracker_comment_message, bool $is_private = false) : bool
{
    $out = new Tempcode();
    if (!addon_installed__messaged('cms_homesite_tracker', $out)) {
        warn_exit($out);
    }
    if (!addon_installed__messaged('cms_homesite', $out)) {
        warn_exit($out);
    }
    if (!addon_installed__messaged('catalogues', $out)) {
        warn_exit($out);
    }

    require_code('cms_homesite');
    require_code('feedback');
    require_lang('tracker');

    $identifier_field = $GLOBALS['SITE_DB']->query_select_value('catalogue_fields', 'id', ['c_name' => 'tracker', $GLOBALS['SITE_DB']->translate_field_ref('cf_name') => do_lang('IDENTIFIER')]);
    $title_field = $GLOBALS['SITE_DB']->query_select_value('catalogue_fields', 'id', ['c_name' => 'tracker', $GLOBALS['SITE_DB']->translate_field_ref('cf_name') => do_lang('TITLE')]);

    // Get the catalogue entry and its title
    $entry_id = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_efv_integer', 'cv_value', ['cf_id' => $identifier_field, 'ce_id' => $tracker_id]);
    if ($entry_id === null) {
        return false;
    }
    $title = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_efv_short', 'cv_value', ['cf_id' => $title_field, 'ce_id' => $entry_id]);
    if ($title === null) {
        $title = do_lang('NA');
    }

    actualise_post_comment(
        true,
        'catalogue_entry',
        strval($entry_id),
        build_url(['page' => 'catalogues', 'type' => 'entry', 'id' => $entry_id], get_module_zone('catalogues')),
        '#' . strval($tracker_id) . ' - ' . $title,
        null,
        false,
        1,
        true,
        false,
        false,
        '',
        $tracker_comment_message,
        time(),
        get_member(),
        $is_private,
    );

    return true;
}

/**
 * Mark a tracker issue as resolved.
 *
 * @param  AUTO_LINK $tracker_id The tracker issue to resolve
 * @param  ?MEMBER $handler The member who resolved the issue (null: current member)
 * @return boolean Whether the action was successful
 */
function resolve_tracker_issue(int $tracker_id, ?int $handler = null) : bool
{
    $out = new Tempcode();
    if (!addon_installed__messaged('cms_homesite_tracker', $out)) {
        warn_exit($out);
    }
    if (!addon_installed__messaged('cms_homesite', $out)) {
        warn_exit($out);
    }
    if (!addon_installed__messaged('catalogues', $out)) {
        warn_exit($out);
    }

    require_code('catalogues');
    require_code('catalogues2');
    require_lang('tracker');

    if ($handler === null) {
        $handler = get_member();
    }

    // Get catalogue field IDs
    $identifier_field = $GLOBALS['SITE_DB']->query_select_value('catalogue_fields', 'id', ['c_name' => 'tracker', $GLOBALS['SITE_DB']->translate_field_ref('cf_name') => do_lang('IDENTIFIER')]);
    $status_field = $GLOBALS['SITE_DB']->query_select_value('catalogue_fields', 'id', ['c_name' => 'tracker', $GLOBALS['SITE_DB']->translate_field_ref('cf_name') => do_lang('STATUS')]);
    $handler_field = $GLOBALS['SITE_DB']->query_select_value('catalogue_fields', 'id', ['c_name' => 'tracker', $GLOBALS['SITE_DB']->translate_field_ref('cf_name') => do_lang('HANDLER')]);


    // Get the catalogue entry ID
    $entry_id = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_efv_integer', 'cv_value', ['cf_id' => $identifier_field, 'ce_id' => $tracker_id]);
    if ($entry_id === null) {
        return false;
    }

    $_entry_row = $GLOBALS['SITE_DB']->query_select('catalogue_entries', ['*'], ['id' => $entry_id], '', 1);
    if (!array_key_exists(0, $_entry_row)) {
        return false;
    }
    $entry_row = $_entry_row[0];

    // Get its field values
    $_current_map = get_catalogue_entry_field_values('tracker', $entry_id);
    $current_map = [];
    foreach ($_current_map as $map_item) {
        $current_map[$map_item['id']] = $map_item['effective_value_pure'];
    }

    // Change the status to completed (but only if it is open)
    if (($current_map[$status_field] != 'open') && ($current_map[$status_field] != 'information_needed')) {
        return false;
    }
    $current_map[$status_field] = 'completed';

    // Change the handler to the current member
    $current_map[$handler_field] = $handler;

    actual_edit_catalogue_entry($entry_id, $entry_row['cc_id'], 1, '', 1, 1, 1, $current_map);

    return true;
}

/**
 * Mark a tracker issue as resolved.
 *
 * @param  AUTO_LINK $tracker_id The tracker issue on which to add commit URL
 * @param  URLPATH $commit_url URL to the commit
 * @return boolean Whether the action was successful
 */
function add_commit_to_tracker_issue(int $tracker_id, string $commit_url) : bool
{
    $out = new Tempcode();
    if (!addon_installed__messaged('cms_homesite_tracker', $out)) {
        warn_exit($out);
    }
    if (!addon_installed__messaged('cms_homesite', $out)) {
        warn_exit($out);
    }
    if (!addon_installed__messaged('catalogues', $out)) {
        warn_exit($out);
    }

    require_code('catalogues');
    require_code('catalogues2');
    require_lang('tracker');

    // Get catalogue field IDs
    $identifier_field = $GLOBALS['SITE_DB']->query_select_value('catalogue_fields', 'id', ['c_name' => 'tracker', $GLOBALS['SITE_DB']->translate_field_ref('cf_name') => do_lang('IDENTIFIER')]);
    $commits_field = $GLOBALS['SITE_DB']->query_select_value('catalogue_fields', 'id', ['c_name' => 'tracker', $GLOBALS['SITE_DB']->translate_field_ref('cf_name') => do_lang('COMMITS')]);

    // Get the catalogue entry
    $entry_id = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_efv_integer', 'cv_value', ['cf_id' => $identifier_field, 'ce_id' => $tracker_id]);
    if ($entry_id === null) {
        return false;
    }
    $_entry_row = $GLOBALS['SITE_DB']->query_select('catalogue_entries', ['*'], ['id' => $entry_id], '', 1);
    if (!array_key_exists(0, $_entry_row)) {
        return false;
    }
    $entry_row = $_entry_row[0];

    // Get its field values
    $_current_map = get_catalogue_entry_field_values('tracker', $entry_id);
    $current_map = [];
    foreach ($_current_map as $map_item) {
        $current_map[$map_item['id']] = $map_item['effective_value_pure'];
    }

    // Add the new commit link to the issue
    if (trim($current_map[$commits_field]) != '') {
        $current_map[$commits_field] .= "\n";
    }
    $current_map[$commits_field] .= $commit_url;

    actual_edit_catalogue_entry($entry_id, $entry_row['cc_id'], 1, '', 1, 1, 1, $current_map);

    return true;
}

/**
 * Award points for a resolved tracker issue.
 * This will undo previous transactions for the same issue if they exist (treated as an edit).
 *
 * @param  integer $tracker_id The issue identifier
 * @param  AUTO_LINK $entry_id The catalogue entry ID
 * @param  MEMBER $reporter The member who reported the issue
 * @param  ?MEMBER $handler The member who resolved the issue (null: not defined)
 * @return boolean Whether the operation was successful
 */
function award_tracker_points(int $tracker_id, int $entry_id, int $reporter, ?int $handler = null) : bool
{
    if (!addon_installed('points')) {
        warn_exit(do_lang_tempcode('MISSING_ADDON', escape_html('points')));
    }

    require_code('points2');

    $ret = true;
    $points = 25; // FUDGE

    // Did we already award points?
    $test_a = $GLOBALS['SITE_DB']->query_select_value_if_there('points_ledger', 'id', ['receiving_member' => $reporter, 'status' => LEDGER_STATUS_NORMAL, 't_type' => 'catalogue_entry', 't_type_id' => strval($entry_id)]);
    $test_b = ($handler === null) ? null : $GLOBALS['SITE_DB']->query_select_value_if_there('points_ledger', 'id', ['receiving_member' => $handler, 'status' => LEDGER_STATUS_NORMAL, 't_type' => 'catalogue_entry', 't_type_id' => strval($entry_id)]);

    // Did both the reporter and handler already receive points?
    if (($test_a !== null) && ($test_b !== null)) {
        return true;
    }

    // Reset so we do not dog-pile points
    points_transactions_reverse_all(true, null, null, 'catalogue_entry', '', strval($entry_id));

    if (($handler !== null) && !is_guest($handler)) {
        $id = points_credit_member($handler, 'Programming god: Resolved tracker issue #' . strval($tracker_id), $points, 0, true, 0, 'catalogue_entry', 'tracker_resolve', strval($entry_id));
        if ($id === null) {
            $ret = false;
        }
    }

    if (!is_guest($reporter) && ($reporter != $handler)) {
        $id = points_credit_member($reporter, 'Tracker master: Reported resolved tracker issue #' . strval($tracker_id), $points, 0, true, 0, 'catalogue_entry', 'tracker_resolved', strval($entry_id));
        if ($id === null) {
            $ret = false;
        }
    }

    return $ret;
}

/**
 * Undo points awarded on a tracker issue.
 * This does not undo sponsorships.
 *
 * @param  AUTO_LINK $entry_id The catalogue entry
 */
function reverse_tracker_points(int $entry_id)
{
    if (!addon_installed('points')) {
        warn_exit(do_lang_tempcode('MISSING_ADDON', escape_html('points')));
    }

    require_code('points2');

    points_transactions_reverse_all(true, null, null, 'catalogue_entry', '', strval($entry_id));
}
