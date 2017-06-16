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
 * @package    core
 */

/**
 * Hook class.
 */
class Hook_spam_heuristics_repetition
{
    /**
     * Find the confidence score for a particular spam heuristic as applied to the current context.
     *
     * @param  string $post_data Confidence score
     * @return integer Confidence score
     */
    public function assess_confidence($post_data)
    {
        $score = intval(get_option('spam_heuristic_confidence_repetition'));
        if ($score == 0) {
            return 0;
        }

        require_code('content');
        foreach (array_keys(find_all_hooks('systems', 'content_meta_aware')) as $hook) {
            $cma_ob = get_content_object($hook);
            $cma_info = $cma_ob->info();
            if (!empty($cma_info['support_spam_heuristics'])) {
                $data = post_param_string($cma_info['support_spam_heuristics'], null);
                if (!empty($data)) {
                    $where = array(
                        $cma_info['submitter_field'] => get_member(),
                    );
                    if ($cma_info['description_field_dereference']) {
                        $where[$GLOBALS['SITE_DB']->translate_field_ref($cma_info['description_field'])] = $data;
                    } else {
                        $where[$cma_info['description_field']] = $data;
                    }

                    $threshold = 60 * 60 * intval(get_option('spam_heuristic_repetition'));

                    $previous = $cma_info['db']->query_select_value(
                        $cma_info['table'],
                        'COUNT(*)',
                        $where,
                        ' AND ' . $cma_info['add_time_field'] . '>' . strval(time() - $threshold)
                    );
                    if ($previous > 0) {
                        return $score;
                    }
                }
            }
        }

        return 0;
    }
}
