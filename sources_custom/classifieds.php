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
 * @package    classified_ads
 */

function initialise_classified_listing(&$row)
{
    $free_days = $GLOBALS['SITE_DB']->query_select_value_if_there('ecom_classifieds_prices', 'MAX(c_days)', [
        'c_catalogue_name' => $row['c_name'],
        'c_price' => 0.00,
    ]);
    $row['ce_last_moved'] = $row['ce_add_date'];
    if ($free_days !== null) {
        $row['ce_last_moved'] += $free_days * 60 * 60 * 24;
    }
    $GLOBALS['SITE_DB']->query_update('catalogue_entries', ['ce_last_moved' => $row['ce_last_moved']], ['id' => $row['id']], '', 1);
}
