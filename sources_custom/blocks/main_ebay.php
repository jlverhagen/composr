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
 * @package    ebay_store
 */

/**
 * Block class.
 */
class Block_main_ebay
{
    /**
     * Find details of the block.
     *
     * @return ?array Map of block info (null: block is disabled)
     */
    public function info() : ?array
    {
        $info = [];
        $info['author'] = 'Babu';
        $info['organisation'] = 'Composr';
        $info['hacked_by'] = null;
        $info['hack_version'] = null;
        $info['version'] = 2;
        $info['locked'] = false;
        $info['min_cms_version'] = 11.0;
        $info['addon'] = 'ebay_store';
        $info['parameters'] = ['seller', 'query', 'max_entries', 'image_size', 'domain', 'title'];
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
            empty($map['title']) ? '' : $map['title'],
            empty($map['max_entries']) ? 4 : intval($map['max_entries']),
            empty($map['image_size']) ? 80 : intval($map['image_size']),
            empty($map['domain']) ? 0 : intval(preg_replace('#=.*$#', '', $map['domain'])),
            isset($map['seller']) ? $map['seller'] : '',
            empty($map['query']) ? '' : preg_replace('#\s#', '+', $map['query']),
        ]
PHP;
        $info['ttl'] = 15;
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
        if (!addon_installed__messaged('ebay_store', $error_msg)) {
            return $error_msg;
        }

        require_lang('ebay');

        $block_id = get_block_id($map);

        if (empty($map['seller'])) {
            return do_template('RED_ALERT', ['_GUID' => '7d0bb5c1c16a31f8e8b1c3bd04831017', 'TEXT' => do_lang_tempcode('NO_PARAMETER_SENT', 'seller')]);
        }

        $title = empty($map['title']) ? do_lang_tempcode('BLOCK_EBAY_TITLE') : $map['title'];
        $max_entries = empty($map['max_entries']) ? 4 : intval($map['max_entries']);
        $image_size = empty($map['image_size']) ? 80 : intval($map['image_size']);
        $domain = empty($map['domain']) ? 0 : intval(preg_replace('#=.*$#', '', $map['domain']));
        $seller = $map['seller'];
        $query = empty($map['query']) ? '' : preg_replace('#\s#', '+', $map['query']); // e.g. Gadgets

        return do_template('BLOCK_MAIN_EBAY', [
            '_GUID' => 'ffda4477bf08164f80dd45ef2985dfe9',
            'BLOCK_ID' => $block_id,
            'TITLE' => $title,
            'MAX_ENTRIES' => strval($max_entries),
            'IMAGE_SIZE' => strval($image_size),
            'DOMAIN' => strval($domain),
            'SELLER' => $seller,
            'QUERY' => $query,
        ]);
    }
}
