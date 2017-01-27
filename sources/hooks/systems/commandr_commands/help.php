<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2016

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    commandr
 */

/**
 * Hook class.
 */
class Hook_commandr_command_help
{
    /**
     * Run function for Commandr hooks.
     *
     * @param  array $options The options with which the command was called
     * @param  array $parameters The parameters with which the command was called
     * @param  object $commandr_fs A reference to the Commandr filesystem object
     * @return array Array of stdcommand, stdhtml, stdout, and stderr responses
     */
    public function run($options, $parameters, &$commandr_fs)
    {
        if (array_key_exists(0, $parameters)) {
            // Load up the relevant block and grab its help output
            $hooks = find_all_hooks('systems', 'commandr_commands');
            $hook_return = null;
            foreach (array_keys($hooks) as $hook) {
                if ($hook == $parameters[0]) {
                    require_code('hooks/systems/commandr_commands/' . filter_naughty_harsh($hook));
                    $object = object_factory('Hook_commandr_command_' . filter_naughty_harsh($hook), true);
                    if (is_null($object)) {
                        continue;
                    }
                    $hook_return = $object->run(array('help' => null), array(), $commandr_fs);
                    break;
                }
            }

            if (!is_null($hook_return)) {
                return array($hook_return[0], $hook_return[1], $hook_return[2], $hook_return[3]);
            } else {
                return array('', '', '', do_lang('NO_HELP'));
            }
        } else {
            // Output a standard "how to use Commandr" help page
            return array('window.open(\'' . addslashes(get_tutorial_url('tut_commandr')) . '\',\'commandr_window1\',\'\');', '', do_lang('SUCCESS'), '');
        }
    }
}
