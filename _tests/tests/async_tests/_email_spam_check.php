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

// Spam checks may utilise APIs

/**
 * Composr test case class (unit testing).
 */
class _email_spam_check_test_set extends cms_test_case
{
    public function testSpamCheck()
    {
        $mime_email = 'From: John Doe <example@example.com>
MIME-Version: 1.0
Content-Type: multipart/mixed;
        boundary="XXXXboundary text"

This is a multipart message in MIME format.

--XXXXboundary text
Content-Type: text/plain

this is the body text

--XXXXboundary text
Content-Type: text/plain;
Content-Disposition: attachment;
        filename="test.txt"

this is the attachment text

--XXXXboundary text--';

        require_code('mail');
        require_code('mail2');
        try {
            list($spam_report, $spam_score) = email_spam_check($mime_email);
            $this->assertTrue($spam_report !== null, 'Failed to retrieve spam report');
            $this->assertTrue($spam_score !== null, 'Failed to retrieve spam score');
        } catch (Exception $e) {
            $this->assertTrue(false, 'Failed to retrieve spam information: ' . $e->getMessage());
        }
    }
}
