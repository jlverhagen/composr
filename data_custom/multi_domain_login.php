<?php

global $SITE_INFO;

require_once(dirname(dirname(__FILE__)) . '/_config.php');

$session_expiry_time = floatval($_GET['session_expiry_time']);
$session_id = $_GET['session_id'];
$guest_session = ($_GET['guest_session'] == '1');

$timeout = $guest_session ? (time() + intval(60.0 * 60.0 * max(0.017, $session_expiry_time))) : null;

$test = setcookie(get_session_cookie(), $session_id, $timeout, get_cookie_path());

/* Caching won't work well
$expires = 60 * 60 * 1;
header('Pragma: public');
header('Cache-Control: max-age=' . strval($expires));
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT');
*/
@header('Expires: Mon, 20 Dec 1998 01:00:00 GMT');
@header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
@header('Cache-Control: no-cache, max-age=0');
@header('Pragma: no-cache'); // for proxies, and also IE

header('Content-type: image/png');
$img = imagecreatetruecolor(1, 1);
imagesavealpha($img, true);
$color = imagecolorallocatealpha($img, 0, 0, 0, 127);
imagefill($img, 0, 0, $color);
imagepng($img);
imagedestroy($img);

/**
 * Get the session cookie's name.
 *
 * @return string The session ID cookie's name
 */
function get_session_cookie()
{
    global $SITE_INFO;
    if (empty($SITE_INFO['session_cookie'])) {
        $SITE_INFO['session_cookie'] = 'cms_session';
    }
    return $SITE_INFO['session_cookie'];
}

/**
 * Get the Composr cookie path.
 *
 * @return ?string The Composr cookie path (null: no special path, global)
 */
function get_cookie_path()
{
    global $SITE_INFO;
    $ret = array_key_exists('cookie_path', $SITE_INFO) ? $SITE_INFO['cookie_path'] : '/';
    return ($ret == '') ? null : $ret;
}

/**
 * Get the Composr cookie domain.
 *
 * @return ?string The Composr cookie domain (null: current domain)
 */
function get_cookie_domain()
{
    global $SITE_INFO;
    $ret = array_key_exists('cookie_domain', $SITE_INFO) ? $SITE_INFO['cookie_domain'] : null;
    return ($ret == '') ? null : $ret;
}
