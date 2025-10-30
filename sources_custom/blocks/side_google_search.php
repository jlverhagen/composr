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
 * @package    google_search
 */

/**
 * Block class.
 */
class Block_side_google_search
{
    /**
     * Find details of the block.
     *
     * @return ?array Map of block info (null: block is disabled)
     */
    public function info() : ?array
    {
        $info = [];
        $info['author'] = 'Kamen / Chris Graham';
        $info['organisation'] = 'Composr';
        $info['hacked_by'] = null;
        $info['hack_version'] = null;
        $info['version'] = 2;
        $info['locked'] = false;
        $info['min_cms_version'] = 11.0;
        $info['addon'] = 'google_search';
        $info['parameters'] = ['param', 'page_name'];
        return $info;
    }

    /**
     * Find caching details for the block.
     *
     * @return ?array Map of cache details (cache_on and ttl) (null: block is disabled)
     */
    public function caching_environment() : ?array
    {
        $info = [];
        $info['cache_on'] = <<<'PHP'
        [
            array_key_exists('page_name', $map) ? $map['page_name'] : 'google_search',
            array_key_exists('param', $map) ? $map['param'] : '',
        ]
PHP;
        $info['ttl'] = 24 * 60 * 60 * 14;
        return $info;
    }

    /**
     * Execute the block.
     *
     * @param  array $map A map of parameters
     * @return Tempcode The result of execution
     */
    public function run(array $map) : object
    {
        i_solemnly_declare(I_UNDERSTAND_SQL_INJECTION | I_UNDERSTAND_XSS | I_UNDERSTAND_PATH_INJECTION);

        $error_msg = new Tempcode();
        if (!addon_installed__messaged('google_search', $error_msg)) {
            return $error_msg;
        }

        require_lang('google_search');

        $block_id = get_block_id($map);

        $page_name = !empty($map['page_name']) ? $map['page_name'] : '_google_search';

        if (empty($map['param'])) {
            return do_template('RED_ALERT', ['_GUID' => '8b1b8190e89d9a35c445f6606148c393', 'TEXT' => do_lang_tempcode('NO_PARAMETER_SENT', 'param')]);
        }
        $id = $map['param'];

        return do_template('BLOCK_SIDE_GOOGLE_SEARCH', [
            '_GUID' => 'e42a949234538f8c2f9a8e96bc43056d',
            'BLOCK_ID' => $block_id,
            'TITLE' => do_lang_tempcode('BLOCK_GOOGLE_TITLE'),
            'PAGE_NAME' => $page_name,
            'ID' => $id,
        ]);
    }
}
