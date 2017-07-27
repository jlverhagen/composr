<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2016

 See text/EN/licence.txt for full licencing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    getid3
 */

/**
 * Hook class.
 */
class Hook_addon_registry_getid3
{
    /**
     * Get a list of file permissions to set
     *
     * @param  boolean $runtime Whether to include wildcards represented runtime-created chmoddable files
     * @return array File permissions to set
     */
    public function get_chmod_array($runtime = false)
    {
        return array();
    }

    /**
     * Get the version of Composr this addon is for
     *
     * @return float Version number
     */
    public function get_version()
    {
        return cms_version_number();
    }

    /**
     * Get the addon category
     *
     * @return string The category
     */
    public function get_category()
    {
        return 'Admin Utilities';
    }

    /**
     * Get the addon author
     *
     * @return string The author
     */
    public function get_author()
    {
        return 'Chris Graham';
    }

    /**
     * Find other authors
     *
     * @return array A list of co-authors that should be attributed
     */
    public function get_copyright_attribution()
    {
        return array(
            'James Heinrich',
            'Allan Hansen',
        );
    }

    /**
     * Get the addon licence (one-line summary only)
     *
     * @return string The licence
     */
    public function get_licence()
    {
        return 'GPL';
    }

    /**
     * Get the description of the addon
     *
     * @return string Description of the addon
     */
    public function get_description()
    {
        return 'Detect the Height/Width/Length of video files when they are uploaded to the gallery.';
    }

    /**
     * Get a list of tutorials that apply to this addon
     *
     * @return array List of tutorials
     */
    public function get_applicable_tutorials()
    {
        return array();
    }

    /**
     * Get a mapping of dependency types
     *
     * @return array File permissions to set
     */
    public function get_dependencies()
    {
        return array(
            'requires' => array(
                'galleries',
            ),
            'recommends' => array(),
            'conflicts_with' => array()
        );
    }

    /**
     * Explicitly say which icon should be used
     *
     * @return URLPATH Icon
     */
    public function get_default_icon()
    {
        return 'themes/default/images/icons/48x48/menu/_generic_admin/component.png';
    }

    /**
     * Get a list of files that belong to this addon
     *
     * @return array List of files
     */
    public function get_file_list()
    {
        return array(
            'sources_custom/hooks/systems/addon_registry/getid3.php',
            'sources_custom/getid3/!delete any module you don\'t like, but check dependencies.txt/.htaccess',
            'sources_custom/getid3/.htaccess',
            'sources_custom/getid3/index.html',
            'sources_custom/getid3/!delete any module you don\'t like, but check dependencies.txt/index.html',
            'sources_custom/getid3/extension.cache.dbm.php',
            'sources_custom/getid3/extension.cache.mysql.php',
            'sources_custom/getid3/getid3.lib.php',
            'sources_custom/getid3/getid3.php',
            'sources_custom/getid3/module.misc.doc.php',
            'sources_custom/getid3/module.misc.exe.php',
            'sources_custom/getid3/module.misc.iso.php',
            'sources_custom/getid3/module.misc.msoffice.php',
            'sources_custom/getid3/module.misc.par2.php',
            'sources_custom/getid3/module.misc.pdf.php',
            'sources_custom/getid3/module.archive.gzip.php',
            'sources_custom/getid3/module.archive.rar.php',
            'sources_custom/getid3/module.archive.szip.php',
            'sources_custom/getid3/module.archive.tar.php',
            'sources_custom/getid3/module.archive.zip.php',
            'sources_custom/getid3/module.audio-video.asf.php',
            'sources_custom/getid3/module.audio-video.bink.php',
            'sources_custom/getid3/module.audio-video.flv.php',
            'sources_custom/getid3/module.audio-video.matroska.php',
            'sources_custom/getid3/module.audio-video.mpeg.php',
            'sources_custom/getid3/module.audio-video.nsv.php',
            'sources_custom/getid3/module.audio-video.quicktime.php',
            'sources_custom/getid3/module.audio-video.real.php',
            'sources_custom/getid3/module.audio-video.riff.php',
            'sources_custom/getid3/module.audio-video.swf.php',
            'sources_custom/getid3/module.audio.aac.php',
            'sources_custom/getid3/module.audio.ac3.php',
            'sources_custom/getid3/module.audio.au.php',
            'sources_custom/getid3/module.audio.avr.php',
            'sources_custom/getid3/module.audio.bonk.php',
            'sources_custom/getid3/module.audio.dss.php',
            'sources_custom/getid3/module.audio.dts.php',
            'sources_custom/getid3/module.audio.flac.php',
            'sources_custom/getid3/module.audio.la.php',
            'sources_custom/getid3/module.audio.lpac.php',
            'sources_custom/getid3/module.audio.midi.php',
            'sources_custom/getid3/module.audio.mod.php',
            'sources_custom/getid3/module.audio.monkey.php',
            'sources_custom/getid3/module.audio.mp3.php',
            'sources_custom/getid3/module.audio.mpc.php',
            'sources_custom/getid3/module.audio.ogg.php',
            'sources_custom/getid3/module.audio.optimfrog.php',
            'sources_custom/getid3/module.audio.rkau.php',
            'sources_custom/getid3/module.audio.shorten.php',
            'sources_custom/getid3/module.audio.tta.php',
            'sources_custom/getid3/module.audio.voc.php',
            'sources_custom/getid3/module.audio.vqf.php',
            'sources_custom/getid3/module.audio.wavpack.php',
            'sources_custom/getid3/module.graphic.bmp.php',
            'sources_custom/getid3/module.graphic.gif.php',
            'sources_custom/getid3/module.graphic.jpg.php',
            'sources_custom/getid3/module.graphic.pcd.php',
            'sources_custom/getid3/module.graphic.png.php',
            'sources_custom/getid3/module.graphic.svg.php',
            'sources_custom/getid3/module.graphic.tiff.php',
            'sources_custom/getid3/module.tag.apetag.php',
            'sources_custom/getid3/module.tag.id3v1.php',
            'sources_custom/getid3/module.tag.id3v2.php',
            'sources_custom/getid3/module.tag.lyrics3.php',
            'sources_custom/getid3/write.apetag.php',
            'sources_custom/getid3/write.id3v1.php',
            'sources_custom/getid3/write.id3v2.php',
            'sources_custom/getid3/write.lyrics3.php',
            'sources_custom/getid3/write.metaflac.php',
            'sources_custom/getid3/write.php',
            'sources_custom/getid3/write.real.php',
            'sources_custom/getid3/write.vorbiscomment.php',
        );
    }
}
