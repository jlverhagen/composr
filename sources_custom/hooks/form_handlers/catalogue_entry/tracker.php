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
class Hook_form_handlers_catalogue_entry_tracker
{
    /**
     * Form hook for adding a catalogue entry.
     *
     * @param  AUTO_LINK $id The ID of the catalogue entry that was just added
     * @param  AUTO_LINK $category_id The ID of the entry's category
     * @param  ID_TEXT $catalogue_name The catalogue name
     * @param  BINARY $validated Whether this entry was validated on adding
     * @param  LONG_TEXT $notes Staff notes
     * @param  BINARY $allow_rating Whether ratings are allowed
     * @param  integer $allow_comments Whether comments are allowed (0=no, 1=yes, 2=review style)
     * @param  BINARY $allow_trackbacks Whether trackbacks are allowed
     * @param  array $map A map of field IDs, to values, that defines the entries settings
     * @param  TIME $time The time the entry was added
     * @param  MEMBER $submitter The member who submitted the entry
     * @param  ?TIME $edit_date The time the entry was edited (null: not specified)
     * @param  integer $views The number of views on the entry
     * @param  ?LONG_TEXT $meta_keywords SEO meta keywords (null: none)
     * @param  ?LONG_TEXT $meta_description SEO meta description (null: none)
     */
    public function add(int $id, int $category_id, string $catalogue_name, int $validated, string $notes, int $allow_rating, int $allow_comments, int $allow_trackbacks, array $map, int $time, int $submitter, ?int $edit_date, int $views, ?string $meta_keywords, ?string $meta_description)
    {
        if (!addon_installed('cms_homesite_tracker') || !addon_installed('catalogues')) {
            return;
        }

        if ($catalogue_name != 'tracker') {
            return;
        }

        require_lang('tracker');

        $id_field = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_fields', 'id', ['c_name' => $catalogue_name, 'cf_name' => do_lang('IDENTIFIER'), 'cf_defines_order' => '1', 'cf_type' => 'tracker_id']);
        if (($id_field === null) || (!isset($map[$id_field]))) {
            return;
        }
        $tracker_id = intval($map[$id_field]);

        // We want tracker issue URL monikers to be based on the issue identifier and not to include the category
        require_code('urls2');
        suggest_new_idmoniker_for('catalogues', 'entry', strval($id), '', '', false, 'tracker-' . strval($tracker_id));

        exit('WORKS');

        // Even though the UI does not allow setting status immediately, we might have immediately set it to Completed in the API, so we need to award points if so.
        if (addon_installed('points') && (get_mass_import_mode() === false)) {
            $status_field = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_fields', 'id', ['c_name' => $catalogue_name, 'cf_name' => do_lang('STATUS')]);
            $handler_field = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_fields', 'id', ['c_name' => $catalogue_name, 'cf_name' => do_lang('HANDLER')]);
            if ($status_field !== null) {
                $handler = (isset($map[$handler_field]) && !empty($map[$handler_field])) ? intval($map[$handler_field]) : null;
                if (isset($map[$status_field]) && !empty($map[$status_field]) && ($map[$status_field] == 'completed')) {
                    // Tracker points (we don't process sponsorships because an immediately-added issue would never have a sponsorship)
                    require_code('cms_homesite_tracker');
                    award_tracker_points($tracker_id, $id, $submitter, $handler);
                }
            }
        }
    }

    /**
     * Form hook for editing a catalogue entry.
     *
     * @param  AUTO_LINK $id The ID of the catalogue entry that was edited
     * @param  AUTO_LINK $category_id The ID of the entry's category
     * @param  ID_TEXT $catalogue_name The catalogue name
     * @param  BINARY $validated Whether this entry is validated
     * @param  LONG_TEXT $notes Staff notes
     * @param  BINARY $allow_rating Whether ratings are allowed
     * @param  integer $allow_comments Whether comments are allowed (0=no, 1=yes, 2=review style)
     * @param  BINARY $allow_trackbacks Whether trackbacks are allowed
     * @param  array $map A map of field IDs, to values, that defines the entries settings
     * @param  ?SHORT_TEXT $meta_keywords Meta keywords for this resource (null: do not edit keywords or description)
     * @param  ?LONG_TEXT $meta_description Meta description for this resource (null: do not edit keywords or description)
     * @param  ?TIME $edit_time Edit time (null: either means current time, or if $null_is_literal, means reset to to null)
     * @param  ?TIME $add_time Add time (null: do not change)
     * @param  ?integer $views Number of views (null: do not change)
     * @param  ?MEMBER $submitter Submitter (null: do not change)
     * @param  boolean $null_is_literal Whether null parameters mean literally use null in the database (if supported) instead of do not change
     */
    public function edit(int $id, int $category_id, string $catalogue_name, int $validated, string $notes, int $allow_rating, int $allow_comments, int $allow_trackbacks, array $map, ?string $meta_keywords, ?string $meta_description, ?int $edit_time, ?int $add_time, ?int $views, ?int $submitter, bool $null_is_literal)
    {
        if (!addon_installed('cms_homesite_tracker') || !addon_installed('catalogues')) {
            return;
        }
        if ($catalogue_name != 'tracker') {
            return;
        }

        $id_field = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_fields', 'id', ['c_name' => $catalogue_name, 'cf_name' => do_lang('IDENTIFIER'), 'cf_defines_order' => '1', 'cf_type' => 'tracker_id']);
        if ($id_field === null) {
            return;
        }

        // Handle points awarding (or revoking) and sponsorships
        if (addon_installed('points')) {
            require_code('cms_homesite_tracker');
            require_lang('tracker');

            $tracker_id = intval($map[$id_field]);

            // Determine if we need to assign or revoke completion points and sponsorships

            $is_getting_points = null; // (true: assign points, false: revoke points, null: do nothing)

            $_submitter = ($submitter !== null) ? $submitter : $GLOBALS['SITE_DB']->query_select_value('catalogue_entries', 'ce_submitter', ['id' => $id]);
            $status_field = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_fields', 'id', ['c_name' => $catalogue_name, 'cf_name' => do_lang('STATUS')]);
            $handler_field = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_fields', 'id', ['c_name' => $catalogue_name, 'cf_name' => do_lang('HANDLER')]);
            $handler = (isset($map[$handler_field]) && !empty($map[$handler_field])) ? intval($map[$handler_field]) : null;

            if (($status_field === null) || ($handler_field === null)) {
                $is_getting_points = null;
            } else {
                if (isset($map[$status_field]) && !empty($map[$status_field])) {
                    if ($map[$status_field] == 'completed') {
                        $is_getting_points = true;
                    } elseif (strpos($map[$status_field], 'closed') !== false) {
                        $is_getting_points = false;
                    } else {
                        $is_getting_points = null;
                    }
                } else {
                    $is_getting_points = null;
                }
            }
            if ($validated == 0) {
                $is_getting_points = false;
            }

            if ($is_getting_points === true) { // Awarding points
                // Tracker points
                award_tracker_points($tracker_id, $id, $_submitter, $handler);

                // Sponsorships
                if (($handler !== null) && (!is_guest($handler))) {
                    require_code('points_escrow');
                    complete_all_escrows_by_content($handler, 'catalogue_entry', strval($id));
                }
            }

            if ($is_getting_points === false) { // Revoking points
                reverse_tracker_points($id);
                cancel_all_escrows_by_content('catalogue_entry', strval($id), do_lang('TRACKER_CATALOGUE_STATUS_DEFAULT_' . $map[$status_field]));
            }
        }
    }

    /**
     * Form hook for deleting a catalogue entry.
     *
     * @param  AUTO_LINK $id The ID of the catalogue entry that was deleted
     * @param  AUTO_LINK $category_id The ID of the entry's category
     * @param  ID_TEXT $catalogue_name The catalogue name
     */
    public function delete(int $id, int $category_id, string $catalogue_name)
    {
        if (!addon_installed('cms_homesite_tracker') || !addon_installed('catalogues')) {
            return;
        }
        if ($catalogue_name != 'tracker') {
            return;
        }

        // NB: We cannot send an issue deleted notification; the entry is already gone. But it will be in the action log as a catalogue entry deleted.

        // The assumption is we treat issues like forum posts; they are only deleted if spam (therefore, revoke points). Otherwise, we keep them for archive purposes.
        if (addon_installed('points')) {
            require_code('cms_homesite_tracker');
            require_code('points_escrow');

            reverse_tracker_points($id);
            cancel_all_escrows_by_content('catalogue_entry', strval($id), do_lang('DELETED'));
        }
    }
}
