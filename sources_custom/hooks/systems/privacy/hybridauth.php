<?php /*

 Composr
 Copyright (c) Christopher Graham, 2004-2024

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
class Hook_privacy_hybridauth extends Hook_privacy_base
{
    /**
     * Find privacy details.
     *
     * @return ?array A map of privacy details in a standardised format (null: disabled)
     */
    public function info() : ?array
    {
        if (!addon_installed('hybridauth')) {
            return null;
        }

        return [
            'label' => 'hybridauth:HYBRIDAUTH',

            'description' => 'hybridauth:DESCRIPTION_PRIVACY_HYBRIDAUTH',

            'cookies' => [
                'hybridauth' => [
                    'category' => 'ESSENTIAL',
                    'reason' => do_lang_tempcode('hybridauth:COOKIE_hybridauth'),
                    'session' => false,
                    'httponly' => true,
                ],
            ],

            'positive' => [
            ],

            'general' => [
            ],

            'database_records' => [
            ],
        ];
    }
}
