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
        warn_exit(do_lang_tempcode('INTERNAL_ERROR', escape_html('TODO')));
    }

    require_code('catalogues2');
    require_code('content2');
    require_code('fields');

    require_lang('tracker');
    require_lang('addons');

    // Map field names to catalogue field IDs
    $fields = $GLOBALS['SITE_DB']->query_select('catalogue_fields', ['*'], ['c_name' => 'tracker']);
    if (count($fields) == 0) {
        warn_exit(do_lang_tempcode('INTERNAL_ERROR', escape_html('TODO')));
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
 * Update details on a Mantis tracker issue (and set the handler to the current member).
 *
 * @param  AUTO_LINK $tracker_id The tracker issue we are editing
 * @param  ?ID_TEXT $version The issue reported version (null: do not change)
 * @param  ?integer $tracker_severity The severity identifier (null: do not change)
 * @param  ?AUTO_LINK $tracker_category The category / addon (null: do not change)
 * @param  ?AUTO_LINK $tracker_project The issue project (null: do not change)
 */
function update_tracker_issue(int $tracker_id, ?string $version = null, ?int $tracker_severity = null, ?int $tracker_category = null, ?int $tracker_project = null)
{
    ensure_version_exists_in_tracker($version);

    $query = "
        UPDATE
        `mantis_bug_table`
        SET
    ";
    if ($tracker_project !== null) {
        $query .= "
            `project_id`='" . db_escape_string(strval($tracker_project)) . "',
        ";
    }
    if (true) {
        $query .= "
            `handler_id`='" . strval(get_member()) . "',
        ";
    }
    if ($tracker_severity !== null) {
        $query .= "
            `severity`='" . db_escape_string(strval($tracker_severity)) . "',
        ";
    }
    if ($version !== null) {
        $query .= "
            `version`='" . db_escape_string($version) . "',
        ";
    }
    if ($tracker_category !== null) {
        $query .= "
            `category_id`='" . db_escape_string(strval($tracker_category)) . "',
        ";
    }
    $query .= "
            `last_updated`='" . strval(time()) . "'
        WHERE
            id=" . strval($tracker_id);
    $GLOBALS['SITE_DB']->_query(trim($query), null, 0, false, false, null, '', false);
}

/**
 * Make sure the given version exists in Mantis.
 *
 * @param  ?ID_TEXT $version The version to check and create if it does not exist (null: do not check anything)
 */
function ensure_version_exists_in_tracker(?string $version)
{
    if ($version === null) {
        return;
    }

    if ($GLOBALS['SITE_DB']->query_value_if_there('SELECT version FROM mantis_project_version_table WHERE ' . db_string_equal_to('version', $version)) === null) {
        $query = "
            INSERT INTO
            `mantis_project_version_table`
            (
                `project_id`,
                `version`,
                `description`,
                `released`,
                `obsolete`,
                `date_order`
            )
            VALUES
            (
                    1,
                    '" . db_escape_string($version) . "',
                    '',
                    1,
                    0,
                    " . strval(time()) . "
            )
        ";
        $GLOBALS['SITE_DB']->_query($query, null, 0, true);
    }
}

/**
 * Upload a file to a tracker issue in Mantis.
 *
 * @param  AUTO_LINK $tracker_id The tracker ID on which to upload a file
 * @param  mixed $upload The file resource to upload
 * @return AUTO The file ID
 */
function upload_to_tracker_issue(int $tracker_id, $upload) : int
{
    $out = new Tempcode();
    if (!addon_installed__messaged('cms_homesite', $out)) {
        warn_exit($out);
    }

    require_code('cms_homesite');

    $disk_filename = md5(uniqid('', true));
    $save_path = get_custom_file_base() . '/tracker/uploads/' . $disk_filename;
    move_uploaded_file($upload['tmp_name'], $save_path);
    fix_permissions($save_path);
    sync_file($save_path);

    $query = "
        INSERT INTO
        `mantis_bug_file_table`
        (
          `bug_id`,
          `title`,
          `description`,
          `diskfile`,
          `filename`,
          `folder`,
          `filesize`,
          `file_type`,
          `content`,
          `date_added`,
          `user_id`
        )
        VALUES
        (
            '" . strval($tracker_id) . "',
            '',
            '',
            '" . $disk_filename . "',
            '" . db_escape_string($upload['name']) . "',
            '" . get_custom_file_base() . "/tracker/uploads/',
            '" . strval($upload['size']) . "',
            'application/octet-stream',
            '',
            '" . strval(time()) . "',
            '" . strval(LEAD_DEVELOPER_MEMBER_ID) . "'
        )
    ";

    return $GLOBALS['SITE_DB']->_query(trim($query), null, 0, false, true, null, '', false);
}

/**
 * Create a bug note on a Mantis tracker issue.
 *
 * @param  AUTO_LINK $tracker_id The tracker ID on which to post the note
 * @param  LONG_TEXT $tracker_comment_message The message to post
 * @return AUTO The bugnote ID
 */
function create_tracker_post(int $tracker_id, string $tracker_comment_message) : int
{
    $out = new Tempcode();
    if (!addon_installed__messaged('cms_homesite', $out)) {
        warn_exit($out);
    }

    require_code('cms_homesite');

    $query = "
        INSERT INTO
        `mantis_bugnote_text_table`
        (
          `note`
        )
        VALUES
        (
            '" . db_escape_string($tracker_comment_message) . "'
        )
    ";
    $text_id = $GLOBALS['SITE_DB']->_query(trim($query), null, 0, false, true, null, '', false);

    $monitors = $GLOBALS['SITE_DB']->query('SELECT user_id FROM mantis_bug_monitor_table WHERE bug_id=' . strval($tracker_id));
    foreach ($monitors as $m) {
        $to_name = $GLOBALS['FORUM_DRIVER']->get_username($m['user_id'], true, USERNAME_DEFAULT_NULL);
        if ($to_name !== null) {
            $to_email = $GLOBALS['FORUM_DRIVER']->get_member_email_address($m['user_id']);

            $join_time = $GLOBALS['FORUM_DRIVER']->get_member_row_field($m['user_id'], 'm_join_time');

            require_code('mail');
            dispatch_mail('Tracker issue updated', 'A tracker issue you are monitoring has been updated (' . get_base_url() . '/tracker/view.php?id=' . strval($tracker_id) . ').', '', [$to_email], $to_name, '', '', ['require_recipient_valid_since' => $join_time]);
        }
    }

    $query = "
        INSERT INTO
        `mantis_bugnote_table`
        (
          `bug_id`,
          `reporter_id`,
          `bugnote_text_id`,
          `view_state`,
          `note_type`,
          `note_attr`,
          `time_tracking`,
          `last_modified`,
          `date_submitted`
        )
        VALUES
        (
            '" . strval($tracker_id) . "',
            '" . strval(LEAD_DEVELOPER_MEMBER_ID) . "',
            '" . strval($text_id) . "',
            '10', /* Public */
            '0',
            '',
            '0',
            '" . strval(time()) . "',
            '" . strval(time()) . "'
        )
    ";
    return $GLOBALS['SITE_DB']->_query($query, null, 0, false, true, null, '', false);
}

/**
 * Mark a Mantis tracker issue as resolved, and award points where applicable.
 *
 * @param  AUTO_LINK $tracker_id The tracker issue to resolve
 * @param  ?MEMBER $handler The member who resolved the issue (null: current member)
 */
function resolve_tracker_issue(int $tracker_id, ?int $handler = null)
{
    if ($handler === null) {
        $handler = get_member();
    }

    $GLOBALS['SITE_DB']->query('UPDATE mantis_bug_table SET resolution=20, status=80, handler_id=' . strval($handler) . ' WHERE id=' . strval($tracker_id));

    if (addon_installed('points')) {
        if (addon_installed('cms_homesite')) {
            require_code('points_escrow__sponsorship');
            escrow_complete_all_sponsorships($tracker_id, $handler);
        }

        $reporter = $GLOBALS['SITE_DB']->query_value_if_there('SELECT reporter_id FROM mantis_bug_table WHERE id=' . strval($tracker_id));
        if ($reporter !== null) {
            award_tracker_points($tracker_id, $reporter, $handler);
        }
    }
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
