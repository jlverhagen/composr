<?php /*

 Composr
 Copyright (c) Christopher Graham, 2004-2024

 See docs/LICENSE.md for full licensing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  Christopher Graham
 * @package    cms_homesite_tracker
 */

/*FORCE_ORIGINAL_LOAD_FIRST*/

/**
 * Hook class.
 */
class Hx_content_meta_aware_catalogue_entry extends Hook_content_meta_aware_catalogue_entry
{
    /**
     * Find an entry title.
     *
     * @param  array $row Database row of entry
     * @param  integer $render_type A FIELD_RENDER_* constant
     * @param  boolean $resource_fs_style Whether to use the content API as resource-fs requires (may be slightly different)
     * @return ?mixed Content title (string or Tempcode, depending on $render_type) (null: could not generate)
     */
    public static function generate_catalogue_entry_title(array $row, int $render_type = 1, bool $resource_fs_style = false)
    {
        if (($row['c_name'] != 'tracker') || !addon_installed('cms_homesite_tracker')) {
            return parent::generate_catalogue_entry_title($row, $render_type, $resource_fs_style);
        }

        require_code('catalogues');

        $field_values = get_catalogue_entry_field_values($row['c_name'], $row['id']);

        $ret = new Tempcode();
        $ret->attach('#');
        foreach ($field_values as $field) {
            if (!in_array($field['cf_name'], [do_lang('IDENTIFIER'), do_lang('TITLE')])) {
                continue;
            }

            if ($field['cf_name'] == do_lang('TITLE')) {
                $ret->attach(' - ');
            }

            $val = $field['cf_default'];
            if (array_key_exists('effective_value_pure', $field)) {
                $val = $field['effective_value_pure'];
            } elseif (array_key_exists('effective_value', $field)) {
                $val = $field['effective_value'];
            }

            $ret->attach($val);
        }

        switch ($render_type) {
            case FIELD_RENDER_COMCODE:
                return comcode_escape($ret->evaluate());

            case FIELD_RENDER_HTML:
                return $ret;
        }

        // FIELD_RENDER_PLAIN:
        return strip_html($ret->evaluate());
    }
}
