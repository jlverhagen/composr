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
 * @package    composr_tutorials
 */

/**
 * Block class.
 */
class Block_main_tutorials_carousel
{
    /**
     * Find details of the block.
     *
     * @return ?array Map of block info (null: block is disabled)
     */
    public function info() : ?array
    {
        $info = [];
        $info['author'] = 'Chris Graham';
        $info['organisation'] = 'Composr';
        $info['hacked_by'] = null;
        $info['hack_version'] = null;
        $info['version'] = 1;
        $info['locked'] = false;
        $info['min_cms_version'] = 11.0;
        $info['addon'] = 'composr_tutorials';
        $info['parameters'] = ['param'];
        return $info;
    }

    /**
     * Find caching details for the block.
     *
     * @return ?array Map of cache details (cache_on and ttl) (null: do not cache)
     */
    public function caching_environment() : ?array
    {
        $info = [];
        $info['cache_on'] = <<<'PHP'
        [
            empty($map['param']) ? '' : $map['param'],
        ]
PHP;
        $info['ttl'] = 60;
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
        if (!addon_installed__messaged('composr_tutorials', $error_msg)) {
            return $error_msg;
        }
        if (!addon_installed__messaged('cms_homesite', $error_msg)) {
            return $error_msg;
        }
        if (!addon_installed__messaged('cms_release_build', $error_msg)) {
            return $error_msg;
        }

        $block_id = get_block_id($map);

        $criteria = empty($map['param']) ? '' : $map['param'];
        if ($criteria == '') {
            $criteria = 'recent';
        }

        require_code('tutorials');
        $tutorials = list_tutorials_by($criteria);
        $_tutorials = templatify_tutorial_list($tutorials, true);

        return do_template('BLOCK_MAIN_TUTORIALS_CAROUSEL', [
            '_GUID' => '07b265b808abd02cc8abae7e3fe6992d',
            'BLOCK_ID' => $block_id,
            'TUTORIALS' => $_tutorials,
        ]);
    }
}
