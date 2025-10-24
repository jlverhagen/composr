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
class calendar_event_types_test_set extends cms_test_case
{
    protected $eventtype_id;

    public function setUp()
    {
        parent::setUp();

        require_code('calendar2');
    }

    public function testCanAddEventType()
    {
        $this->eventtype_id = add_event_type('test_event_type', 'icons/calendar/testtype', '');
        $this->assertTrue('test_event_type' == get_translated_text($GLOBALS['SITE_DB']->query_select_value('calendar_types', 't_title', ['id' => $this->eventtype_id])));
    }

    public function testCanEditEventType()
    {
        edit_event_type($this->eventtype_id, 'test_event_type1', 'icons/calendar/testtype1', '');
        $this->assertTrue('test_event_type1' == get_translated_text($GLOBALS['SITE_DB']->query_select_value('calendar_types', 't_title', ['id' => $this->eventtype_id])));
    }

    public function testCanDeleteEventType()
    {
        // This is just to pick up on exceptions
        delete_event_type($this->eventtype_id);
    }

    public function tearDown()
    {
        parent::tearDown();
    }
}
