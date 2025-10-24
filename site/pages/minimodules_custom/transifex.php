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
 * @package    transifex
 */

i_solemnly_declare(I_UNDERSTAND_SQL_INJECTION | I_UNDERSTAND_XSS | I_UNDERSTAND_PATH_INJECTION);

$error_msg = new Tempcode();
if (!addon_installed__messaged('transifex', $error_msg)) {
    return $error_msg;
}

$title = get_screen_title('Composr translations, via Transifex', false);

require_code('transifex');
require_code('lang2');
require_code('http');

$project_slug = 'composr-cms-' . str_replace('.', '-', strval(cms_version()));

$test = cache_and_carry('_transifex', ['/project/' . $project_slug . '/languages/', 'GET', null, false], 10);
if (is_string($test)) {
    $test = unserialize($test);
}
if ($test[1] == '200') {
    $languages = list_to_map('language_code', json_decode($test[0], true));
} else {
    $languages = [];
}

$_languages = [];

foreach ($languages as $language_code => $language_details_basic) {
    if (empty($language_details_basic['reviewers']) && empty($language_details_basic['translators'])) {
        continue; // Not started yet
    }

    $language_name = lookup_language_full_name(cms_strtoupper_ascii($language_code));

    $test = cache_and_carry('_transifex', ['/project/' . $project_slug . '/language/' . $language_code . '/?details', 'GET', null, false], 10);
    if (is_string($test)) {
        $test = unserialize($test);
    }
    if ($test[1] == '200') {
        $language_details = json_decode($test[0], true);

        $percentage = intval(round(100.0 * $language_details['translated_segments'] / $language_details['total_segments'])); // calculate %age

        $download_url = find_script('transifex_pull');
        $download_url .= '?lang=' . urlencode(cms_strtoupper_ascii($language_code));
        $download_url .= '&output=1';

        $download_core_url = find_script('transifex_pull');
        $download_core_url .= '?lang=' . urlencode(cms_strtoupper_ascii($language_code));
        $download_core_url .= '&core_only=1';
        $download_core_url .= '&output=1';

        $_languages[str_pad(strval($percentage), 3, '0', STR_PAD_LEFT) . '__' . $language_code] = [
            'LANGUAGE_CODE' => cms_strtoupper_ascii($language_code),
            'LANGUAGE_NAME' => $language_name,
            'TRANSLATORS' => implode(', ', array_merge($language_details_basic['reviewers'], $language_details_basic['translators'])),
            'PERCENTAGE' => integer_format($percentage) . '%',
            'DOWNLOAD_URL' => $download_url,
            'DOWNLOAD_CORE_URL' => $download_core_url,
        ];
    }
}

ksort($_languages);
$_languages = array_reverse($_languages);

require_code('form_templates');
list($warning_details, $ping_url) = handle_conflict_resolution('', false);

return do_template('TRANSIFEX_SCREEN', [
    '_GUID' => '56c6b6d32f1794be3114a1b95f0a7ec5',
    'TITLE' => $title,
    'LANGUAGES' => $_languages,
    'WARNING_DETAILS' => $warning_details,
    'PING_URL' => $ping_url,
]);
