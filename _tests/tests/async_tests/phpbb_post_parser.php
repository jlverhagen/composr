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
class phpbb_post_parser_test_set extends cms_test_case
{
    public function testParsing()
    {
        require_code('forum/phpbb3');

        $tests = [
            '<r>dss<B><s>[b]</s>d<e>[/b]</e></B>sd</r>' => 'dss[b]d[/b]sd', // BBCode enabled
            '<t>dss[b]d[/b]sd</t>' => 'dss[semihtml]&#91;[/semihtml]b]d[semihtml]&#91;[/semihtml]/b]sd', // BBCode disabled
            '<r><E>:D</E></r>' => ':D', // Emoticons enabled
            '<t>:D</t>' => '[semihtml]:D[/semihtml]', // Emoticons disabled
        ];
        foreach ($tests as $in => $expected_comcode) {
            $got_comcode = _phpbb3_post_text_to_comcode($in);
            $this->assertTrue($got_comcode == $expected_comcode, 'Expected ' . $expected_comcode . ' but got ' . $got_comcode);
        }

        $tests = [
            '<r>blah<ATTACHMENT filename="sample-logo.png" index="0"><s>[attachment=0]</s>sample-logo.png<e>[/attachment]</e></ATTACHMENT>blah</r>' => 'blah[attachment]12345[/attachment]blah', // With attachment (inline)
        ];
        foreach ($tests as $in => $expected_comcode) {
            $got_comcode = _phpbb3_post_text_to_comcode($in, [12345]);
            $this->assertTrue($got_comcode == $expected_comcode, 'Expected ' . $expected_comcode . ' but got ' . $got_comcode);
        }
    }
}
