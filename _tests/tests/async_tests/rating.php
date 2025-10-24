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
 * @package    testing_platform
 */

/**
 * Composr test case class (unit testing).
 */
class rating_test_set extends cms_test_case
{
    protected $event_id;

    public function testRateCalendarEvent()
    {
        require_code('calendar2');
        require_code('feedback');
        $this->event_id = add_calendar_event(8, 'none', null, 0, 'test_event', '', 3, 2010, 1, 10, 'day_of_month', 10, 15, null, null, null, 'day_of_month', null, null, null, 1, null, 1, 1, 1, 1, '', null, 0, null, null, null);
        if ('test_event' == get_translated_text($GLOBALS['SITE_DB']->query_select_value('calendar_events', 'e_title', ['id' => $this->event_id]))) {
            $GLOBALS['SITE_DB']->query_insert('rating', ['rating_for_type' => 'events', 'rating_for_id' => $this->event_id, 'rating_member' => get_member(), 'rating_ip_address' => get_ip_address(), 'rating_time' => time(), 'rating' => 4]);
        }
        $data = $GLOBALS['SITE_DB']->query_select('rating', ['rating '], ['rating_for_id' => $this->event_id, 'rating_member' => get_member()]);
        $rating = $data[0]['rating'];
        $this->assertTrue(4 == $rating);
    }

    public function tearDown()
    {
        delete_calendar_event($this->event_id);
        $GLOBALS['SITE_DB']->query_delete('rating', ['rating_for_id' => $this->event_id, 'rating_member' => get_member()]);

        parent::tearDown();
    }
}
