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
class cma_hooks_test_set extends cms_test_case
{
    protected $all_cma_info = [];
    protected $all_keys = [];

    public function setUp()
    {
        parent::setUp();

        require_code('content');

        $this->all_keys = [];

        $cma_hooks = find_all_hooks('systems', 'content_meta_aware') + find_all_hooks('systems', 'resource_meta_aware');
        foreach (array_keys($cma_hooks) as $content_type) {
            if ($content_type == 'temp_test') {
                continue;
            }

            $cma_ob = get_content_object($content_type);
            $cma_info = $cma_ob->info();

            if ($cma_info === null) {
                continue;
            }

            $this->all_cma_info[$content_type] = $cma_info;
            $this->all_keys = array_unique(array_merge($this->all_keys, array_keys($cma_info)));
        }
    }

    public function testAllPropertiesDefined()
    {
        // Too specific to want to define it all for each hook
        $may_be_unset_properties = [
            'edit_page_link_field',
            'edit_page_link_pattern_post',
            'title_field_post',
            'filtercode',
            'filtercode_protected_fields',
            'parent_category_field__resource_fs',
            'title_field_dereference__resource_fs',
            'title_field_supports_comcode',
            'title_field__resource_fs',
            'extra_where_sql',
            'active_only_extra_where_sql',
            'prominence_custom_sort',
            'prominence_custom_sort_dir',
            'content_type_label_override',
            'content_type_universal_label_override',
            'video_field',
            'video_generator',
            'validation_is_minor',
            'uses_workflow',
        ];

        foreach ($this->all_cma_info as $content_type => $cma_info) {
            foreach ($this->all_keys as $key) {
                if (in_array($key, $may_be_unset_properties)) {
                    continue;
                }

                $this->assertTrue(array_key_exists($key, $cma_info), $key . ' not defined for ' . $content_type);
            }
        }
    }
}
