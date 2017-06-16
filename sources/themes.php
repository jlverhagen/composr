<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2016

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    core
 */

/**
 * Standard code module initialisation function.
 *
 * @ignore
 */
function init__themes()
{
    global $THEME_IMAGES_CACHE, $RECORD_THEME_IMAGES_CACHE, $RECORDED_THEME_IMAGES, $THEME_IMAGES_LOAD_INTENSITY;
    $THEME_IMAGES_CACHE = array();
    $RECORD_THEME_IMAGES_CACHE = false;
    $RECORDED_THEME_IMAGES = array();

    if (!defined('THEME_IMAGE_PLACE_SITE')) {
        define('THEME_IMAGE_PLACE_SITE', 0);
        define('THEME_IMAGE_PLACE_FORUM', 1);
        define('THEME_IMAGES_LOAD_INTENSITY__NONE', 0);
        define('THEME_IMAGES_LOAD_INTENSITY__SMART_CACHE', 1);
        define('THEME_IMAGES_LOAD_INTENSITY__ALL', 2);
    }
    $THEME_IMAGES_LOAD_INTENSITY = array(
        THEME_IMAGE_PLACE_SITE => THEME_IMAGES_LOAD_INTENSITY__NONE,
        THEME_IMAGE_PLACE_FORUM => THEME_IMAGES_LOAD_INTENSITY__NONE
    );
}

/**
 * Find the URL to the theme image of the specified ID. It searches various priorities, including language and theme overrides.
 *
 * @param  ID_TEXT $id The theme image ID
 * @param  boolean $silent_fail Whether to silently fail (i.e. not give out an error message when a theme image cannot be found)
 * @param  boolean $leave_local Whether to leave URLs as relative local URLs
 * @param  ?ID_TEXT $theme The theme to search in (null: users current theme)
 * @param  ?LANGUAGE_NAME $lang The language to search for (null: users current language)
 * @param  ?object $db The database to use (null: site database)
 * @param  boolean $pure_only Whether to only search the default 'images' filesystem
 * @return URLPATH The URL found (blank: not found)
 */
function find_theme_image($id, $silent_fail = false, $leave_local = false, $theme = null, $lang = null, $db = null, $pure_only = false)
{
    global $THEME_IMAGES_CACHE, $USER_LANG_CACHED, $THEME_IMAGES_LOAD_INTENSITY, $RECORD_THEME_IMAGES_CACHE, $SMART_CACHE, $SITE_INFO;

    // Special case: bad function parameters...

    if (!$GLOBALS['DEV_MODE']) {
        if (empty($id)) {
            return ''; // $id should be non-empty
        }
    }

    // Special case: theme wizard...

    if ((isset($_GET['keep_theme_seed'])) && (get_param_string('keep_theme_seed', null) !== null) && (addon_installed('themewizard')) && (function_exists('has_privilege')) && (has_privilege(get_member(), 'view_profiling_modes'))) {
        require_code('themewizard');
        $test = find_theme_image_themewizard_preview($id, $silent_fail);
        if ($test !== null) {
            return $test;
        }
    }

    // Work out basic parameters...

    if ($db === null) {
        $db = $GLOBALS['SITE_DB'];
        $db_place = THEME_IMAGE_PLACE_SITE;
    } else {
        $db_place = ($GLOBALS['SITE_DB'] === $db) ? THEME_IMAGE_PLACE_SITE : THEME_IMAGE_PLACE_FORUM;
    }

    $true_theme = isset($GLOBALS['FORUM_DRIVER']) ? $GLOBALS['FORUM_DRIVER']->get_theme() : 'default';
    if ($theme === null) {
        $theme = $true_theme;
    }

    $true_lang = ($USER_LANG_CACHED === null) ? user_lang() : $USER_LANG_CACHED;
    if ($lang === null) {
        $lang = $true_lang;
    }

    $truism = ($theme === $true_theme) && ($lang === $true_lang);

    $url_path = null; // null means keep searching, '' means we know it does not exist

    $force_recache = false;

    // Special case: Separate lookup (cannot go through $THEME_IMAGES_CACHE but can rely on theme_images table)...

    // Can we get it from the database / internal caching?...

    if (!$pure_only) {
        if ($truism) {
            // Are we looking for something the the internal cache does not know about yet? If so then we better load further
            $has = isset($THEME_IMAGES_CACHE[$db_place][$id]);
            if (
                (!$has) &&
                ($THEME_IMAGES_LOAD_INTENSITY[$db_place] !== THEME_IMAGES_LOAD_INTENSITY__ALL)
            ) {
                load_theme_image_cache($db, $db_place, $true_theme, $true_lang);

                // Hmm, still failing from smart cache level? If so then we better load further
                if (
                    (!isset($THEME_IMAGES_CACHE[$db_place][$id])) &&
                    ($THEME_IMAGES_LOAD_INTENSITY[$db_place] === THEME_IMAGES_LOAD_INTENSITY__SMART_CACHE)
                ) {
                    load_theme_image_cache($db, $db_place, $true_theme, $true_lang);
                }
            }

            if ($has || isset($THEME_IMAGES_CACHE[$db_place][$id])) {
                $url_path = $THEME_IMAGES_CACHE[$db_place][$id];

                // Check it is still here
                static $checked = array();
                if (!isset($checked[$url_path])) {
                    if ((url_is_local($url_path)) && (support_smart_decaching())) {
                        if (substr($url_path, 0, 22) === 'themes/default/images/') {
                            if ((!isset($SITE_INFO['no_disk_sanity_checks'])) || ($SITE_INFO['no_disk_sanity_checks'] === '0')) {
                                $missing = !is_file(get_file_base() . '/' . rawurldecode($url_path));
                            } else {
                                $missing = false;
                            }
                        } else {
                            $missing = !is_file(get_custom_file_base() . '/' . rawurldecode($url_path)) && !is_file(get_file_base() . '/' . rawurldecode($url_path));
                        }
                        if ($missing) {
                            $url_path = '';

                            // Dynamic fixup possible?
                            if ($theme != 'default') {
                                $url_path = $db->query_select_value_if_there('theme_images', 'path', array('id' => $id, 'theme' => 'default', 'lang' => $lang));
                                if ($url_path !== null) {
                                    $db->query_update('theme_images', array('path' => $url_path), array('id' => $id, 'theme' => $theme, 'lang' => $lang), '', 1);
                                } else {
                                    $db->query_delete('theme_images', array('id' => $id, 'theme' => $theme, 'lang' => $lang), '', 1);
                                }
                            }

                            $force_recache = true;
                        }
                    }

                    $checked[$url_path] = true;
                }
            }
        } else {
            // Can't rely on caching because the cache only runs if $truism
            $url_path = $db->query_select_value_if_there('theme_images', 'path', array('id' => $id, 'theme' => $theme, 'lang' => $lang));
        }
    }

    // Disk search then?...

    if ($url_path === null) {
        // Do search
        $priorities = array();
        if (!$pure_only) {
            $priorities[] = array($theme, $lang, 'images_custom');
            $priorities[] = array($theme, '', 'images_custom');
            if ($lang !== fallback_lang()) {
                $priorities[] = array($theme, fallback_lang(), 'images_custom');
            }
        }
        $priorities[] = array($theme, $lang, 'images');
        $priorities[] = array($theme, '', 'images');
        if ($lang !== fallback_lang()) {
            $priorities[] = array($theme, fallback_lang(), 'images');
        }
        if ($theme !== 'default') {
            if (!$pure_only) {
                $priorities[] = array('default', $lang, 'images_custom');
                $priorities[] = array('default', '', 'images_custom');
                if ($lang !== fallback_lang()) {
                    $priorities[] = array('default', fallback_lang(), 'images_custom');
                }
            }
            $priorities[] = array('default', $lang, 'images');
            $priorities[] = array('default', '', 'images');
            if ($lang !== fallback_lang()) {
                $priorities[] = array('default', fallback_lang(), 'images');
            }
        }

        foreach ($priorities as $priority) {
            $url_path = _search_img_file($priority[0], $priority[1], $id, $priority[2]);
            if ($url_path !== null) {
                break;
            }
        }

        // Missing?
        if ($url_path === null) {
            $url_path = ''; // This means search happened and it's missing
        }

        // Store result of search in database
        if ((!$GLOBALS['SEMI_DEV_MODE']) || ($url_path !== '')) { // We don't cache failure on dev-mode as we may add it later while writing code and don't want to have to keep doing cache flushes
            if (!$db->is_forum_db()) { // If guard is here because a MSN site can't code assumptions about the file system of the central site into that site's database, we rely on that site to maintain its own theme_images table for performance
                push_query_limiting(false);
                $db->query_insert('theme_images', array('id' => $id, 'theme' => $theme, 'lang' => $lang, 'path' => $url_path), false, true); // Allow for race conditions
                pop_query_limiting();
            }
        }

        // Update internal caching?
        if ((!$pure_only) && ($truism)) {
            $THEME_IMAGES_CACHE[$db_place][$id] = $url_path;
        }
    }

    // Final stuff, then return...

    // Smart cache learning if we ended up having to bypass smart cache
    if ((($THEME_IMAGES_LOAD_INTENSITY[$db_place] === THEME_IMAGES_LOAD_INTENSITY__ALL) || ($force_recache)) && (!$pure_only)) {
        $SMART_CACHE->append('theme_images_' . $theme . '_' . $lang . '_' . strval($db_place), $id, $url_path);
    }

    if ($url_path !== '') {
        // Turn to full URL (the default behaviour)?
        if (!$leave_local) {
            if (url_is_local($url_path)) {
                if ($db->is_forum_db()) {
                    $url_path = get_forum_base_url() . '/' . $url_path;
                } else {
                    if ((substr($url_path, 0, 22) === 'themes/default/images/') || (!is_file(get_custom_file_base() . '/' . rawurldecode($url_path)))) {
                        $url_path = get_base_url() . '/' . $url_path;
                    } else {
                        $url_path = get_custom_base_url() . '/' . $url_path;
                    }
                }
            }

            // Apply CDN
            static $cdn = null;
            if ($cdn === null) {
                $cdn = get_option('cdn');
            }
            if ($cdn !== '') {
                $url_path = cdn_filter($url_path);
            }
        }

        // Take note for view mode tools
        if ($RECORD_THEME_IMAGES_CACHE) {
            global $RECORDED_THEME_IMAGES;
            if (!is_on_multi_site_network()) {
                $RECORDED_THEME_IMAGES[serialize(array($id, $theme, $lang))] = true;
            }
        }
    } else {
        // Missing
        if (!$silent_fail) {
            require_code('site');
            trigger_error(do_lang('NO_SUCH_THEME_IMAGE', escape_html($id)), E_USER_NOTICE);
        }
    }

    // Done
    return $url_path;
}

/**
 * Load up theme image cache.
 *
 * @param  object $db The database to load from (used for theme images running across multi-site-networks)
 * @param  integer $db_place The internal name of the database to load from (used for theme images running across multi-site-networks)
 * @param  ID_TEXT $true_theme Theme0
 * @param  LANGUAGE_NAME $true_lang Language
 */
function load_theme_image_cache($db, $db_place, $true_theme, $true_lang)
{
    global $THEME_IMAGES_CACHE, $THEME_IMAGES_LOAD_INTENSITY, $SMART_CACHE;

    switch ($THEME_IMAGES_LOAD_INTENSITY[$db_place]) {
        case THEME_IMAGES_LOAD_INTENSITY__NONE:
            $THEME_IMAGES_CACHE[$db_place] = $SMART_CACHE->get('theme_images_' . $true_theme . '_' . $true_lang . '_' . strval($db_place));
            if ($THEME_IMAGES_CACHE[$db_place] === null) {
                $THEME_IMAGES_CACHE[$db_place] = array();
            }

            $THEME_IMAGES_LOAD_INTENSITY[$db_place] = THEME_IMAGES_LOAD_INTENSITY__SMART_CACHE;

            break;

        case THEME_IMAGES_LOAD_INTENSITY__SMART_CACHE:
            $theme_images = $db->query_select('theme_images', array('id', 'path'), array('theme' => $true_theme, 'lang' => $true_lang));
            $THEME_IMAGES_CACHE[$db_place] = collapse_2d_complexity('id', 'path', $theme_images);

            $THEME_IMAGES_LOAD_INTENSITY[$db_place] = THEME_IMAGES_LOAD_INTENSITY__ALL;

            break;
    }
}

/**
 * Filter a path so it runs through a CDN.
 *
 * @param  URLPATH $url_path Input URL
 * @return URLPATH Output URL
 */
function cdn_filter($url_path)
{
    static $cdn = null;
    if ($cdn === null) {
        $cdn = get_option('cdn');
    }
    static $km = null;
    if ($km === null) {
        $km = get_param_integer('keep_minify', null);
    }

    if (($cdn !== '') && ($km !== 0)) {
        if ($cdn === '<autodetect>') {
            $cdn = get_value('cdn');
            if ($cdn === null) {
                require_code('themes2');
                $cdn = autoprobe_cdns();
            }
        }
        if ($cdn === '') {
            return $url_path;
        }

        static $cdn_consistency_check = array();

        if (isset($cdn_consistency_check[$url_path])) {
            return $cdn_consistency_check[$url_path];
        }

        static $cdn_parts = null;
        if ($cdn_parts === null) {
            $cdn_parts = explode(',', $cdn);
        }

        if (count($cdn_parts) === 1) {
            $cdn_part = $cdn_parts[0];
        } else {
            $sum_asc = 0;
            $basename = basename($url_path);
            $url_path_len = strlen($basename);
            for ($i = 0; $i < $url_path_len; $i++) {
                $sum_asc += ord($basename[$i]);
            }

            $cdn_part = $cdn_parts[$sum_asc % count($cdn_parts)]; // To make a consistent but fairly even distribution we do some modular arithmetic against the total of the ascii values
        }

        static $normal_suffix = null;
        if ($normal_suffix === null) {
            $normal_suffix = '#(^https?://)' . str_replace('#', '#', preg_quote(get_domain())) . '(/)#';
        }
        $out = preg_replace($normal_suffix, '${1}' . $cdn_part . '${2}', $url_path);

        $cdn_consistency_check[$url_path] = $out;

        return $out;
    }

    return $url_path;
}

/**
 * Search for a specified image file within a theme for a specified language.
 *
 * @param  ID_TEXT $theme The theme
 * @param  ?LANGUAGE_NAME $lang The language (null: try generally, under no specific language)
 * @param  ID_TEXT $id The theme image ID
 * @param  ID_TEXT $dir Directory to search
 * @return ?URLPATH The URL path to the image (null: was not found)
 * @ignore
 */
function _search_img_file($theme, $lang, $id, $dir = 'images')
{
    $places = array(get_custom_file_base(), get_file_base());
    $extensions = array('png', 'jpg', 'jpe', 'jpeg', 'gif', 'ico', 'svg', 'webp');

    foreach ($places as $_base) {
        $base = $_base . '/themes/';

        foreach ($extensions as $extension) {
            $file_path = $base . $theme . '/';
            if ($dir !== '') {
                $file_path .= $dir . '/';
            }
            if (!empty($lang)) {
                $file_path .= $lang . '/';
            }
            $file_path .= $id . '.' . $extension;

            if (is_file($file_path)) { // Good, now return URL
                $url_path = 'themes/' . rawurlencode($theme) . '/' . $dir . '/';
                if (!empty($lang)) {
                    $url_path .= $lang . '/';
                }
                $url_path .= $id . '.' . $extension;
                return $url_path;
            }
        }
    }
    return null;
}
