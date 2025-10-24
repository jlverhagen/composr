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
class sensible_git_branches_test_set extends cms_test_case
{
    public function testSensibleBranches()
    {
        if (!addon_installed('cms_homesite')) {
            $this->assertTrue(false, 'cms_homesite addon is required');
            return;
        }

        require_code('version');
        require_code('cms_homesite');

        $branches = get_composr_branches();
        foreach ($branches as $branch) {
            $this->assertTrue(($branch['status'] != VERSION_MAINLINE) || (in_array($branch['git_branch'], ['master', 'main'])), $branch['git_branch'] . ' does not seem to have the expected version status');
        }
    }
}
