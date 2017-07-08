<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2017

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

/*EXTRA FUNCTIONS: shell_exec*/

/**
 * Standard code module initialisation function.
 *
 * @ignore
 */
function init__images()
{
    if (!defined('IMAGE_CRITERIA_NONE')) {
        define('IMAGE_CRITERIA_NONE', 0);
        define('IMAGE_CRITERIA_GD_READ', 1);
        define('IMAGE_CRITERIA_GD_WRITE', 2); // NB: We can assume IMAGE_CRITERIA_GD_READ is always true when IMAGE_CRITERIA_GD_WRITE is true, but not vice-versa
        define('IMAGE_CRITERIA_RASTER', 4);
        define('IMAGE_CRITERIA_VECTOR', 8); // Opposite of raster
        define('IMAGE_CRITERIA_WEBSAFE', 16); // NB: We will make a basic assumption that we are not going to try to use IMAGE_CRITERIA_GD_READ to make something IMAGE_CRITERIA_WEBSAFE
    }
}

/**
 * Find image dimensions. Better than PHP's built in getimagesize as it gets the correct size for animated gifs.
 *
 * @param  string $path Either a filesystem path or an absolute URL
 * @return ~array The width and height (false: error)
 *
 * @ignore
 */
function cms_getimagesize($path)
{
    if (looks_like_url($path)) {
        $_path = convert_url_to_path($path);
    } else {
        $_path = $path;
    }

    if ($_path === null) {
        $data = http_get_contents($path, array('trigger_error' => false, 'byte_limit' => 1024 * 1024 * 20/*reasonable limit*/));
    } else {
        if (function_exists('getimagesize')) {
            $details = @getimagesize($_path);
            if ($details !== false) {
                return array($details[0], $details[1]);
            }
            return false;
        }

        // Should never actually get here
        $data = file_get_contents($_path);
    }

    if (function_exists('getimagesizefromstring')) {
        if (get_file_extension($path) == 'gif') { // Workaround problem with animated gifs
            $header = unpack('@6/' . 'vwidth/' . 'vheight', $data);
            $sx = $header['width'];
            $sy = $header['height'];
            return array($sx, $sy);
        }

        $details = @getimagesizefromstring($data);
        if ($details !== false) {
            return array($details[0], $details[1]);
        }
    }

    return false;
}

/**
 * Get the maximum allowed image size, as set in the configuration.
 *
 * @param  boolean $consider_php_limits Whether to consider limitations in PHP's configuration
 * @return integer The maximum image size, in bytes
 */
function get_max_image_size($consider_php_limits = true)
{
    require_code('files');
    $a = php_return_bytes(ini_get('upload_max_filesize'));
    $b = php_return_bytes(ini_get('post_max_size'));
    $c = intval(get_option('max_download_size')) * 1024;
    if (has_privilege(get_member(), 'exceed_filesize_limit')) {
        $c = 0;
    }

    $possibilities = array();
    if ($consider_php_limits) {
        if ($a != 0) {
            $possibilities[] = $a;
        }
        if ($b != 0) {
            $possibilities[] = $b;
        }
    }
    if ($c != 0) {
        $possibilities[] = $c;
    }

    return (count($possibilities) == 0) ? (1024 * 1024 * 1024 * 1024) : min($possibilities);
}

/**
 * Get the Tempcode for an image thumbnail.
 *
 * @param  URLPATH $url The URL to the image thumbnail
 * @param  mixed $caption The caption for the thumbnail (string or Tempcode)
 * @param  boolean $js_tooltip Whether to use a JS tooltip. Forcibly set to true if you pass Tempcode
 * @param  boolean $is_thumbnail_already Whether already a thumbnail (if not, function will make one)
 * @param  ?integer $width Thumbnail width to use (null: default)
 * @param  ?integer $height Thumbnail height to use (null: default)
 * @param  boolean $only_make_smaller Whether to apply a 'never make the image bigger' rule for thumbnail creation (would affect very small images)
 * @return Tempcode The thumbnail
 */
function do_image_thumb($url, $caption, $js_tooltip = false, $is_thumbnail_already = true, $width = null, $height = null, $only_make_smaller = false)
{
    if (is_object($caption)) {
        $js_tooltip = true;
    }

    $url = preg_replace('#' . preg_quote(get_custom_base_url() . '/', '#') . '#', '', $url);

    $box_size = (($width === null) && ($height === null));

    if ($width === null) {
        $width = intval(get_option('thumb_width'));
    }
    if ($height === null) {
        $height = intval(get_option('thumb_width'));
    }

    if (is_image($url, IMAGE_CRITERIA_VECTOR)) {
        $is_thumbnail_already = true;
    }

    if (!$is_thumbnail_already) {
        $new_name = strval($width) . '_' . strval($height) . '_';
        if ($only_make_smaller) {
            $new_name .= 'only_smaller_';
        }
        $new_name .= url_to_filename($url);

        $thumb_path = get_custom_file_base() . '/uploads/auto_thumbs/' . $new_name;

        if (!file_exists($thumb_path)) {
            $url = convert_image($url, $thumb_path, $box_size ? null : $width, $box_size ? null : $height, $box_size ? $width : null, false, null, false, $only_make_smaller);
        } else {
            $url = get_custom_base_url() . '/uploads/auto_thumbs/' . rawurlencode($new_name);
        }
    }

    if (url_is_local($url)) {
        $url = get_custom_base_url() . '/' . $url;
    }

    if ((!is_object($caption)) && ($caption == '')) {
        $caption = do_lang('THUMBNAIL');
        $js_tooltip = false;
    }
    return do_template('IMG_THUMB', array('_GUID' => 'f1c130b7c3b2922fe273596563cb377c', 'JS_TOOLTIP' => $js_tooltip, 'CAPTION' => $caption, 'URL' => $url));
}

/**
 * Take some image/thumbnail info, and if needed make and caches a thumbnail, and return a thumb url whatever the situation.
 *
 * @param  URLPATH $full_url The full URL to the image which will-be/is thumbnailed
 * @param  URLPATH $thumb_url The URL to the thumbnail (blank: no thumbnail yet)
 * @param  ID_TEXT $thumb_dir The directory, relative to the Composr install's uploads directory, where the thumbnails are stored. MINUS "_thumbs"
 * @param  ID_TEXT $table The name of the table that is storing what we are doing the thumbnail for
 * @param  AUTO_LINK $id The ID of the table record that is storing what we are doing the thumbnail for
 * @param  ID_TEXT $thumb_field_name The name of the table field where thumbnails are saved
 * @param  ?integer $thumb_width The thumbnail width to use (null: default)
 * @param  boolean $only_make_smaller Whether to apply a 'never make the image bigger' rule for thumbnail creation (would affect very small images)
 * @return URLPATH The URL to the thumbnail
 */
function ensure_thumbnail($full_url, $thumb_url, $thumb_dir, $table, $id, $thumb_field_name = 'thumb_url', $thumb_width = null, $only_make_smaller = false)
{
    if ($full_url == $thumb_url) {
        // Special case
        return $thumb_url;
    }

    if ($thumb_width === null) {
        $thumb_width = intval(get_option('thumb_width'));
    }

    if ((!function_exists('imagetypes')) || ($full_url == '')) {
        if ((url_is_local($thumb_url)) && ($thumb_url != '')) {
            return get_custom_base_url() . '/' . $thumb_url;
        }
        return $thumb_url;
    }

    // Ensure existing path still exists
    if ($thumb_url != '') {
        if (url_is_local($thumb_url)) {
            $thumb_path = get_custom_file_base() . '/' . rawurldecode($thumb_url);
            if (!file_exists($thumb_path)) {
                $from = str_replace(' ', '%20', $full_url);
                if (url_is_local($from)) {
                    $from = get_custom_base_url() . '/' . $from;
                }

                if (is_image($from, IMAGE_CRITERIA_WEBSAFE, true)) {
                    $_thumb_url = convert_image($from, $thumb_path, null, null, intval($thumb_width), false);
                    if ($_thumb_url != $thumb_url) {
                        // Failed somehow, so do a full regeneration and resave
                        require_code('images2');
                        return _ensure_thumbnail($full_url, $thumb_url, $thumb_dir, $table, $id, $thumb_field_name, $thumb_width, $only_make_smaller);
                    }
                } else {
                    if (addon_installed('galleries')) {
                        require_code('galleries2');
                        create_video_thumb($full_url, $thumb_path);
                    }
                }
            }
            return get_custom_base_url() . '/' . $thumb_url;
        }
        return $thumb_url;
    }

    // Do a full regeneration and resave
    require_code('images2');
    return _ensure_thumbnail($full_url, $thumb_url, $thumb_dir, $table, $id, $thumb_field_name, $thumb_width, $only_make_smaller);
}

/**
 * Resize an image to the specified size, but retain the aspect ratio.
 *
 * @param  URLPATH $from The URL to the image to resize. May be either relative or absolute
 * @param  PATH $to The file path (including filename) to where the resized image will be saved. May be changed by reference if it cannot save an image there for some reason
 * @param  ?integer $width The maximum width we want our new image to be (null: don't factor this in)
 * @param  ?integer $height The maximum height we want our new image to be (null: don't factor this in)
 * @param  ?integer $box_width This is only considered if both $width and $height are null. If set, it will fit the image to a box of this dimension (suited for resizing both landscape and portraits fairly) (null: use width or height)
 * @param  boolean $exit_on_error Whether to exit Composr if an error occurs
 * @param  ?string $ext2 The file extension representing the file type to save with (null: same as our input file)
 * @param  boolean $using_path Whether $from was in fact a path, not a URL
 * @param  boolean $only_make_smaller Whether to apply a 'never make the image bigger' rule for thumbnail creation (would affect very small images)
 * @param  ?array $thumb_options This optional parameter allows us to specify cropping or padding for the image. See comments in the function. (null: no details passed)
 * @return URLPATH The thumbnail URL (blank: URL is outside of base URL)
 */
function convert_image($from, &$to, $width, $height, $box_width = null, $exit_on_error = true, $ext2 = null, $using_path = false, $only_make_smaller = true, $thumb_options = null)
{
    require_code('images2');
    cms_profile_start_for('convert_image');
    $ret = _convert_image($from, $to, $width, $height, $box_width, $exit_on_error, $ext2, $using_path, $only_make_smaller, $thumb_options);
    cms_profile_end_for('convert_image', $from);
    return $ret;
}

/**
 * Find whether the image specified is actually an image, based on file extension.
 *
 * @param  string $name A URL or file path to the image
 * @param  integer $criteria A bitmask of IMAGE_CRITERIA_* constants that the image must match
 * @param  boolean $as_admin Whether there are admin privileges, to render dangerous media types (client-side risk only)
 * @param  boolean $mime_too Whether to check mime as well as file extension. A full URL must have been passed
 * @return boolean Whether the string pointed to a file appeared to be an image
 */
function is_image($name, $criteria, $as_admin = false, $mime_too = false)
{
    if (substr(basename($name), 0, 1) == '.') {
        return false; // Temporary file that some OS's make
    }

    $ext = get_file_extension($name);

    // Raster/vector check
    $is_vector = ($ext == 'svg');
    if (($criteria & IMAGE_CRITERIA_RASTER) != 0) {
        if ($is_vector) {
            return false;
        }
    }
    if (($criteria & IMAGE_CRITERIA_VECTOR) != 0) {
        if (!$is_vector) {
            return false;
        }
    }

    // GD-read check
    if (($criteria & IMAGE_CRITERIA_GD_READ) != 0) {
        $found = false;
        if (function_exists('imagetypes')) {
            $gd = imagetypes();
            if (($ext == 'gif') && (($gd & IMG_GIF) != 0) && (function_exists('imagecreatefromgif'))) {
                $found = true;
            }
            if ((($ext == 'jpg') || ($ext == 'jpeg') || ($ext == 'jpe')) && (($gd & IMG_JPEG) != 0)) {
                $found = true;
            }
            if (($ext == 'png') && (($gd & IMG_PNG) != 0)) {
                $found = true;
            }
            if (($ext == 'webp') && (function_exists('imagecreatefromwebp')/* https://bugs.php.net/bug.php?id=72596 */)) {
                $found = true;
            }
        } else {
            $found = (($ext == 'jpg') || ($ext == 'jpeg') || ($ext == 'png'));
        }
        if (!$found) {
            return false;
        }
    }

    // GD-write check
    if (($criteria & IMAGE_CRITERIA_GD_WRITE) != 0) {
        $found = false;
        if (function_exists('imagetypes')) {
            $gd = imagetypes();
            if (($ext == 'gif') && (($gd & IMG_GIF) != 0) && (function_exists('imagegif'))) {
                $found = true;
            }
            if ((($ext == 'jpg') || ($ext == 'jpeg') || ($ext == 'jpe')) && (($gd & IMG_JPEG) != 0)) {
                $found = true;
            }
            if (($ext == 'png') && (($gd & IMG_PNG) != 0)) {
                $found = true;
            }
            if (($ext == 'webp') && (function_exists('imagewebp')/* https://bugs.php.net/bug.php?id=72596 */)) {
                $found = true;
            }
        } else {
            $found = (($ext == 'jpg') || ($ext == 'jpeg') || ($ext == 'png'));
        }
        if (!$found) {
            return false;
        }
    }

    // Web-safe check
    if (($criteria & IMAGE_CRITERIA_WEBSAFE) != 0) {
        if (!in_array($ext, array('jpeg', 'jpe', 'jpg', 'gif', 'png', 'bmp', 'svg', 'ico'))) {
            return false;
        }
    }

    // Configured extension list check
    static $types = null;
    if ($types === null) {
        $types = explode(',', get_allowed_image_file_types());
    }
    $found = false;
    foreach ($types as $val) {
        if (strtolower($val) == $ext) {
            $found = true;
        }
    }
    if (!$found) {
        return false;
    }

    // Mime type recognition and security check
    require_code('mime_types');
    $ext_mime_type = get_mime_type($ext, $as_admin);
    if (substr($ext_mime_type, 0, 6) != 'image/') {
        return false;
    }

    // Mime type consistency check
    if (($mime_too) && (looks_like_url($name))) {
        $http_result = cms_http_request($name, array('trigger_error' => false, 'byte_limit' => 0));

        if ($ext_mime_type != $http_result->download_mime_type) {
            return false;
        }
    }

    return true;
}

/*
What follows are other media types, not images. However, we define them here to avoid having to explicitly load the full media rendering API.
*/

/**
 * Find whether the video specified is actually a 'video', based on file extension.
 *
 * @param  string $name A URL or file path to the video
 * @param  boolean $as_admin Whether there are admin privileges, to render dangerous media types (client-side risk only)
 * @param  boolean $must_be_true_video Whether it really must be an actual video/audio, not some other kind of rich media which we may render in a video spot
 * @return boolean Whether the string pointed to a file appeared to be a video
 */
function is_video($name, $as_admin, $must_be_true_video = false)
{
    $allow_audio = (get_option('allow_audio_videos') == '1');

    if (is_image($name, IMAGE_CRITERIA_WEBSAFE, true)) {
        return false;
    }

    if ($must_be_true_video) {
        require_code('mime_types');
        $ext = get_file_extension($name);
        if (($ext == 'rm') || ($ext == 'ram')) {
            return true; // These have audio mime types, but may be videos
        }
        $mime_type = get_mime_type($ext, $as_admin);
        return ((substr($mime_type, 0, 6) == 'video/') || (($allow_audio) && (substr($mime_type, 0, 6) == 'audio/')));
    }

    require_code('media_renderer');
    $acceptable_media = $allow_audio ? (MEDIA_TYPE_VIDEO | MEDIA_TYPE_AUDIO | MEDIA_TYPE_OTHER /* but not images */) : MEDIA_TYPE_VIDEO;
    $hooks = find_media_renderers($name, array(), $as_admin, null, $acceptable_media);
    return $hooks !== null;
}

/**
 * Find whether the video specified is actually a 'video', based on file extension.
 *
 * @param  string $name A URL or file path to the video
 * @param  boolean $as_admin Whether there are admin privileges, to render dangerous media types (client-side risk only)
 * @return boolean Whether the string pointed to a file appeared to be an audio file
 */
function is_audio($name, $as_admin)
{
    require_code('media_renderer');
    $acceptable_media = MEDIA_TYPE_AUDIO;
    $hooks = find_media_renderers($name, array(), $as_admin, null, $acceptable_media);
    return $hooks !== null;
}

/**
 * Find whether the video specified is actually a 'video', based on file extension.
 *
 * @param  string $name A URL or file path to the video
 * @param  boolean $as_admin Whether there are admin privileges, to render dangerous media types (client-side risk only)
 * @return boolean Whether the string pointed to a file appeared to be an audio file
 */
function is_media($name, $as_admin)
{
    require_code('media_renderer');
    $hooks = find_media_renderers($name, array(), $as_admin, null);
    return $hooks !== null;
}

/**
 * Get a comma-separated list of allowed file types for audio upload.
 *
 * @return string Allowed file types
 */
function get_allowed_image_file_types()
{
    $supported = str_replace(' ', '', get_option('valid_images'));
    return $supported;
}

/**
 * Get a comma-separated list of allowed file types for video upload.
 *
 * @return string Allowed file types
 */
function get_allowed_video_file_types()
{
    $supported = str_replace(' ', '', get_option('valid_videos'));
    if (get_option('allow_audio_videos') == '1') {
        $supported .= ',' . get_allowed_audio_file_types();
    }
    $supported .= ',pdf';
    if (has_privilege(get_member(), 'use_very_dangerous_comcode')) {
        $supported .= ',swf';
    }
    return $supported;
}

/**
 * Get a comma-separated list of allowed file types for audio upload.
 *
 * @return string Allowed file types
 */
function get_allowed_audio_file_types()
{
    $supported = str_replace(' ', '', get_option('valid_audios'));
    return $supported;
}
