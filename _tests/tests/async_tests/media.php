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
class media_test_set extends cms_test_case
{
    public function setUp()
    {
        require_code('media_renderer');
        require_code('images');

        parent::setUp();
    }

    public function testVimeoThumbnail()
    {
        $test_url = 'https://vimeo.com/channels/staffpicks/264037633';
        require_code('hooks/systems/media_rendering/vimeo');
        $ob = new Hook_media_rendering_vimeo();
        $thumb_url = $ob->get_video_thumbnail($test_url);
        $this->assertTrue($thumb_url !== null);
        if ($thumb_url !== null) {
            $test = cms_getimagesizefromstring(http_get_contents($thumb_url));
            $this->assertTrue(is_array($test) && is_integer($test[0]) && is_integer($test[1]));
        }
    }

    public function testYouTubeThumbnail()
    {
        $test_url = 'https://www.youtube.com/watch?v=C1bLJieqbhk';
        require_code('hooks/systems/media_rendering/youtube');
        $ob = new Hook_media_rendering_youtube();
        $thumb_url = $ob->get_video_thumbnail($test_url);
        $this->assertTrue($thumb_url !== null);
        if ($thumb_url !== null) {
            $test = cms_getimagesizefromstring(http_get_contents($thumb_url));
            $this->assertTrue(is_array($test) && is_integer($test[0]) && is_integer($test[1]));
        }
    }

    public function testFacebookThumbnail()
    {
        /*
        $test_url = 'https://www.facebook.com/CollegeHumor/videos/10154300448557807/';
        require_code('hooks/systems/media_rendering/video_facebook');
        $ob = new Hook_media_rendering_video_facebook();
        $thumb_url = $ob->get_video_thumbnail($test_url);
        $this->assertTrue($thumb_url !== null);
        if ($thumb_url !== null) {
            $test = cms_getimagesizefromstring(http_get_contents($thumb_url));
            $this->assertTrue(is_array($test) && is_integer($test[0]) && is_integer($test[1]));
        }
    */
        $message = 'Facebook must be tested manually as API requires authentication.';
        $this->dump($message, 'INFO:');
    }
}
