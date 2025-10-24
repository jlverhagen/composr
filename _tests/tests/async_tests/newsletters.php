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
class newsletters_test_set extends cms_test_case
{
    protected $news_id;

    public function setUp()
    {
        parent::setUp();

        require_code('newsletter');
        require_code('newsletter2');

        $this->news_id = add_newsletter('New Offer', 'The new offer of the week.');

        $this->assertTrue('New Offer' == get_translated_text($GLOBALS['SITE_DB']->query_select_value('newsletters', 'title', ['id' => $this->news_id])));
    }

    public function testEditNewsletter()
    {
        edit_newsletter($this->news_id, 'Thanks', 'Thank you');

        $this->assertTrue('Thanks' == get_translated_text($GLOBALS['SITE_DB']->query_select_value('newsletters', 'title', ['id' => $this->news_id])));
    }

    public function testVariableSubstitution()
    {
        $message_raw = 'abc {FORENAME} {SURNAME} {NAME} {EMAIL_ADDRESS} {SEND_ID} {X123}';
        $subject = 'def {FORENAME}';
        $forename = 'ghi';
        $surname = 'jkl';
        $name = 'mno';
        $email_address = 'pqr@example.com';
        $send_id = 'stu';
        $hash = 'vwx';
        $extra_mappings = [
            'x123' => 'yz'
        ];

        $message_wrapped = newsletter_prepare($message_raw, $subject, null, $forename, $surname, $name, $email_address, $send_id, $hash, $extra_mappings);

        // Comcode
        $got = preg_replace('#\][^\[\]]*\[/url\]#', '][/url]', $message_wrapped);
        $expected = "abc ghi jkl mno pqr@example.com stu yz\n\n\n-------------------------\n\n[font size=\"0.8\"]You can [url=\"unsubscribe\"][/url] from this newsletter[/font]\n\n";
        $this->assertTrue($got == $expected, 'Got: ' . $got . '; Expected: ' . $expected);

        // HTML
        $got = str_replace(' data-click-stats-event-track="{}"', '', preg_replace('# href="[^"]*"#', ' href=""', comcode_to_tempcode($message_wrapped, null, true)->evaluate()));
        $expected = "#^abc ghi jkl mno pqr@example.com stu yz<br /><br /><br /><hr />\n<span style=\"  font-size: 0.8em;\">You can <a class=\"user-link\" href=\"\" (rel=\"external\" target=\"_blank\"|target=\"_top\")( title=\"xxx \(this link will open in a new window\)\")?" . ">unsubscribe</a> from this newsletter</span><br /><br />$#";
        $this->assertTrue(preg_match($expected, $got) != 0, 'Got: ' . $got . '; Expected: ' . $expected);
    }

    public function tearDown()
    {
        delete_newsletter($this->news_id);

        parent::tearDown();
    }
}
