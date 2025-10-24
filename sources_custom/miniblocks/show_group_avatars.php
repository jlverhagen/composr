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
 * @package    show_group_avatars
 */

/*
    Parameters:

    order=date|random|username
    group_id
    limit
*/

i_solemnly_declare(I_UNDERSTAND_SQL_INJECTION | I_UNDERSTAND_XSS | I_UNDERSTAND_PATH_INJECTION);

if (!addon_installed('show_group_avatars')) {
    return do_template('RED_ALERT', ['_GUID' => '7dcc8480e51b5acdb4790fe09e9b457e', 'TEXT' => do_lang_tempcode('MISSING_ADDON', escape_html('show_group_avatars'))]);
}

if (get_forum_type() != 'cns') {
    return do_template('RED_ALERT', ['_GUID' => 'ed304b2a3b305270980c3c69471137c9', 'TEXT' => do_lang_tempcode('NO_CNS')]);
}

$order = 'm_join_time DESC';
if (isset($map['order'])) {
    if ($map['order'] == 'random') {
        $order = db_function('RAND');
    }
    if ($map['order'] == 'username') {
        $order = 'm_username';
    }
}

$where = 'm_avatar_url<>\'\' AND ' . db_string_equal_to('m_validated_email_confirm_code', '');
if (addon_installed('validation')) {
    $where .= ' AND m_validated=1';
}
if (isset($map['param'])) {
    if (is_numeric($map['param'])) {
        $group_id = intval($map['param']);
    } else {
        $group_id = $GLOBALS['FORUM_DB']->query_select_value_if_there('f_groups', 'id', [$GLOBALS['FORUM_DB']->translate_field_ref('g_name') => $map['param']]);
        if ($group_id === null) {
            $ret = paragraph(do_lang_tempcode('MISSING_RESOURCE'), '', 'nothing-here');
            $ret->evaluate_echo();
            return;
        }
    }
    $where .= ' AND (m_primary_group=' . strval($group_id) . ' OR EXISTS(SELECT gm_member_id FROM ' . $GLOBALS['FORUM_DB']->get_table_prefix() . 'f_group_members x WHERE x.gm_member_id=m.id AND gm_group_id=' . strval($group_id) . '))';
}

$limit = isset($map['limit']) ? intval($map['limit']) : 200;

require_code('cns_members2');

echo '<div class="clearfix">';

$query = 'SELECT m.* FROM ' . $GLOBALS['FORUM_DB']->get_table_prefix() . 'f_members m WHERE ' . $where . ' ORDER BY ' . $order;
$rows = $GLOBALS['FORUM_DB']->query($query, $limit);
foreach ($rows as $row) {
    $url = $GLOBALS['FORUM_DRIVER']->member_profile_url($row['id'], true);

    $avatar_url = $row['m_avatar_url'];
    if (url_is_local($avatar_url)) {
        $avatar_url = get_custom_base_url() . '/' . $avatar_url;
    }

    $username = $GLOBALS['FORUM_DRIVER']->get_username($row['id']);

    $tooltip = static_evaluate_tempcode(render_member_box($row['id'], true, false));

    echo '
        <div class="box left float-separation"><div class="box-inner">
            <a href="' . escape_html($url->evaluate()) . '"><img src="' . escape_html($avatar_url) . '" /></a><br />

            <a href="' . escape_html($url->evaluate()) . '" data-cms-tooltip="{ contents: \'' . escape_html(str_replace("\n", '\n', addslashes($tooltip))) . '\', triggers: \'hover focus\' }">' . escape_html($username) . '</a><br />
        </div></div>
    ';
}

echo '</div>';
