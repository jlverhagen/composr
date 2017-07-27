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
 * @package    core_cns
 */

/**
 * Hook class.
 */
class Hook_snippet_profile_tab
{
    /**
     * Run function for snippet hooks. Generates XHTML to insert into a page using AJAX.
     *
     * @return Tempcode The snippet
     */
    public function run()
    {
        $member_id_viewing = get_member();
        $member_id_of = get_param_integer('member_id');

        $hook = filter_naughty_harsh(get_param_string('tab'));

        require_code('urls2');

        $keep_get = array();
        foreach (array_keys($_GET) as $key) {
            if (in_array($key, array('snippet', 'tab', 'url', 'title', 'member_id', 'utheme'))) {
                continue;
            }
            $keep_get[$key] = get_param_string($key, null, true);
        }
        $former_context = set_execution_context(array('page' => 'members', 'type' => 'view', 'id' => $member_id_of) + $keep_get, get_module_zone('members'));

        require_code('hooks/systems/profiles_tabs/' . $hook, true);
        $ob = object_factory('Hook_profiles_tabs_' . $hook);
        if ($ob->is_active($member_id_of, $member_id_viewing)) {
            // We need to minimise the dependency stuff that comes out, we don't need any default values
            push_output_state(false, true);

            // Cleanup dependencies that will already have been handled
            global $CSSS, $JAVASCRIPTS;
            unset($CSSS['global']);
            unset($CSSS['no_cache']);
            unset($JAVASCRIPTS['global']);
            unset($JAVASCRIPTS['staff']);

            // And, go
            $ret = $ob->render_tab($member_id_of, $member_id_viewing);
            $out = new Tempcode();
            $eval = $ret[1]->evaluate();
            $out->attach(symbol_tempcode('CSS_TEMPCODE'));
            $out->attach(symbol_tempcode('JS_TEMPCODE'));
            $out->attach($eval);
            $out->attach(symbol_tempcode('JS_TEMPCODE', array('footer')));

            call_user_func_array('set_execution_context', $former_context);

            return $out;
        }

        // Very likely a session was lost
        if (is_guest()) {
            $login_url = build_url(array('page' => 'login', 'type' => 'login'), '_SELF');
            require_css('login');
            $passion = form_input_hidden('redirect', static_evaluate_tempcode($GLOBALS['FORUM_DRIVER']->member_profile_url($member_id_of, true, true)));
            $ret = do_template('LOGIN_SCREEN', array('_GUID' => 'f401d48a9d2a70af6c2976d396207fc1', 'EXTRA' => '', 'USERNAME' => $GLOBALS['FORUM_DRIVER']->get_username($member_id_of), 'JOIN_URL' => $GLOBALS['FORUM_DRIVER']->join_url(), 'TITLE' => '', 'LOGIN_URL' => $login_url, 'PASSION' => $passion));
            $out = new Tempcode();
            $eval = $ret->evaluate();
            $out->attach(symbol_tempcode('CSS_TEMPCODE'));
            $out->attach(symbol_tempcode('JS_TEMPCODE'));
            $out->attach($eval);
            $out->attach(symbol_tempcode('JS_TEMPCODE', array('footer')));
            return $out;
        }

        call_user_func_array('set_execution_context', $former_context);

        return do_template('INLINE_WIP_MESSAGE', array('_GUID' => 'aae58043638dac785405a42e9578202b', 'MESSAGE' => do_lang_tempcode('INTERNAL_ERROR')));
    }
}
