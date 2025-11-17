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
 * @package    google_analytics
 */

/**
 * Hook class.
 */
class Hook_admin_stats_google_analytics extends Source_hook_stats_blob
{
    /**
     * Find metadata about stats categories that are defined by this stats hook.
     *
     * @return ?array Map of metadata (null: hook is disabled)
     */
    public function category_info() : ?array
    {
        if (!addon_installed('google_analytics')) {
            return null;
        }

        require_code('google_analytics');
        require_lang('google_analytics');

        $result = google_analytics_initialise(true);
        if (is_object($result)) {
            return null;
        }

        return [
            'google_analytics_page_hits' => [
                'label_lang_string' => 'GOOGLE_ANALYTICS_PAGE_HITS',
                'icon' => 'spare/analytics',
            ],
            'google_analytics_sessions_devices' => [
                'label_lang_string' => 'GOOGLE_ANALYTICS_SESSIONS_DEVICES',
                'icon' => 'spare/analytics',
            ],
            'google_analytics_demographics' => [
                'label_lang_string' => 'GOOGLE_ANALYTICS_DEMOGRAPHICS',
                'icon' => 'spare/analytics',
            ],
            'google_analytics_performance' => [
                'label_lang_string' => 'GOOGLE_ANALYTICS_PERFORMANCE',
                'icon' => 'spare/analytics',
            ],
        ];
    }

    /**
     * Find metadata about stats graphs that are provided by this stats hook.
     *
     * @param  boolean $for_kpi Whether this is for setting up a KPI
     * @return ?array Map of metadata (null: hook is disabled)
     */
    public function info(bool $for_kpi = false) : ?array
    {
        if (!addon_installed('google_analytics')) {
            return null;
        }

        require_code('google_analytics');
        require_lang('google_analytics');

        $result = google_analytics_initialise(true);
        if (is_object($result)) {
            return null;
        }

        $metrics = enumerate_google_analytics_metrics();

        $ret = [];

        foreach ($metrics as $metric_name => $metric_details) {
            $ret['google_analytics__' . $metric_name] = [
                'label' => make_string_tempcode($metric_details['label']),
                'category' => $metric_details['category'],
                'filters' => [],
                'pivot' => null,
            ];
        }

        return $ret;
    }

    /**
     * Generate graph.
     *
     * @param  string $graph_name Graph name
     * @param  array $filters Filter settings to take precedence
     * @return Tempcode Graph
     */
    public function generate(string $graph_name, array $filters) : object
    {
        static $done_once = false;
        if (!$done_once) {
            set_helper_panel_text(comcode_lang_string('DOC_GOOGLE_ANALYTICS'));
            $done_once = true;
        }

        $days = empty($filters['days']) ? 31 : intval($filters['days']);

        return render_google_analytics(preg_replace('#^google_analytics__#', '', $graph_name), null, $days);
    }
}
