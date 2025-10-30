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
class Hook_comcode_encrypt
{
    /**
     * Run function for Comcode hooks. They find the custom-comcode-row-like attributes of the tag.
     *
     * @return ?array Fake Custom Comcode row (null: disabled)
     */
    public function get_tag() : ?array
    {
        if (!addon_installed('password_censor')) {
            return null;
        }

        // You may wonder how a Comcode tag can change itself.
        //  The _password_censor function interactively is changing submitted Comcode.

        return [
            'tag_title' => 'Encrypt',
            'tag_description' => 'Store the contents of the tag as encrypted in the database.',
            'tag_example' => '[encrypt]Text to encrypt[/encrypt]',
            'tag_tag' => 'encrypt',
            'tag_replace' => cms_file_get_contents_safe(get_file_base() . '/themes/default/templates_custom/COMCODE_ENCRYPT.tpl', FILE_READ_LOCK | FILE_READ_UNIXIFIED_TEXT | FILE_READ_BOM),
            'tag_parameters' => '',
            'tag_block_tag' => 1,
            'tag_textual_tag' => 1,
            'tag_dangerous_tag' => 0,
        ];
    }
}
