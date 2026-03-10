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
 * @package    ezoic
 */

/**
 * Hook class.
 */
class Hook_startup_ezoic
{
    public function run($MICRO_BOOTUP, $MICRO_AJAX_BOOTUP)
    {
        if ((!$MICRO_AJAX_BOOTUP) && (!$MICRO_BOOTUP) && (running_script('index'))) {
            if (!addon_installed('ezoic')) {
                return;
            }

            if (!allowed_cookies('MARKETING')) {
                return;
            }
            if (!allowed_cookies('ANALYTICS')) {
                return;
            }

            require_code('csp');
            $nonce_html = csp_nonce_html();

            attach_to_screen_header('<script ' . $nonce_html . ' data-cfasync="false" src="https://cmp.gatekeeperconsent.com/min.js"></script>');
            attach_to_screen_header('<script ' . $nonce_html . ' data-cfasync="false" src="https://the.gatekeeperconsent.com/cmp.min.js"></script>');
            attach_to_screen_header('<script ' . $nonce_html . ' async src="//www.ezojs.com/ezoic/sa.min.js"></script>');

            $inline = new Tempcode();
            $inline->attach('<script ' . $nonce_html . '>');
            $inline->attach('(function () {');
            $inline->attach('window.ezstandalone = window.ezstandalone || {};');
            $inline->attach('ezstandalone.cmd = ezstandalone.cmd || [];');
            $inline->attach('})();');
            $inline->attach('</script>');
            attach_to_screen_header($inline);
        }
    }
}
