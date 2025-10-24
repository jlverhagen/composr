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
 * @package    webdav
 */

/*CQC: No check*/

use Sabre\DAV;

/**
 * Standard code module initialisation function.
 *
 * @ignore
 */
function init__webdav()
{
    global $WEBDAV_LOG_FILE;
    $WEBDAV_LOG_FILE = null;
}

/**
 * Main WebDAV entry-point script.
 */
function webdav_script()
{
    if (!addon_installed('webdav')) {
        warn_exit(do_lang_tempcode('MISSING_ADDON', escape_html('webdav')));
    }

    if (!addon_installed('commandr')) {
        warn_exit(do_lang_tempcode('MISSING_ADDON', escape_html('commandr')));
    }

    if (version_compare(PHP_VERSION, '8.0.0', '<')) { // LEGACY: SabreDav does not require 8.0 but its dependencies do
        require_lang('critical_error');
        warn_exit(do_lang_tempcode('PHP_TOO_OLD', escape_html('8.0')));
    }

    cms_ini_set('ocproducts.type_strictness', '0');

    header('X-Robots-Tag: noindex');

    require_code('sabredav/vendor/autoload');

    require_code('webdav_commandr_fs');

    // Optional logging (create this file and give write access to it)
    $log_path = get_custom_file_base() . '/data_custom/modules/webdav/tmp/debug.log';
    global $WEBDAV_LOG_FILE;
    if (is_file($log_path)) {
        $WEBDAV_LOG_FILE = fopen($log_path, 'ab');
        $log_message = 'Request... ' . $_SERVER['REQUEST_METHOD'] . ': ' . $_SERVER['REQUEST_URI'];
        //$log_message.="\n".file_get_contents('php://input'); // Only enable when debugging, as breaks PUT requests (see http://stackoverflow.com/questions/3107624/why-can-php-input-be-read-more-than-once-despite-the-documentation-saying-othe)
        webdav_log($log_message);
    }

    // Initialise...

    $root_dir = new webdav_commandr_fs\Directory('');
    $server = new DAV\Server($root_dir);
    webdav_commandr_fs\ServerRegistry::setServer($server);

    $parsed = parse_url(get_base_url());
    if (!isset($parsed['path'])) {
        $parsed['path'] = '';
    }
    if (substr($parsed['path'], -1) != '/') {
        $parsed['path'] .= '/';
    }
    $webdav_root = get_value('webdav_root');
    if ($webdav_root === null) {
        $webdav_root = (preg_match('#^' . preg_quote($parsed['path'], '#') . 'webdav#', $_SERVER['REQUEST_URI']) != 0) ? 'webdav' : '';
    }
    $server->setBaseUri($parsed['path'] . $webdav_root);

    if (!$GLOBALS['FORUM_DRIVER']->is_super_admin(get_member())) { // If already admin (e.g. backdoor_ip), no need for access check
        $auth_backend = new webdav_commandr_fs\Auth();
        $auth_plugin = new DAV\Auth\Plugin($auth_backend);
        $server->addPlugin($auth_plugin);
    }

    $lock_backend = new DAV\Locks\Backend\File(get_custom_file_base() . '/data_custom/modules/webdav/locks/locks.dat');
    $lock_plugin = new DAV\Locks\Plugin($lock_backend);
    $server->addPlugin($lock_plugin);

    $tffp = new DAV\TemporaryFileFilterPlugin(get_custom_file_base() . '/data_custom/modules/webdav/tmp');
    $server->addPlugin($tffp);

    $plugin = new DAV\Browser\Plugin();
    $server->addPlugin($plugin);

    $server->start();

    // Close off log
    if ($WEBDAV_LOG_FILE !== null) {
        fclose($WEBDAV_LOG_FILE);
        $WEBDAV_LOG_FILE = null;
    }
}

/**
 * Log something to the WebDAV log.
 *
 * @param  string $str String to log
 */
function webdav_log(string $str)
{
    global $WEBDAV_LOG_FILE;
    if ($WEBDAV_LOG_FILE !== null) {
        fwrite($WEBDAV_LOG_FILE, $str . "\n\n");
    }
}
