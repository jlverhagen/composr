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
class should_ignore_file_test_set extends cms_test_case
{
    public function testShouldIgnoreFile()
    {
        require_code('files');

        $this->assertTrue(should_ignore_file('data_custom/unit_test_positive_ignore_sampler.xxx', IGNORE_UNSHIPPED_VOLATILE), 'Failing positive ignore');

        $this->assertTrue(!should_ignore_file('data_custom/unit_test_negative_ignore_sampler.xxx', IGNORE_UNSHIPPED_VOLATILE), 'Failing negative ignore');

        $this->assertTrue(!should_ignore_file('unit_test_positive_ignore_sampler.xxx', IGNORE_UNSHIPPED_VOLATILE), 'Failing negative ignore (root file)'); // should not fail in root, as ignore rule is scoped to data_custom
    }
}
