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
 * @package    cns_tapatalk
 */

define('IN_MOBIQUO', true);
define('FORUM_ROOT', __DIR__);

define('COMMON_CLASS_PATH_INCLUDE', __DIR__ . '/include');

include COMMON_CLASS_PATH_INCLUDE . '/common_functions.php';

initialise_composr();

if (isset($_GET['user_id'])) {
    $member_id = intval($_GET['user_id']);
} elseif (isset($_GET['username'])) {
    $member_id = $GLOBALS['FORUM_DRIVER']->get_member_from_username($_GET['username']);
} else {
    $member_id = get_member();
}

cms_ini_set('ocproducts.xss_detect', '0');

$url = $GLOBALS['FORUM_DRIVER']->get_member_avatar_url($member_id);
if ($url == '') {
    // Transparent 1x1 PNG
    header('Content-Type: image/png');
    echo base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABAQMAAAAl21bKAAAAA1BMVEUAAACnej3aAAAAAXRSTlMAQObYZgAAAApJREFUCNdjYAAAAAIAAeIhvDMAAAAASUVORK5CYII=');
    return;
}

$stem = get_base_url() . '/';
if (substr($url, 0, strlen($stem)) == $stem) {
    $url = get_file_base() . '/' . rawurldecode(substr($url, strlen($stem)));
} else {
    $stem = get_custom_base_url() . '/';
    if (substr($url, 0, strlen($stem)) == $stem) {
        $url = get_custom_file_base() . '/' . rawurldecode(substr($url, strlen($stem)));
    }
}

require_code('mime_types');
require_code('files');
header('Content-Type: ' . get_mime_type(get_file_extension($url), false));
ini_set('allow_url_fopen', '1');

cms_ob_end_clean();
@readfile($url);
