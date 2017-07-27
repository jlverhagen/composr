<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2016

 See text/EN/licence.txt for full licencing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    testing_platform
 */

/**
 * Composr test case class (unit testing).
 */
class cms_test_case extends WebTestCase
{
    public $site_closed;

    public function setUp()
    {
        // Make sure the site is open
        $this->site_closed = get_option('site_closed');
        require_code('config2');
        set_option('site_closed', '0', 0);

        if (php_function_allowed('set_time_limit')) {
            @set_time_limit(0);
        }
    }

    public function tearDown()
    {
        if ($this->site_closed !== null) {
            set_option('site_closed', $this->site_closed, 0);
        }
    }

    public function get($url, $parameters = null)
    {
        $parts = array();
        if ((preg_match('#([' . URL_CONTENT_REGEXP . ']*):([' . URL_CONTENT_REGEXP . ']+|[^/]|$)((:(.*))*)#', $url, $parts) != 0) && ($parts[1] != 'mailto')) { // Specially encoded page-link. Complex regexp to make sure URLs do not match
            $real_url = page_link_to_url($url);

            $ret = parent::get($real_url, $parameters);
        } else {
            $ret = parent::get($url, $parameters);
        }

        require_code('files');

        // Save, so we can run webstandards checker on it later
        $path = get_file_base() . '/_tests/html_dump/' . get_class($this);
        if (!file_exists($path)) {
            mkdir($path, 0777);
        }
        $content = $this->_browser->getContent();
        cms_file_put_contents_safe($path . '/' . url_to_filename($url) . '.htm.tmp', $content, FILE_WRITE_FIX_PERMISSIONS);

        // Save the text so we can run through Word's grammar checker
        $text_content = $content;
        $text_content = preg_replace('#<[^>]* title="([^"]+)"<[^>]*>#U', '\\1', $text_content);
        $text_content = preg_replace('#<[^>]* alt="([^"]+)"<[^>]*>#U', '\\1', $text_content);
        $text_content = preg_replace('#<style[^>]*>.*</style>#Us', '', $text_content);
        $text_content = preg_replace('#<script[^>]*>.*</script>#Us', '', $text_content);
        $text_content = preg_replace('#<[^>]*>#U', '', $text_content);
        $text_content = preg_replace('#\s\s+#', '. ', $text_content);
        $text_content = str_replace('&ndash;', '-', $text_content);
        $text_content = str_replace('&mdash;', '-', $text_content);
        $text_content = str_replace('&hellip;', '...', $text_content);
        $text_content = @html_entity_decode($text_content, ENT_QUOTES);
        cms_file_put_contents_safe($path . '/' . url_to_filename($url) . '.txt.tmp', $text_content, FILE_WRITE_FIX_PERMISSIONS);

        return $ret;
    }

    public function establish_admin_session()
    {
        global $MEMBER_CACHED;
        require_code('users_active_actions');
        $MEMBER_CACHED = restricted_manually_enabled_backdoor();

        $this->dump($this->_browser->getContent());
    }
}
