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
class glossary_test_set extends cms_test_case
{
    public function testConsistentSize()
    {
        $c = cms_file_get_contents_safe(get_file_base() . '/docs/pages/comcode_custom/EN/sup_glossary.txt', FILE_READ_LOCK | FILE_READ_UNIXIFIED_TEXT | FILE_READ_BOM);
        $cnt = substr_count($c, '<tr');
        $this->assertTrue($cnt <= 100, 'Glossary should be restricted to 100 terms, but has ' . integer_format($cnt - 1) . '. Merge some terms if you have had to add new ones (e.g. put less important associated terms under a major term) and/or remove some.');

        // Next in line to remove - Web 2.0, PageRank
    }
}
