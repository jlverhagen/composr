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
 * @package    composr_tutorials
 */

i_solemnly_declare(I_UNDERSTAND_SQL_INJECTION | I_UNDERSTAND_XSS | I_UNDERSTAND_PATH_INJECTION);

$error_msg = new Tempcode();
if (!addon_installed__messaged('composr_tutorials', $error_msg)) {
    return $error_msg;
}
if (!addon_installed__messaged('testing_platform', $error_msg)) {
    return $error_msg;
}

require_lang('tutorials');

// Prompt for confirmation
if (post_param_integer('confirm', 0) == 0) {
    $preview = do_lang_tempcode('COMPILE_API_CONFIRM');
    $title = get_screen_title(do_lang('COMPILE_API_TITLE'), false);
    $url = get_self_url(false, false);
    return do_template('CONFIRM_SCREEN', ['_GUID' => 'aab9607451ae5c18a9e7a0756766453d', 'TITLE' => $title, 'PREVIEW' => $preview, 'FIELDS' => form_input_hidden('confirm', '1'), 'URL' => $url]);
}

$title = get_screen_title(do_lang('COMPILE_API_TITLE'), false);

require_code('tasks');
return call_user_func_array__long_task(do_lang('COMPILE_API_TITLE'), $title, 'compile_api', [], true, false);
