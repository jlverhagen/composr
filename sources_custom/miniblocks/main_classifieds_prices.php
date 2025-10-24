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

i_solemnly_declare(I_UNDERSTAND_SQL_INJECTION | I_UNDERSTAND_XSS | I_UNDERSTAND_PATH_INJECTION);

if (!addon_installed('classified_ads')) {
    return do_template('RED_ALERT', ['_GUID' => '9f069bc8cd075e8bb0e6d653208a0a8c', 'TEXT' => do_lang_tempcode('MISSING_ADDON', escape_html('classified_ads'))]);
}

if (!addon_installed('catalogues')) {
    return do_template('RED_ALERT', ['_GUID' => '2bb675ffc41f5df9a3ade64d25c1d755', 'TEXT' => do_lang_tempcode('MISSING_ADDON', escape_html('catalogues'))]);
}
if (!addon_installed('ecommerce')) {
    return do_template('RED_ALERT', ['_GUID' => '3316b32121235a6890629b838cd400bc', 'TEXT' => do_lang_tempcode('MISSING_ADDON', escape_html('ecommerce'))]);
}

require_lang('classifieds');

if (!isset($map['param'])) {
    $map['param'] = 'classifieds';
}
$catalogue_name = $map['param'];

$show_free = ((isset($map['show_free'])) && ($map['show_free'] == '1'));

$prices = $GLOBALS['SITE_DB']->query_select('ecom_classifieds_prices', ['*'], ['c_catalogue_name' => $catalogue_name], 'ORDER BY c_price');

$data = [];
foreach ($prices as $price) {
    if ((!$show_free) && ($price['c_price'] == 0.0)) {
        continue;
    }

    $data[] = [
        'PRICE' => float_to_raw_string($price['c_price']),
        'CURRENCY' => get_option('currency'),
        'LABEL' => get_translated_text($price['c_label']),
    ];
}

echo static_evaluate_tempcode(do_template('CLASSIFIEDS', ['_GUID' => '7216f4a435534cc609344101c8ea3031', 'DATA' => $data]));
