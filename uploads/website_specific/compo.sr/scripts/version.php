<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2016

 You may not distribute a modified version of this file, unless it is solely as a Composr modification.
 See text/EN/licence.txt for full licencing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    composr_homesite
 */

// Example test URL:
//  http://localhost/composr/uploads/website_specific/compo.sr/scripts/version.php?version=13.0.0&test_mode=1&html=1

// Fixup SCRIPT_FILENAME potentially being missing
$_SERVER['SCRIPT_FILENAME'] = __FILE__;

// Find Composr base directory, and chdir into it
global $FILE_BASE, $RELATIVE_PATH;
$FILE_BASE = realpath(__FILE__);
$deep = 'uploads/website_specific/compo.sr/scripts/';
$FILE_BASE = str_replace($deep, '', $FILE_BASE);
$FILE_BASE = str_replace(str_replace('/', '\\', $deep), '', $FILE_BASE);
if (substr($FILE_BASE, -4) == '.php') {
    $a = strrpos($FILE_BASE, '/');
    $b = strrpos($FILE_BASE, '\\');
    $FILE_BASE = dirname($FILE_BASE);
}
$RELATIVE_PATH = '';
@chdir($FILE_BASE);

global $FORCE_INVISIBLE_GUEST;
$FORCE_INVISIBLE_GUEST = true;
global $EXTERNAL_CALL;
$EXTERNAL_CALL = false;
if (!is_file($FILE_BASE . '/sources/global.php')) {
    exit('<html><head><title>Critical startup error</title></head><body><h1>Composr startup error</h1><p>The second most basic Composr startup file, sources/global.php, could not be located. This is almost always due to an incomplete upload of the Composr system, so please check all files are uploaded correctly.</p><p>Once all Composr files are in place, Composr must actually be installed by running the installer. You must be seeing this message either because your system has become corrupt since installation, or because you have uploaded some but not all files from our manual installer package: the quick installer is easier, so you might consider using that instead.</p><p>ocProducts maintains full documentation for all procedures and tools, especially those for installation. These may be found on the <a href="http://compo.sr">Composr website</a>. If you are unable to easily solve this problem, we may be contacted from our website and can help resolve it for you.</p><hr /><p style="font-size: 0.8em">Composr is a website engine created by ocProducts.</p></body></html>');
}
require($FILE_BASE . '/sources/global.php');

require_lang('composr_homesite');
require_code('composr_homesite');
require_code('version2');

header('Content-type: text/plain; charset=' . get_charset());
if (get_param_integer('html', 0) == 1) {
    header('Content-type: text/html');
    echo '<script src="' . get_base_url() . '/themes/default/templates_cached/EN/javascript.js"></script>';
}

$version_dotted = get_param_string('version');
$version_pretty = get_version_pretty__from_dotted($version_dotted);
list(, $qualifier, $qualifier_number, $long_dotted_number, , $long_dotted_number_with_qualifier) = get_version_components__from_dotted($version_dotted);

// Work out upgrade paths
$release_tree = get_release_tree();
$higher_versions = array(null, null, null, null);
$description = '';
foreach ($release_tree as $other_version_dotted => $download_row) { // As $release_tree is sorted we will keep updating recommendations with newer, so we end with the newest on each level
    list(, $other_qualifier, $other_qualifier_number, $other_long_dotted_number, , $other_long_dotted_number_with_qualifier) = get_version_components__from_dotted($other_version_dotted);

    if (version_compare($long_dotted_number_with_qualifier, $other_long_dotted_number_with_qualifier, '>=')) {
        continue;
    }

    // Ok it's newer...

    $differs_at = find_biggest_branch_differ_position($long_dotted_number_with_qualifier, $other_long_dotted_number_with_qualifier);
    if ($differs_at === null) {
        fatal_exit(do_lang_tempcode('INTERNAL_ERROR'));
    }

    $other_version_pretty = get_version_pretty__from_dotted($other_version_dotted);

    $news_row = find_version_news($other_version_pretty);

    if ($news_row === null) {
        continue; // Somehow the news post is missing, we'll consider the release pulled
    }

    // We chain all the download descriptions together; each says why the version involved is out of date, so together it is like a "why upgrade" history. The news posts on the other hand says what a new version itself offers.
    if (strlen($download_row['nice_description']) < 3000) { // If not too long
        if ($description != '') {
            $description = "\n---\n" . $description;
        }
        $description .= strip_download_description($download_row['nice_description']);
    }

    $higher_versions[$differs_at] = array(
        'version_pretty' => $other_version_pretty,
        'version_dotted' => $other_version_dotted,
        'news_id' => $news_row['id'],
        'download_description' => $description,
        'add_date' => $download_row['add_date'],
    );
}
$has_jump = ($higher_versions[0] !== null) || ($higher_versions[1] !== null) || ($higher_versions[2] !== null) || ($higher_versions[3] !== null);

// Current version
$our_version = null;
$download_row = find_version_download($version_pretty, 'quick');
if ($download_row !== null) {
    $our_version = array(
        'version_pretty' => $version_pretty,
        'version_dotted' => $version_dotted,
        'download_description' => strip_download_description($download_row['nice_description']),
        'add_date' => $download_row['add_date'],
    );
}
echo '<h3 class="notes_about">Notes about your current version (' . escape_html($version_pretty) . ')</h3>';
if ($our_version !== null) {
    if (!$has_jump) {
        $descrip = $our_version['download_description'] . ' You are running the latest version.';
    } else {
        $descrip = 'You are <strong>not</strong> running the latest version. Browse the <a title="Composr news archive (this link will open in a new window)" target="_blank" href="' . escape_html(static_evaluate_tempcode(build_url(array('page' => 'news'), 'site'))) . '">Composr news archive</a> for a full list of the updates or see below for recommended paths.';
    }
    echo '<p>' . $descrip . '</p>';
} else {
    echo '<p>This version does not exist in our database. This means it is either very new, or unsupported (or we have made a mistake - in which case, please contact us).</p>';
}

// Latest versions
if ($has_jump) {
    echo '<h3>Latest recommended upgrade paths</h3>';

    $upgrade_type = array('major upgrade, may break compatibility of customisations', 'feature upgrade', 'easy patch upgrade');
    for ($i = 0; $i <= 3; $i++) {
        if ($higher_versions[$i] !== null) {
            display_version_upgrade_path($higher_versions[$i]);
        }
    }
}

function find_biggest_branch_differ_position($long_dotted_number_with_qualifier, $other_long_dotted_number_with_qualifier)
{
    $parts = explode('.', $long_dotted_number_with_qualifier);
    $other_parts = explode('.', $other_long_dotted_number_with_qualifier);

    if (count($parts) > count($other_parts)) {
        $other_parts[] = '0';
    } elseif (count($other_parts) > count($parts)) {
        $parts[] = '0';
    }

    foreach ($parts as $i => $part) {
        if ($other_parts[$i] != $part) {
            return $i; // 0|1|2|3
        }
    }

    return null;
}

function strip_download_description($d)
{
    return static_evaluate_tempcode(comcode_to_tempcode(preg_replace('#A new version, [\.\d\w]+ is available\. #', '', preg_replace('# There may have been other upgrades since .* - see .+\.#', '', $d))));
}

function display_version_upgrade_path($higher_version)
{
    $version_dotted = get_param_string('version');

    static $i = 0;
    $i++;

    $note = '';

    if (is_release_discontinued($higher_version['version_dotted'])) {
        list(, , , , $general_number) = get_version_components__from_dotted($higher_version['version_dotted']);
        $note .= ' &ndash; <em>Note that the ' . get_version_branch($general_number) . ' version line is no longer supported</em>';
    }

    $tooltip = comcode_to_tempcode('[title="2"]Inbetween versions[/title]' . $higher_version['download_description']);

    $upgrade_url = static_evaluate_tempcode(build_url(array('page' => 'news', 'type' => 'view', 'id' => $higher_version['news_id'], 'from_version' => $version_dotted, 'wide_high' => 1), 'site'));

    echo '<p class="version vertical_alignment">';

    // Version number
    echo '<span class="version_number">' . escape_html($higher_version['version_pretty']) . '</span> ';

    // Output upgrader link
    $upgrade_script = 'upgrader.php';
    if (isset($higher_version['news_id'])) {
        $upgrade_script .= '?news_id=' . strval($higher_version['news_id']) . '&from_version=' . urlencode($version_dotted);
    }
    echo "
        <span class=\"version_button\" id=\"link_pos_" . strval($i) . "\"></span>
        <script>// <![CDATA[
            var div=document.getElementById('link_pos_" . strval($i) . "');
            var upgrader_link=get_base_url()+'/" . $upgrade_script . "';
            var h='<form style=\"display: inline\" action=\"'+upgrader_link+'\" target=\"_blank\" method=\"post\"><input class=\"menu__adminzone__tools__upgrade button_micro\" type=\"submit\" title=\"Upgrade to " . escape_html($higher_version['version_pretty']) . "\" value=\"Launch upgrader\" /><\/form>';
            div.innerHTML=h;
        //]]></script>
        ";

    // Version News link
    echo '<span class="version_news_link">[ <a onclick="window.open(this.href,null,\'status=yes,toolbar=no,location=no,menubar=no,resizable=yes,scrollbars=yes,width=976,height=600\'); return false;" target="_blank" title="' . escape_html($higher_version['version_pretty']) . ' news post (this link will open in a new window)" href="' . escape_html($upgrade_url) . '">view news post</a> ]</span>';

    // Details
    echo '<span class="version_details">(' . escape_html($higher_version['version_pretty']) . ', released ' . display_time_period(time() - $higher_version['add_date']) . ' ago)</span>';
    echo ' ';
    echo '<span class="version_note">' . $note . '</span>';
    echo ' ';
    echo '<img class="version_help_icon" onmouseout="if (typeof window.deactivateTooltip!=\'undefined\') deactivateTooltip(this,event);" onmousemove="if (typeof window.activateTooltip!=\'undefined\') repositionTooltip(this,event);" onmouseover="if (this.parentNode.title!=undefined) this.parentNode.title=\'\'; if (typeof window.activateTooltip!=\'undefined\') activateTooltip(this,event,\'' . escape_html(str_replace("\n", '\n', addslashes($tooltip->evaluate()))) . '\',\'600px\',null,null,false,true);" alt="Help" src="' . escape_html(find_theme_image('icons/16x16/help')) . '" />';

    echo '</p>';

    // Noscript version
    echo "
    <noscript>
        <form style=\"display: inline\" action=\"../" . $upgrade_script . "\" target=\"_blank\" method=\"post\">
            <input class=\"menu__adminzone__tools__upgrade button_screen_item\" type=\"submit\" title=\"Upgrade to " . escape_html($higher_version['version_pretty']) . "\" value=\"Launch upgrader\" />
        </form>
    </noscript>
    ";
}
