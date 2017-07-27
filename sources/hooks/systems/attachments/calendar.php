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
 * @package    calendar
 */

/**
 * Hook class.
 */
class Hook_attachments_calendar
{
    /**
     * Run function for attachment hooks. They see if permission to an attachment of an ID relating to this content is present for the current member.
     *
     * @param  ID_TEXT $id The ID
     * @param  object $connection The database connection to check on
     * @return boolean Whether there is permission
     */
    public function run($id, $connection)
    {
        if (addon_installed('content_privacy')) {
            require_code('content_privacy');
            if (!has_privacy_access('event', $id)) {
                return false;
            }
        }

        $info = $connection->query_select('calendar_events', array('e_submitter', 'e_type'), array('id' => intval($id)), '', 1);
        if (!array_key_exists(0, $info)) {
            return false;
        }

        if (!has_category_access(get_member(), 'calendar', strval($info[0]['e_type']))) {
            return false;
        }

        if (addon_installed('content_privacy')) {
            require_code('content_privacy');
            if (!has_privacy_access('event', strval($id))) {
                return false;
            }
        }

        if ($info[0]['e_submitter'] == get_member()) {
            return true;
        }

        return false;
    }
}
