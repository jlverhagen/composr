<?php /*

Composr
Copyright (c) Christopher Graham, 2004-2024

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
class foreign_keys_test_set extends cms_test_case
{

    public function testDataIntegrityScan()
    {
        // Sanity checks
        if (strpos(get_db_type(), 'mysql') === false) {
            $this->assertTrue(false, 'MySQL required for this test.');
            return;
        }
        if (get_value('innodb', '0') != '1') {
            $this->assertTrue(false, 'InnoDB required for this test.');
            return;
        }
        if ($GLOBALS['SITE_DB']->query_select_value_if_there('group_zone_access', 'group_id', ['zone_name' => '', 'group_id' => 1]) === null) {
            $this->assertTrue(false, 'This test will not work correctly unless Guests have access to the welcome zone. Please fix that.');
            return;
        }

        // Insert a privilege with an invalid module / page name
        require_code('developer_tools');
        $insert = make_dummy_db_row('group_privileges', false);

        // Run data integrity (this should remove the invalid record we created)
        require_code('upgrade_mysql');
        _upgrader_data_integrity_screen();

        // Check if our dummy privilege still exists; it shouldn't
        $data = $GLOBALS['SITE_DB']->query_select_value_if_there('group_privileges', 'the_page', $insert);
        $this->assertTrue(($data === null), 'Failed to clean up an orphaned group_privileges record.');

        // Reverse-check; find something else mapped by foreign keys which should *not* have been removed
        $data2 = $GLOBALS['SITE_DB']->query_select_value_if_there('group_zone_access', 'group_id', ['zone_name' => '', 'group_id' => 1]);
        $this->assertTrue(($data2 !== null), 'Data integrity removed a group_zone_access record (Guests on the welcome zone) when it should not have.');
    }
}
