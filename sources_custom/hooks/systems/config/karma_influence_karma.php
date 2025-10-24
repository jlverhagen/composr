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
 * @package    karma
 */

/**
 * Hook class.
 */
class Hook_config_karma_influence_karma
{
    /**
     * Gets the details relating to the config option.
     *
     * @return array The details
     */
    public function get_details() : array
    {
        return [
            'human_name' => 'KARMA_INFLUENCE_KARMA',
            'type' => 'integer',
            'category' => 'FEATURE',
            'group' => 'KARMIC_INFLUENCE',
            'explanation' => 'CONFIG_OPTION_karma_influence_karma',
            'shared_hosting_restricted' => '0',
            'order_in_category_group' => 4,
            'list_options' => '',
            'required' => true,
            'public' => false,
            'addon' => 'karma',
        ];
    }

    /**
     * Gets the default value for the config option.
     *
     * @return ?string The default value (null: option is disabled)
     */
    public function get_default() : ?string
    {
        if (!addon_installed('karma') || get_forum_type() != 'cns') {
            return null;
        }

        return '0';
    }
}
