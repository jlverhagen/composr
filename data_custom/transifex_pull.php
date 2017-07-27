<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2016

 See text/EN/licence.txt for full licencing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    transifex
 */

// Examples:
// http://localhost/composr/data_custom/transifex_pull.php?username=info@ocproducts.com&password=xxx&lang=ES

// Parameters:
//    username                  Transifex username (required, if not saved)
//    password                  Transifex password (required, if not saved)
//    output=0|1                Leave as 0 to save individual files directly into Composr, Set as 1 to download a TAR file (default: 0)
//    lang                      Set to a language code to  download just that language (default: blank)
//    core_only=0|1             Set to 1 to only download translations for bundled addons (labelled as 'core' within Transifex) (default: 0)
//    limit_substring           Set to a resource file name substring to download just that resource file(s) (default: blank)

/*
To save the username/password credentials...
:set_value('transifex_username', 'xxx', true);
:set_value('transifex_password', 'xxx', true);
*/

// Fixup SCRIPT_FILENAME potentially being missing
$_SERVER['SCRIPT_FILENAME'] = __FILE__;

// Find Composr base directory, and chdir into it
global $FILE_BASE, $RELATIVE_PATH;
$FILE_BASE = (strpos(__FILE__, './') === false) ? __FILE__ : realpath(__FILE__);
if (substr($FILE_BASE, -4) == '.php') {
    $a = strrpos($FILE_BASE, '/');
    if ($a === false) {
        $a = 0;
    }
    $b = strrpos($FILE_BASE, '\\');
    if ($b === false) {
        $b = 0;
    }
    $FILE_BASE = dirname($FILE_BASE);
}
if (!is_file($FILE_BASE . '/sources/global.php')) {
    $a = strrpos($FILE_BASE, '/');
    if ($a === false) {
        $a = 0;
    }
    $b = strrpos($FILE_BASE, '\\');
    if ($b === false) {
        $b = 0;
    }
    $RELATIVE_PATH = basename($FILE_BASE);
    $FILE_BASE = dirname($FILE_BASE);
} else {
    $RELATIVE_PATH = '';
}
@chdir($FILE_BASE);

global $FORCE_INVISIBLE_GUEST;
$FORCE_INVISIBLE_GUEST = false;
global $EXTERNAL_CALL;
$EXTERNAL_CALL = false;
global $KNOWN_UTF8;
$KNOWN_UTF8 = true;
if (!is_file($FILE_BASE . '/sources/global.php')) {
    exit('<!DOCTYPE html>' . "\n" . '<html lang="EN"><head><title>Critical startup error</title></head><body><h1>Composr startup error</h1><p>The second most basic Composr startup file, sources/global.php, could not be located. This is almost always due to an incomplete upload of the Composr system, so please check all files are uploaded correctly.</p><p>Once all Composr files are in place, Composr must actually be installed by running the installer. You must be seeing this message either because your system has become corrupt since installation, or because you have uploaded some but not all files from our manual installer package: the quick installer is easier, so you might consider using that instead.</p><p>ocProducts maintains full documentation for all procedures and tools, especially those for installation. These may be found on the <a href="http://compo.sr">Composr website</a>. If you are unable to easily solve this problem, we may be contacted from our website and can help resolve it for you.</p><hr /><p style="font-size: 0.8em">Composr is a website engine created by ocProducts.</p></body></html>');
}
require($FILE_BASE . '/sources/global.php');

require_code('transifex');
transifex_pull_script();
