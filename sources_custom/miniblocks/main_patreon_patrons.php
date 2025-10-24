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
 * @package    patreon
 */

i_solemnly_declare(I_UNDERSTAND_SQL_INJECTION | I_UNDERSTAND_XSS | I_UNDERSTAND_PATH_INJECTION);

if (!addon_installed('patreon')) {
    return do_template('RED_ALERT', ['_GUID' => 'a0af2478810a5aeaae447db2ecc6e0c8', 'TEXT' => do_lang_tempcode('MISSING_ADDON', escape_html('cms_homesite'))]);
}

require_code('patreon');
$level = isset($map['level']) ? intval($map['level']) : 30;
$patreon_patrons = get_patreon_patrons_on_minimum_level($level);

if (empty($patreon_patrons)) {
    // Auto-import if nothing yet
    patreon_sync();
    $patreon_patrons = get_patreon_patrons_on_minimum_level($level);

    // Ok, test data
    if ((empty($patreon_patrons)) && ($GLOBALS['DEV_MODE'])) {
        require_code('lorem');

        $map = [
            'p_member_id' => db_get_first_id() + 1,
            'p_tier' => lorem_word(),
            'p_id' => placeholder_number(),
            'p_monthly' => placeholder_number(),
            'p_name' => lorem_phrase(),
        ];
        $GLOBALS['SITE_DB']->query_insert('patreon_patrons', $map);

        $patreon_patrons = get_patreon_patrons_on_minimum_level($level);
    }
}

$_patreon_patrons = [];
foreach ($patreon_patrons as $patron) {
    $_patreon_patrons[] = [
        'NAME' => $patron['name'],
        'USERNAME' => $patron['username'],
        'MEMBER_ID' => strval($patron['p_member_id']),
        'TIER' => $patron['tier'],
    ];
}

$tpl = do_template('BLOCK_MAIN_PATREON_PATRONS', ['_GUID' => '8b7ed8319aa6ec0e6bc0e8b5e1fede4d', 'PATREON_PATRONS' => $_patreon_patrons]);
$tpl->evaluate_echo();
