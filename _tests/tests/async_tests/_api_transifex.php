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
class _api_transifex_test_set extends cms_test_case
{
    public function setUp()
    {
        parent::setUp();

        require_code('transifex');

        $this->load_key_options('transifex');
    }

    public function testPull()
    {
        if (!addon_installed('transifex')) {
            $this->assertTrue(false, 'The transifex addon must be installed for this test to run');
            return;
        }

        require_code('tar');
        $temp_nam = cms_tempnam();
        $tar_file = tar_open($temp_nam, 'wb');

        $project_slug = get_composr_transifex_project('10'); // TODO: Change this when v11 released
        $files = [];
        _pull_ini_file_from_transifex($project_slug, $tar_file, 'DE', 'global', $files);

        tar_close($tar_file);

        $tar_file = tar_open($temp_nam, 'rb');
        $details = tar_get_file($tar_file, 'lang_custom/DE/global.ini');
        $this->assertTrue($details !== null, 'lang_custom/DE/global.ini missing');
        if ($details !== null) {
            $this->assertTrue(strpos($details['data'], 'ffnen') !== false);
        }
        tar_close($tar_file);

        unlink($temp_nam);
    }
}
