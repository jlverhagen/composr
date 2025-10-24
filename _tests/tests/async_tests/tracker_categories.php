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
class tracker_categories_test_set extends cms_test_case
{
    public function testHasAddons()
    {
        $brand_base_url = get_brand_base_url();
        $post = [];
        $_categories = http_get_contents($brand_base_url . '/data/endpoint.php/cms_homesite/tracker_categories', ['convert_to_internal_encoding' => true, 'ua' => 'Composr Test Platform']);
        $categories = json_decode($_categories, true);
        $addons = find_all_hooks('systems', 'addon_registry');
        foreach ($addons as $addon_name => $place) {
            if ($place == 'sources') {
                $this->assertTrue(in_array($addon_name, $categories['response_data']), $addon_name);
            }
        }
    }

    public function testNoUnknownAddons()
    {
        $brand_base_url = get_brand_base_url();
        $_categories = http_get_contents($brand_base_url . '/data/endpoint.php/cms_homesite/tracker_categories', ['convert_to_internal_encoding' => true, 'ua' => 'Composr Test Platform']);
        $categories = json_decode($_categories, true);
        $addons = find_all_hooks('systems', 'addon_registry');
        foreach ($categories['response_data'] as $category) {
            if (cms_strtolower_ascii($category) != $category) {
                continue; // Only lower case must correspond to addons
            }

            $this->assertTrue(array_key_exists($category, $addons), $category);
        }
    }
}
