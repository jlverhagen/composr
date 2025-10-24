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
class quizzes_test_set extends cms_test_case
{
    protected $quiz_id;

    public function setUp()
    {
        parent::setUp();

        require_code('quiz2');

        $this->quiz_id = add_quiz('Quiz1', 15, 'Begin', 'End', '', 'somethng', 60, time(), null, 1, 0, 'TEST', 1, 'Questions', null, 0, null);

        $this->assertTrue('Quiz1' == get_translated_text($GLOBALS['SITE_DB']->query_select_value('quizzes', 'q_name', ['id' => $this->quiz_id])));
    }

    public function testEditQuiz()
    {
        edit_quiz($this->quiz_id, 'Quiz2', 10, 'Go', 'Stop', '', 'Nothing', 50, time(), null, 3, 0, 'TEST', 1, 'Questions', 'Nothing', '', 0, null);

        $this->assertTrue('Quiz2' == get_translated_text($GLOBALS['SITE_DB']->query_select_value('quizzes', 'q_name', ['id' => $this->quiz_id])));
    }

    public function tearDown()
    {
        delete_quiz($this->quiz_id);

        parent::tearDown();
    }
}
