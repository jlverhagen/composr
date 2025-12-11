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
class xml_db_test_set extends cms_test_case
{
    protected $db;

    public function setUp()
    {
        parent::setUp();

        require_code('database/xml');
        $static = new Source_database_static_xml('cms_');
        $this->db = object_factory('Source_database_connector', false, ['test', 'localhost', 'root', '', 'cms_', false, $static]);

        $this->db->drop_table_if_exists('db_meta');
        $this->db->create_table('db_meta', [
            'm_table' => '*ID_TEXT',
            'm_name' => '*ID_TEXT',
            'm_type' => 'ID_TEXT',
        ]);

        $this->db->drop_table_if_exists('test');
        $this->db->create_table('test', [
            'id' => '*AUTO',
            'line1' => 'SHORT_TEXT',
            'line2' => 'SHORT_TEXT',
        ]);
        $this->db->query_insert('test', [
            'line1' => 'test1',
            'line2' => 'test2',
        ]);
        $this->db->query_insert('test', [
            'line1' => 'test1',
            'line2' => 'test2',
        ]);
        $this->db->query_insert('test', [
            'line1' => 'test1',
            'line2' => 'test2x',
        ]);
    }

    public function testCompoundDistinct()
    {
        $rows = $this->db->query_select('test', ['DISTINCT line1,line2'], [], 'ORDER BY line1');
        $this->assertTrue(count($rows) == 2);
        if (array_key_exists(0, $rows)) {
            $this->assertTrue(array_keys($rows[0]) == ['line1', 'line2']);
        }
    }

    public function testWildcardDistinct()
    {
        $rows = $this->db->query_select('test', ['DISTINCT *'], [], 'ORDER BY line1');
        $this->assertTrue(count($rows) == 3);

        $rows = $this->db->query_select('test r', ['DISTINCT r.*'], [], 'ORDER BY line1');
        if (array_key_exists(0, $rows)) {
            $this->assertTrue(count($rows[0]) == 3);
        }
        $this->assertTrue(count($rows) == 3);
    }

    public function testAliasDistinct()
    {
        $rows = $this->db->query_select('test', ['DISTINCT line1 AS foo,line2 AS bar'], [], 'ORDER BY line1');
        $this->assertTrue(count($rows) == 2);
        if (array_key_exists(0, $rows)) {
            $this->assertTrue(array_keys($rows[0]) == ['foo', 'bar']);
        }
    }

    public function testOrderDistinctConstraint()
    {
        $rows = $this->db->query_select('test', ['DISTINCT id'], [], 'ORDER BY id');
        $this->assertTrue(count($rows) == 3);

        $rows = $this->db->query_select('test', ['DISTINCT line1'], [], 'ORDER BY id', null, 0, true);
        $this->assertTrue($rows === null);
    }

    public function testGroupByConstraint()
    {
        $rows = $this->db->query_select('test', ['id'], [], 'GROUP BY id');
        $this->assertTrue(count($rows) == 3);

        $rows = $this->db->query_select('test', ['line1'], [], 'GROUP BY id', null, 0, true);
        $this->assertTrue($rows === null);
    }

    public function testCount()
    {
        $rows = $this->db->query_select('test', ['COUNT(*) AS cnt']);
        $this->assertTrue($rows[0]['cnt'] == 3);
        $this->assertTrue(count($rows) == 1);

        $rows = $this->db->query_select('test', ['COUNT(DISTINCT line2) AS cnt']);
        $this->assertTrue($rows[0]['cnt'] == 2);

        $rows = $this->db->query_select('test r', ['COUNT(DISTINCT r.line2) AS cnt']);
        $this->assertTrue($rows[0]['cnt'] == 2);

        $rows = $this->db->query_select('test r', ['COUNT(DISTINCT *) AS cnt']);
        $this->assertTrue($rows[0]['cnt'] == 3);
    }

    public function tearDown()
    {
        parent::tearDown();
    }
}
