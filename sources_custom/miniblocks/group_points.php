<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2016

 See text/EN/licence.txt for full licencing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    group_points
 */

i_solemnly_declare(I_UNDERSTAND_SQL_INJECTION | I_UNDERSTAND_XSS | I_UNDERSTAND_PATH_INJECTION);

require_code('points');

$_id = isset($map['param']) ? $map['param'] : '';
if ($_id == '') {
    $id = get_member();
} else {
    $id = intval($_id);
}
$username = $GLOBALS['FORUM_DRIVER']->get_username($id, true);

$group_points = get_group_points();

$fields = new Tempcode();

asort($group_points);

echo '<div class="wide_table_wrap"><table class="results_table wide_table spaced_table"><thead></thead><tbody><tr><th>Usergroup</th><th>One-off point bonus</th><th>Monthly points</th><th>' . escape_html($username) . ' in this group?</th></tr>';

$groups = $GLOBALS['FORUM_DRIVER']->get_usergroup_list(false, true, true);

$my_groups = $GLOBALS['FORUM_DRIVER']->get_members_groups($id);

$done = 0;

foreach ($group_points as $group_id => $points) {
    if (($points['p_points_one_off'] != 0) || (in_array($group_id, $my_groups))) {
        $group_name = $groups[$group_id];
        echo '<tr>
            <td>' . escape_html($group_name) . '</td>
            <td>' . escape_html(integer_format($points['p_points_one_off'])) . '</td>
            <td>' . escape_html(integer_format($points['p_points_per_month'])) . ' <span class="associated_details">per month</span></td>
            <td>' . (in_array($group_id, $my_groups) ? '<img src="' . escape_html(find_theme_image('checklist/checklist1')) . '" /> Yes' : '<img src="' . escape_html(find_theme_image('checklist/checklist0')) . '" /> No') . '</td>
        </tr>';

        $done++;
    }
}

if ($done == 0) {
    echo '<tr><td colspan="4"><p class="nothing_here">No bonuses configured yet. <a href="' . escape_html(static_evaluate_tempcode(build_url(array('page' => 'group_points'), 'adminzone'))) . '">Configure bonuses here</a>.</p></td></tr>';
}

echo '</tbody></table></div>';
