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
 * @package    hybridauth
 */

/**
 * Hook class.
 */
class Hook_symbol_HYBRIDAUTH_BUTTONS
{
    /**
     * Get information about this symbol.
     *
     * @return ?array Array of information (null: hook disabled)
     */
    public function info() : ?array
    {
        if (!addon_installed('hybridauth')) {
            return null;
        }

        return [
            'compile' => SYMBOL_COMPILE_STATIC_NONE,
            'public' => false,
        ];
    }

    /**
     * Run function for symbol hooks. Searches for tasks to perform.
     *
     * @param  array $param Symbol parameters
     * @param  string $lang The language to evaluate this symbol in (some symbols refer to language elements)
     * @param  array $escaped Array of escaping operations
     * @return string Result
     */
    public function run(array $param, string $lang, array $escaped) : string
    {
        require_code('hybridauth');
        require_lang('hybridauth');

        $providers = enumerate_hybridauth_providers();

        $only_prominent = !empty($param[1]);
        $max = !empty($param[2]) ? intval($param[2]) : null;

        $keep = symbol_tempcode('KEEP');

        // Where to redirect to
        $page_after_login = get_option('page_after_login');
        if ($page_after_login != '') {
            if (strpos($page_after_login, ':') === false) {
                $zone = get_page_zone($page_after_login, false);
                if ($zone === null) {
                    $zone = 'site';
                }
                $return_url = static_evaluate_tempcode(build_url(['page' => $page_after_login], $zone));
            } else {
                $return_url = page_link_to_url($page_after_login);
            }
        } else {
            if ((get_page_name() == 'join') || (currently_logging_in())) {
                $return_url = static_evaluate_tempcode(build_url(['page' => ''], ''));
            } else {
                $return_url = get_self_url(true, true);
            }
        }
        $return_url_part = urlencode(static_evaluate_tempcode(protect_url_parameter($return_url)));

        $buttons = '';
        $i = 0;
        foreach ($providers as $provider => $info) {
            if (($only_prominent) && (!$info['prominent_button'])) {
                continue;
            }

            if (($max !== null) && ($i == $max)) {
                break;
            }

            if (!$info['enabled']) {
                continue;
            }

            $url = find_script('hybridauth') . '?provider=' . urlencode($provider) . '&composr_return_url=' . $return_url_part . $keep->evaluate();

            $button = do_template('HYBRIDAUTH_BUTTON', [
                '_GUID' => '274651f0ae26f38cd09bf11e30a8ef23',
                'CODENAME' => $provider,
                'LABEL' => $info['label'],
                'BACKGROUND_COLOUR' => $info['background_colour'],
                'TEXT_COLOUR' => $info['text_colour'],
                'ICON' => $info['icon'],
                'URL' => $url,
            ]);
            $buttons .= $button->evaluate();

            $i++;
        }

        return $buttons;
    }
}
