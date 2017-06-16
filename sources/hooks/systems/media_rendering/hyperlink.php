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
 * @package    core_rich_media
 */
/*
Notes...
 - Link rendering will not use passed description parameter, etc. This is intentional: the normal flow of rendering through a standardised media template is not used.
*/

/**
 * Hook class.
 */
class Hook_media_rendering_hyperlink
{
    /**
     * Get the label for this media rendering type.
     *
     * @return string The label
     */
    public function get_type_label()
    {
        require_lang('comcode');
        return do_lang('MEDIA_TYPE_' . preg_replace('#^Hook_media_rendering_#', '', __CLASS__));
    }

    /**
     * Find the media types this hook serves.
     *
     * @return integer The media type(s), as a bitmask
     */
    public function get_media_type()
    {
        return MEDIA_TYPE_OTHER;
    }

    /**
     * See if we can recognise this mime type.
     *
     * @param  ID_TEXT $mime_type The mime type
     * @return integer Recognition precedence
     */
    public function recognises_mime_type($mime_type)
    {
        return MEDIA_RECOG_PRECEDENCE_LOW;
    }

    /**
     * See if we can recognise this URL pattern.
     *
     * @param  URLPATH $url URL to pattern match
     * @return integer Recognition precedence
     */
    public function recognises_url($url)
    {
        // Won't link to local URLs
        if (strpos($url, '://localhost/') !== false && strpos(get_base_url(), '://localhost/') === false) {
            return MEDIA_RECOG_PRECEDENCE_NONE;
        }
        if (strpos($url, '://127.0.0.1/') !== false && strpos(get_base_url(), '://127.0.0.1/') === false) {
            return MEDIA_RECOG_PRECEDENCE_NONE;
        }
        if (strpos($url, '://localhost:') !== false && strpos(get_base_url(), '://localhost:') === false) {
            return MEDIA_RECOG_PRECEDENCE_NONE;
        }
        if (strpos($url, '://127.0.0.1:') !== false && strpos(get_base_url(), '://127.0.0.1:') === false) {
            return MEDIA_RECOG_PRECEDENCE_NONE;
        }

        return MEDIA_RECOG_PRECEDENCE_LOW;
    }

    /**
     * Provide code to display what is at the URL, in the most appropriate way.
     *
     * @param  mixed $url URL to render
     * @param  mixed $url_safe URL to render (no sessions etc)
     * @param  array $attributes Attributes (e.g. width, height, length)
     * @param  boolean $as_admin Whether there are admin privileges, to render dangerous media types
     * @param  ?MEMBER $source_member Member to run as (null: current member)
     * @return Tempcode Rendered version
     */
    public function render($url, $url_safe, $attributes, $as_admin = false, $source_member = null)
    {
        $_url = is_object($url) ? $url->evaluate() : $url;
        $_url_safe = is_object($url_safe) ? $url_safe->evaluate() : $url_safe;

        if ((isset($attributes['likely_not_framed'])) && ($attributes['likely_not_framed'] == '1')) {
            $attributes['framed'] = '0';
        }

        // Try and find the link title
        require_code('http');
        $meta_details = get_webpage_meta_details($_url);

        $defined_not_framed = ((array_key_exists('framed', $attributes)) && ($attributes['framed'] == '0'));

        // Render as a nice preview box
        if (!$defined_not_framed) {
            if ((array_key_exists('mime_type', $attributes)) && ($attributes['mime_type'] != '')) {
                $mime_type = $attributes['mime_type'];
            } else {
                $mime_type = $meta_details['t_mime_type'];
            }
            if ($mime_type != 'text/html' && $mime_type != 'application/xhtml+xml') { // A download, i.e. not a webpage. We assume we will never want to force a webpage as a download unless we specify a mime-type. Richer things like PDFs will have been claimed by a better hook
                return do_template('MEDIA_DOWNLOAD', _create_media_template_parameters($url, $attributes, $as_admin, $source_member));
            }

            if (($meta_details['t_description'] != '') || ($meta_details['t_image_url'] != '')) {
                $title = $meta_details['t_title'];
                $meta_title = $meta_details['t_meta_title'];
                if ($meta_title == '') {
                    $meta_title = escape_html($title);
                }

                return do_template('MEDIA_WEBPAGE_SEMANTIC', array(
                    '_GUID' => '59ae26467bbde639a176a213d85370ea',
                    'TITLE' => $meta_details['t_title'],
                   'META_TITLE' => $meta_title,
                   'DESCRIPTION' => ((array_key_exists('description', $attributes)) && ($attributes['description'] != '')) ? $attributes['description'] : $meta_details['t_description'],
                   'IMAGE_URL' => ((array_key_exists('thumb_url', $attributes)) && ($attributes['thumb_url'] != '')) ? $attributes['thumb_url'] : $meta_details['t_image_url'],
                   'URL' => $meta_details['t_url'],
                   'WIDTH' => ((array_key_exists('width', $attributes)) && ($attributes['width'] != '')) ? $attributes['width'] : get_option('thumb_width'),
                   'HEIGHT' => ((array_key_exists('height', $attributes)) && ($attributes['height'] != '')) ? $attributes['height'] : get_option('thumb_width'),
                ));
            }
            // Hmm, okay we'll proceed towards a plain link if it's not a download and has no metadata to box
        } // Hmm, we explicitly said we want a plain link

        $link_captions_title = $meta_details['t_title'];
        if ($link_captions_title == '') {
            if (!empty($attributes['filename'])) {
                $link_captions_title = $attributes['filename'];
            } else {
                $link_captions_title = $_url_safe;
            }
        }

        require_code('comcode_renderer');
        if ($source_member === null) {
            $source_member = get_member();
        }
        $comcode = '';

        // Render as a 'page' link?
        $page_link = url_to_page_link($_url_safe, true);
        if ($page_link != '') {
            return _do_tags_comcode('page', array('param' => $page_link), make_string_tempcode(escape_html($link_captions_title)), false, '', 0, $source_member, false, $GLOBALS['SITE_DB'], $comcode, false, false);
        }

        // Okay, just render as a URL then
        if (is_object($url)) {
            $url_tempcode = $url;
        } else {
            $url_tempcode = new Tempcode();
            $url_tempcode->attach($url);
        }
        return _do_tags_comcode('url', array('param' => $link_captions_title), $url_tempcode, false, '', 0, $source_member, false, $GLOBALS['SITE_DB'], $comcode, false, false);
    }
}
