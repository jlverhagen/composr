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
class gallery_images_test_set extends cms_test_case
{
    protected $image_id;

    public function setUp()
    {
        parent::setUp();

        require_code('galleries');
        require_code('galleries2');

        $this->image_id = add_image('', '', '', 'themes/default/images/no_image.png', 0, 0, 0, 0, '', null, null, null, 0, null);

        // The software copies local images not in the galleries upload folder to the galleries upload folder
        $actual_path = $GLOBALS['SITE_DB']->query_select_value('images', 'url', ['id' => $this->image_id]);
        $this->assertTrue('uploads/galleries/no_image.png' == $actual_path, 'Wrong path: Got ' . $actual_path);
    }

    public function testEditGalleryImage()
    {
        edit_image($this->image_id, '', '', '', 'themes/default/images/blank.gif', 0, 0, 0, 0, '', '', '');

        // The software copies local images not in the galleries upload folder to the galleries upload folder
        $actual_path = $GLOBALS['SITE_DB']->query_select_value('images', 'url', ['id' => $this->image_id]);
        $this->assertTrue('uploads/galleries/blank.gif' == $actual_path, 'Wrong path: Got ' . $actual_path);
    }

    public function tearDown()
    {
        delete_image($this->image_id, false);

        parent::tearDown();
    }
}
