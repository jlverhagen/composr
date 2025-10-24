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
 * @package    activity_feed
 */

/**
 * Hook class.
 */
class Hook_rss_activity_feed
{
    /**
     * Check if the given member has access to view this feed.
     *
     * @param  MEMBER $member_id The member trying to access this feed
     * @return boolean Whether the member has access
     */
    public function has_access(int $member_id) : bool
    {
        if (!addon_installed('activity_feed')) {
            return false;
        }

        return true;
    }

    /**
     * Run function for RSS hooks.
     *
     * @param  string $_filters A list of categories we accept from
     * @param  TIME $cutoff Cutoff time, before which we do not show results from
     * @param  string $prefix Prefix that represents the template set we use
     * @set RSS_ ATOM_
     * @param  string $date_string The standard format of date to use for the syndication type represented in the prefix
     * @param  integer $max The maximum number of entries to return, ordering by date
     * @return ?array A pair: The main syndication section, and a title (null: error)
     */
    public function run(string $_filters, int $cutoff, string $prefix, string $date_string, int $max) : ?array
    {
        if (!$this->has_access(get_member())) {
            return null;
        }

        require_lang('activity_feed');
        require_code('activity_feed');

        list(, $where_clause) = get_activity_querying_sql(get_member(), ($_filters == '') ? 'all' : 'some_members', array_map('intval', explode(',', $_filters)));

        $rows = $GLOBALS['SITE_DB']->query('SELECT * FROM ' . get_table_prefix() . 'activities WHERE (' . $where_clause . ') AND a_time>' . strval($cutoff) . ' ORDER BY a_time DESC', $max, 0);

        $content = new Tempcode();
        foreach ($rows as $row) {
            $id = strval($row['id']);
            $author = $GLOBALS['FORUM_DRIVER']->get_username($row['a_member_id']);

            $news_date = date($date_string, $row['a_time']);
            $edit_date = '';

            list($_title,) = render_activity($row);
            $news_title = xmlentities($_title->evaluate());
            $summary = xmlentities('');
            $news = '';

            $category = '';
            $category_raw = '';

            $view_url = build_url(['page' => 'members', 'type' => 'view', 'id' => $row['a_member_id']], get_module_zone('members'), [], false, false, true);

            $if_comments = new Tempcode();

            $content->attach(do_template($prefix . 'ENTRY', ['VIEW_URL' => $view_url, 'SUMMARY' => $summary, 'EDIT_DATE' => $edit_date, 'IF_COMMENTS' => $if_comments, 'TITLE' => $news_title, 'CATEGORY_RAW' => $category_raw, 'CATEGORY' => $category, 'AUTHOR' => $author, 'ID' => $id, 'NEWS' => $news, 'DATE' => $news_date], null, false, null, '.xml', 'xml'));
        }

        return [$content, do_lang('ACTIVITY')];
    }
}
