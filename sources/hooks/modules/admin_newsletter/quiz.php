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
 * @package    quizzes
 */

/**
 * Hook class.
 */
class Hook_whatsnew_quiz
{
    /**
     * Run function for newsletter hooks.
     *
     * @param  TIME $cutoff_time The time that the entries found must be newer than
     * @param  LANGUAGE_NAME $lang The language the entries found must be in
     * @param  string $filter Category filter to apply
     * @return array Tuple of result details
     */
    public function run($cutoff_time, $lang, $filter)
    {
        if (!addon_installed('quizzes')) {
            return array();
        }

        require_lang('quiz');

        unset($filter); // Not used

        $max = intval(get_option('max_newsletter_whatsnew'));

        $new = new Tempcode();

        $rows = $GLOBALS['SITE_DB']->query('SELECT * FROM ' . $GLOBALS['SITE_DB']->get_table_prefix() . 'quizzes WHERE q_add_date>' . strval($cutoff_time) . ' ORDER BY q_add_date DESC', $max);
        if (count($rows) == $max) {
            return array();
        }
        foreach ($rows as $row) {
            $id = $row['id'];
            $_url = build_url(array('page' => 'quiz', 'type' => 'do', 'id' => $row['id']), get_module_zone('quiz'), null, false, false, true);
            $url = $_url->evaluate();
            $name = get_translated_text($row['q_name'], null, $lang);
            $description = get_translated_text($row['q_start_text'], null, $lang);
            $member_id = null;
            $new->attach(do_template('NEWSLETTER_WHATSNEW_RESOURCE_FCOMCODE', array('_GUID' => '1a8cad8defc5b92eded5aee376250ae5', 'MEMBER_ID' => $member_id, 'URL' => $url, 'NAME' => $name, 'DESCRIPTION' => $description, 'CONTENT_TYPE' => 'quiz', 'CONTENT_ID' => strval($id)), null, false, null, '.txt', 'text'));

            handle_has_checked_recently($url); // We know it works, so mark it valid so as to not waste CPU checking within the generated Comcode
        }

        return array($new, do_lang('QUIZZES', '', '', '', $lang));
    }
}
