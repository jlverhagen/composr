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
class warnings_test_set extends cms_test_case
{
    protected $warn_id;

    public function setUp()
    {
        parent::setUp();

        if (!addon_installed('cns_warnings')) {
            $this->assertTrue(false, 'Test requires the cns_warnings addon');
            return;
        }

        if (get_forum_type() != 'cns') {
            $this->assertTrue(false, 'Test only works with Conversr');
            return;
        }

        require_code('cns_warnings');
        require_code('cns_warnings2');

        $this->establish_admin_session();

        $this->warn_id = cns_make_warning(1, 'nothing', null, null, 1);

        $this->assertTrue('nothing' == $GLOBALS['FORUM_DB']->query_select_value('f_warnings', 'w_explanation', ['id' => $this->warn_id]));
    }

    public function testConsistentOrdering()
    {
        if ((get_forum_type() != 'cns') || (!addon_installed('cns_warnings'))) {
            return;
        }

        $groups = [];

        $hooks = find_all_hook_obs('systems', 'cns_warnings', 'Hook_cns_warnings_');
        foreach ($hooks as $hook => $ob) {
            $info = $ob->get_details();
            if (!isset($info['order'])) {
                continue;
            }
            if (!isset($groups[$info['order']])) {
                $groups[$info['order']] = [];
            }
            $groups[$info['order']][$hook] = $info;
        }

        foreach ($groups as $order => $hooks) {
            if (count($hooks) > 1) {
                $hook_names = [];
                foreach ($hooks as $name => $hook) {
                    $hook_names[] = $name;
                }
                $this->assertTrue(false, 'Duplicate order ' . $order . ' in hooks/systems/cns_warnings ' . implode(', ', $hook_names));
            }
        }
    }

    public function testEditWarning()
    {
        if ((get_forum_type() != 'cns') || (!addon_installed('cns_warnings'))) {
            return;
        }

        cns_edit_warning($this->warn_id, 'something', 1);

        $this->assertTrue('something' == $GLOBALS['FORUM_DB']->query_select_value('f_warnings', 'w_explanation', ['id' => $this->warn_id]));
    }

    public function tearDown()
    {
        if ((get_forum_type() != 'cns') || (!addon_installed('cns_warnings'))) {
            return;
        }

        cns_delete_warning($this->warn_id);

        parent::tearDown();
    }
}
