<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2016

 See text/EN/licence.txt for full licencing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    cns_tapatalk
 */

/*EXTRA FUNCTIONS: json_encode*/

if (!defined('IN_MOBIQUO') && !function_exists('get_base_url')) {
    exit('May not call this directly');
}

if (!function_exists('apache_request_headers')) {
    /**
     * Get HTTP headers.
     *
     * @return array List of headers
     */
    function apache_request_headers()
    {
        $headers = array();
        foreach ($_SERVER as $h => $v) {
            $matches = array();
            if (preg_match('#^HTTP\_(.+)$#', $h, $matches) != 0) {
                $headers[$matches[1]] = $v;
            }
        }
        return $headers;
    }
}

/**
 * Bootstrap Composr.
 */
function initialise_composr()
{
    global $FILE_BASE, $RELATIVE_PATH;
    $FILE_BASE = dirname(dirname(dirname(__FILE__)));
    $RELATIVE_PATH = 'data_custom';

    global $FORCE_INVISIBLE_GUEST;
    $FORCE_INVISIBLE_GUEST = false;
    global $EXTERNAL_CALL;
    $EXTERNAL_CALL = true;

    if (!is_file($FILE_BASE . '/sources/global.php')) {
        exit(json_encode(array('status' => 'Required system file is missing')));
    }
    require_once($FILE_BASE . '/sources/global.php');

    error_reporting(E_ALL);
    ini_set('ocproducts.type_strictness', '0'); // Much Tapatalk client code will not be compatible with this

    cns_require_all_forum_stuff();

    require_lang('tapatalk');

    require_code('developer_tools');

    require_code('character_sets');
    convert_data_encodings(true);

    $GLOBALS['LAX_COMCODE'] = true;
}

/**
 * Find pagination positions.
 *
 * @param  array $params Parameters
 * @param  integer $start_param_num Start parameter index
 * @param  integer $end_param_num End parameter index
 * @param  integer $default_max Default maximum parameter
 * @return array A pair: start position, max results
 */
function get_pagination_positions($params, $start_param_num, $end_param_num, $default_max)
{
    $start = isset($params[$start_param_num]) ? intval($params[$start_param_num]) : 0;
    if (isset($params[$end_param_num])) {
        $max = intval($params[$end_param_num]) - $start + 1;
    } else {
        $max = $default_max;
    }

    if ($max < 0 || $max > 50) {
        warn_exit('Out-of-range');
    }

    return array($start, $max);
}

/**
 * Find pagination positions, for different pattern of parameters.
 *
 * @param  array $params Parameters
 * @param  integer $page_param_num Page parameter index
 * @param  integer $max_param_num Max parameter index
 * @param  integer $default_max Default maximum parameter
 * @return array A pair: start position, max results
 */
function get_pagination_positions__by_page($params, $page_param_num, $max_param_num, $default_max)
{
    if (count($params) > 1) {
        $page = $params[$page_param_num];
        $max = $params[$max_param_num];
        $start = ($page - 1) * $max;
    } else {
        $start = 0;
        $max = $default_max;
    }

    if ($max < 0 || $max > 50) {
        warn_exit('Out-of-range');
    }

    return array($start, $max);
}

/**
 * Get a mapping of emoticons, so we can do code conversion.
 *
 * @param  string $set Which ones of the mapping to get
 * @set missing_from_composr perfect_matches all
 * @return array Emoticons mapping
 */
function get_tapatalk_to_composr_emoticon_map($set)
{
    $GLOBALS['FORUM_DRIVER']->find_emoticons(); // Fill cache

    $ret = array();

    if ($set != 'perfect_matches') {
        $ret += array(
            ':eek:' => 'O_o',
            ':confused:' => ':|',
            ':thumbup:' => ':thumbs:',
            ':thumbdown:' => ':shake:',
            ':mad:' => ':@',
            ':p' => ':P',
        );
    }

    if ($set != 'missing_from_composr') {
        $ret += array(
            ':D' => ':D',
            ':(' => ':(',
            ':)' => ':)',
            ';)' => ';)',
            ':cool:' => ':cool:',
            ':o' => ':o',
            ':rolleyes:' => ':rolleyes:',
        );
    }

    // Check really are in our code (as this is user-editable)
    foreach ($ret as $tapatalk_code => $composr_code) {
        if (!isset($GLOBALS['FORUM_DRIVER']->EMOTICON_CACHE[$composr_code])) {
            unset($ret[$tapatalk_code]);
        }
    }

    return $ret;
}

/**
 * Add attachments to some Comcode. Also handle emoticon mapping. Also handle wordfilter.
 *
 * @param  string $comcode Comcode
 * @param  array $attachment_ids Attachment IDs
 * @return string Comcode
 */
function add_attachments_from_comcode($comcode, $attachment_ids)
{
    // Map emoticons
    $emoticon_map = get_tapatalk_to_composr_emoticon_map('missing_from_composr');
    $comcode = str_replace(array_keys($emoticon_map), array_values($emoticon_map), $comcode);

    // Wordfilter
    require_code('wordfilter');
    $comcode = check_wordfilter($comcode);

    // Map BBCode that we do not support, to Comcode
    $comcode = str_ireplace(array('[spoiler', '[/spoiler'), array('[hide', '[/hide'), $comcode);
    $comcode = preg_replace('#(\[\w+=)([^"\[\]]*)(\])#', '\1"\2"]', $comcode); // So comcode_to_clean_text works better

    // Add attachments
    $comcode .= "\n\n[media_set]";
    foreach ($attachment_ids as $attachment_id) {
        $comcode .= "\n\n" . '[attachment type="inline"]' . strval($attachment_id) . '[/attachment]';
    }
    $comcode .= "\n\n[/media_set]";

    return $comcode;
}

/**
 * Take attachments out of some Comcode.
 *
 * @param  string $comcode Comcode
 * @param  boolean $inline_image_substitutions Whether to substitute for inline images, if possible
 * @return string Comcode
 */
function strip_attachments_from_comcode($comcode, $inline_image_substitutions = false)
{
    if (strpos($comcode, '[attachment') === false) {
        return $comcode;
    }

    if ($inline_image_substitutions) {
        $matches = array();
        $num_matches = preg_match_all('#\[attachment(_safe)?( description="([^"]*)")?[^\]]*\](\d+)\[/attachment(_safe)?\]#', $comcode, $matches);
        for ($i = 0; $i < $num_matches; $i++) {
            $original_filename = $GLOBALS['SITE_DB']->query_select_value_if_there('attachments', 'a_original_filename', array('id' => intval($matches[4][$i])));
            require_code('images');
            if ((!is_null($original_filename)) && (is_image($original_filename, false))) {
                $comcode = str_replace($matches[0][$i], '[img="' . $matches[3][$i] . '"]' . find_script('attachment') . '?id=' . urlencode($matches[4][$i]) . '[/img]', $comcode);
            }
        }
    }

    return preg_replace('#\n*\[attachment(_safe)?( [^\[\]]*)?\].*\[/attachment(_safe)?\]#U', '', $comcode);
}

/**
 * preg_replace callback for fixing up tag case.
 *
 * @param  array $matches Matches
 * @return string Replacement
 */
function _tag_case_fix($matches)
{
    $tag = strtolower($matches[1]);
    $attributes = $matches[2];
    $inner = $matches[3];
    return '[' . $tag . '' . $attributes . ']' . $inner . '[/' . $tag . ']';
}

/**
 * Strip out some Comcode so we can display on mobile.
 *
 * @param  string $data Comcode
 * @return string Rawer text
 */
function tapatalk_strip_comcode($data)
{
    // We need to simplify messy HTML as much as possible
    require_code('comcode_from_html');
    $data = force_clean_comcode($data);

    // Remove non-image attachment code (will be separately delivered)
    $data = strip_attachments_from_comcode($data, true);

    // Shortcuts
    $data = html_entity_decode($data, ENT_QUOTES, get_charset());
    $shortcuts = array('(EUR-)' => '&euro;', '{f.}' => '&fnof;', '-|-' => '&dagger;', '=|=' => '&Dagger;', '{%o}' => '&permil;', '{~S}' => '&Scaron;', '{~Z}' => '&#x17D;', '(TM)' => '&trade;', '{~s}' => '&scaron;', '{~z}' => '&#x17E;', '{.Y.}' => '&Yuml;', '(c)' => '&copy;', '(r)' => '&reg;', '---' => '&mdash;', '--' => '&ndash;', '...' => '&hellip;', '-->' => '&rarr;', '<--' => '&larr;');
    $data = strtr($data, array_flip($shortcuts));

    // Emoticons
    // FUDGE: Disable emoticons Tapatalk actually has inbuilt. Tapatalk will sub in those that it supports
    $emoticon_map = get_tapatalk_to_composr_emoticon_map('perfect_matches');
    $bak = $GLOBALS['FORUM_DRIVER']->EMOTICON_CACHE;
    foreach ($emoticon_map as $tapatalk_code => $composr_code) {
        unset($GLOBALS['FORUM_DRIVER']->EMOTICON_CACHE[$composr_code]);
        // Actually, Tapatalk emoticon rendering seems erratic in text mode (iOS), but it's best we TRY, because the centred on-own-line image emoticons look very poor
    }
    // Map Composr ones back to Tapatalk ones
    $emoticon_map = get_tapatalk_to_composr_emoticon_map('missing_from_composr');
    $data = str_replace(array_values($emoticon_map), array_keys($emoticon_map), $data);
    $GLOBALS['FORUM_DRIVER']->EMOTICON_CACHE = array(); // DISABLE ALL EMOTICON TO IMAGE RENDERING FOR NOW ACTUALLY, LOOKS AWFUL ON WINDOWS MOBILE VERSION
    // Apply remaining ones in Composr as BBCode img tags
    $_smilies = $GLOBALS['FORUM_DRIVER']->find_emoticons(); // Sorted in descending length order
    // Pre-check, optimisation
    $smilies = array();
    foreach ($_smilies as $code => $imgcode) {
        if (strpos($data, $code) !== false) {
            $smilies[$code] = $imgcode;
        }
    }
    if (count($smilies) != 0) {
        $len = strlen($data);
        for ($i = 0; $i < $len; ++$i) // Has to go through in byte order so double application cannot happen (i.e. smiley contains [all or portion of] smiley code somehow)
        {
            $char = $data[$i];

            if ($char == '"') // This can cause severe HTML corruption so is a disallowed character
            {
                $i++;
                continue;
            }
            foreach ($smilies as $code => $imgcode) {
                $code_len = strlen($code);
                if (($char == $code[0]) && (substr($data, $i, $code_len) == $code)) {
                    $eval = '[img]' . find_theme_image($imgcode[1]) . '[/img]';
                    $before = substr($data, 0, $i);
                    $after = substr($data, $i + $code_len);
                    $data = $before . $eval . $after;
                    $len = strlen($data);
                    $i += strlen($eval) - 1;
                    break;
                }
            }
        }
    }
    $GLOBALS['FORUM_DRIVER']->EMOTICON_CACHE = $bak;

    // Fix up bad BBCode, to BBCode Tapatalk may support
    do {
        $old_data = $data;
        $data = preg_replace_callback('#\[([A-Z]+)([^\]]*)\](.*)\[/\1\]#Us', '_tag_case_fix', $data);
    } while ($data != $old_data);
    $data = preg_replace('#(\[\w+=)([^" ]*)(\])#Us', '$1"$2"$3', $data);

    // No clickable images allowed, Tapatalk will make simple image tag expandable
    $data = preg_replace('#\[url(\s[^\[\]*]|=[^\[\]*])?\]\s*(\[img(\s[^\[\]*])?\].*\[/img\])\s*\[/url\]#Us', '$2', $data);

    // Take out any filename alt-text
    $data = preg_replace('#\[img( param)?="(C:\\\\fakepath\\\\|IMG|PC|DCP|SBCS|DSC|PIC)[^"]*"\](.*)\[/img\]#Us', '[img]$3[/img]', $data);

    // Rewrite certain Comcode tags to Tapatalk style BBCode
    $data = preg_replace('#\[hide( param)?="([^"]*)"\](.*)\[/hide\]#Us', '$2:' . "\n" . '[spoiler]$3[/spoiler]', $data);
    $data = preg_replace('#\[hide](.*)\[/hide\]#Us', '[spoiler]$1[/spoiler]', $data);
    $data = preg_replace('#\[quote( param)?="([^"]*)"\](.*)\[/quote\]#Us', '[quote name="$2"]$3[/quote]', $data);
    $data = preg_replace('#\[img( param)?="([^"]+)"\](.*)\[/img\]#Us', '$2:' . "\n" . '[img]$3[/img]', $data);
    $data = preg_replace('#\[img[^\]]*\](.*)\[/img\]#Us', '[img]$1[/img]', $data);
    $data = preg_replace('#\[url="([^"]*)"\](https?://.*)\[/url\]#Us', '[url="$2"]$1[/url]', $data);

    // Nested quotes not allowed
    $quote_open = '\[quote[^\]]*\]';
    $quote_close = '\[/quote\]';
    $no_quote_seq = '((?!' . $quote_open . ').)*';
    do {
        $old_data = $data;
        $data = preg_replace('#' . '(' . $quote_open . $no_quote_seq . ')' . $quote_open . $no_quote_seq . $quote_close . '(' . $no_quote_seq . $quote_close . ')' . '#Us', '$1$4', $data);
    } while ($data != $old_data);

    // Strip most Comcode
    $protected_tags = array(
        'url',
        'img',
        'quote',
        'spoiler',
        'code',
        'u',
        'b',
        'i',
        'color', // Not officially supported in text mode, but hopefully clients will strip it. We substitute to HTML for HTML mode
    );
    require_code('mail');
    $data = strip_comcode($data, false, $protected_tags);

    // Parse URLs
    $data = preg_replace('#([^"\]]|^)(https?://.*)( |\n|\[|\)|"|>|<|\.\n|,|$)#U', '$1[url]$2[/url]$3', $data);
    $data = preg_replace('#keep_session=\w*#', 'filtered=1', $data);

    // Trim
    $data = trim($data);

    return $data;
}
