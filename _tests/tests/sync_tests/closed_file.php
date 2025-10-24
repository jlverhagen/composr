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
class closed_file_test_set extends cms_test_case
{
    public function testClosedFile()
    {
        $path = get_file_base() . '/closed.html';
        $test = 'Test';
        file_put_contents($path, $test);
        sync_file($path);

        $url = static_evaluate_tempcode(build_url(['page' => ''], ''));
        $result = cms_http_request($url, ['trigger_error' => false]);

        $this->assertTrue((($result->download_url !== null) && ($result->download_url == (get_base_url() . '/closed.html'))), 'Expected to download the closed.html page, but instead got ' . (($result->download_url === null) ? 'null' : $result->download_url) . ' (if you have an OP cache enabled and the base URL is a subpath, this may cause the test to fail)');

        unlink($path);
        sync_file($path);
    }
}
