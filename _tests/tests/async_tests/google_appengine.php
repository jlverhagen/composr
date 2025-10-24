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
class google_appengine_test_set extends cms_test_case
{
    public function testPregConstraint()
    {
        require_code('files2');

        cms_extend_time_limit(TIME_LIMIT_EXTEND__SLOW);

        $files = get_directory_contents(get_file_base(), '', IGNORE_ALIEN | IGNORE_SHIPPED_VOLATILE | IGNORE_UNSHIPPED_VOLATILE | IGNORE_NONBUNDLED | IGNORE_FLOATING, true, true, ['php']);
        $files[] = 'install.php';
        foreach ($files as $path) {
            $c = cms_file_get_contents_safe(get_file_base() . '/' . $path);

            if (preg_match('#preg_(replace|replace_callback|match|match_all|grep|split)\(\'(.)[^\']*(?<!\\\\)\\2[^\']*e#', $c) != 0) {
                $this->assertTrue(false, 'regexp /e not allowed (in ' . $path . ')');
            }

            /*
            Think Google AppEngine was since fixed, and we use this for symlink resolution
            if ((strpos($c, '\'SCRIPT_FILENAME\'') !== false) && ($path != 'sources/bootstrap.php') && ($path != 'sources/minikernel.php') && ($path != 'sources/global.php') && ($path != 'sources/global2.php') && ($path != 'sources_custom/phpstub.php')) {
                $this->assertTrue(false, 'SCRIPT_FILENAME does not work stably across platforms (in ' . $path . ')');
            }
            */
        }
    }
}
