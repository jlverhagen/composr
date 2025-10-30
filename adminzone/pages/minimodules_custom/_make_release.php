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
 * @package    cms_homesite
 */

/*EXTRA FUNCTIONS: shell_exec*/

/* To be called by make_release.php - not directly linked from menus */

i_solemnly_declare(I_UNDERSTAND_SQL_INJECTION | I_UNDERSTAND_XSS | I_UNDERSTAND_PATH_INJECTION);

if (!addon_installed('downloads')) {
    warn_exit(do_lang_tempcode('MISSING_ADDON', escape_html('downloads')));
}
if (!addon_installed('news')) {
    warn_exit(do_lang_tempcode('MISSING_ADDON', escape_html('news')));
}
if (!addon_installed('addon_publish')) {
    warn_exit(do_lang_tempcode('MISSING_ADDON', escape_html('addon_publish')));
}

$error_msg = new Tempcode();
if (!addon_installed__messaged('cms_homesite', $error_msg)) {
    return $error_msg;
}

require_code('cms_homesite');

$title = get_screen_title('Publish new ' . brand_name() . ' release', false);
$title->evaluate_echo();

$version_dotted = post_param_string('version');
$is_old_tree = post_param_integer('is_old_tree') == 1;
$is_bleeding_edge = post_param_integer('is_bleeding_edge') == 1;
$video_url = post_param_string('video_url', '', INPUT_FILTER_URL_GENERAL);
$changes = post_param_string('changes', '');
$descrip = post_param_string('descrip', '', INPUT_FILTER_GET_COMPLEX);
$needed = post_param_string('needed', '', INPUT_FILTER_GET_COMPLEX);
$criteria = post_param_string('criteria', '', INPUT_FILTER_GET_COMPLEX);
$justification = post_param_string('justification', '', INPUT_FILTER_GET_COMPLEX);
$db_upgrade = post_param_integer('db_upgrade', 0) == 1;

$urls = cms_publish_release($version_dotted, $is_old_tree, $is_bleeding_edge, $video_url, $changes, $descrip, $needed, $criteria, $justification, $db_upgrade);

// DONE!

echo '<p>Done version ' . escape_html($version_dotted) . '!</p>';

echo '<ul>';

foreach ($urls as $_link_title => $link_url) {
    if (is_object($_link_title)) {
        $link_title = $_link_title->evaluate();
    } else {
        $link_title = strval($_link_title);
    }
    echo '<li><a href="' . escape_html($link_url) . '">' . $link_title . '</a></li>';
}
echo '</ul>';
