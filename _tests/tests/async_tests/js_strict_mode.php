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
class js_strict_mode_test_set extends cms_test_case
{
    public function testInStrictMode()
    {
        $templates = [];
        $path = get_file_base() . '/themes/default/javascript';
        $dh = opendir($path);
        while (($file = readdir($dh)) !== false) {
            if (cms_strtolower_ascii(substr($file, -3)) == '.js') {
                if (in_array($file, [
                    '_attachment_ui_defaults.js',
                    'button_realtime_rain.js',
                    'skitter.js',
                    'jquery.js',
                    'webfontloader.js',
                    'jquery_autocomplete.js',
                    'modernizr.js',
                    '_wysiwyg_settings.js',
                    'xsl_mopup.js',
                    '_polyfill_web_animations.js',
                    'toastify.js',
                    'password_checks.js', // Not a standalone file
                ])) {
                    continue;
                }

                $c = cms_file_get_contents_safe($path . '/' . $file, FILE_READ_LOCK | FILE_READ_BOM);

                $this->assertTrue(strpos($c, 'use strict') !== false, 'Strict mode not enabled for ' . $file);
            }
        }
        closedir($dh);
    }
}
