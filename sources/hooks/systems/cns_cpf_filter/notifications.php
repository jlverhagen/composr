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
 * @package    cns_forum
 */

/**
 * Hook class.
 */
class Hook_cns_cpf_filter_notifications
{
    /**
     * Find which special CPFs to enable.
     *
     * @return array A list of CPFs to enable
     */
    public function to_enable()
    {
        return array('smart_topic_notification' => true);
    }
}
