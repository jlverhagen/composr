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
class _tutorial_quality_test_set extends cms_test_case
{
    public function setUp()
    {
        parent::setUp();

        if (in_safe_mode()) {
            $this->assertTrue(false, 'Cannot work in safe mode');
            return;
        }

        if ($this->debug) {
            cms_ob_end_clean();
        }

        disable_php_memory_limit();

        cms_extend_time_limit(TIME_LIMIT_EXTEND__CRAWL);
    }

    public function testValidComcode()
    {
        if (in_safe_mode()) {
            return;
        }

        require_code('comcode_check');

        $path = get_file_base() . '/docs/pages/comcode_custom/EN';
        $dh = opendir($path);
        while (($file = readdir($dh)) !== false) {
            if (($this->only !== null) && ($this->only != $file)) {
                continue;
            }

            if (substr($file, -4) == '.txt') {
                if ($this->debug) {
                    var_dump($file);
                }

                $c = cms_file_get_contents_safe($path . '/' . $file, FILE_READ_LOCK | FILE_READ_BOM);
                check_comcode($c, null, true); // This is quite slow
            }
        }
        closedir($dh);
    }
}
