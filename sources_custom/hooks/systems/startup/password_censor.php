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
 * @package    password_censor
 */

/**
 * Hook class.
 */
class Hook_startup_password_censor
{
    public function run()
    {
        if (!addon_installed('password_censor')) {
            return;
        }

        foreach ($_POST as $key => $val) {
            if ((is_string($val)) && (strpos($val, '[encrypt') !== false)) {
                require_code('password_censor');
                $_POST[$key] = _password_censor(post_param_string($key), PASSWORD_CENSOR__PRE_SCAN);
            }
        }
    }
}
