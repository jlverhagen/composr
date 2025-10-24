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
class templates_test_set extends cms_test_case
{
    public function setUp()
    {
        parent::setUp();

        require_code('themes2');
    }

    public function testTemplateParameterDetectionViaPreview()
    {
        $parameters = find_template_parameters('templates/DOWNLOAD_BOX.tpl');
        $this->assertTrue(in_array('EDIT_DATE_RAW', $parameters)); // Template scan would not find EDIT_DATE_RAW, as not used by default
    }

    public function testTemplateParameterDetectionViaScan()
    {
        $parameters = find_template_parameters('templates/ACTIVITY_FEED_ACTIVITY.tpl'); // Has no preview, so will have to do a template scan
        $this->assertTrue(in_array('TIMESTAMP', $parameters));
    }

    public function testTemplateGUIDs()
    {
        $guids = find_template_guids('templates/INDEX_SCREEN.tpl');
        $this->assertTrue(count($guids) > 1);
    }
}
