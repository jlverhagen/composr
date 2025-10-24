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
 * @package    composr_tutorials
 */

/**
 * Hook class.
 */
class Hook_snippet_tutorial_box
{
    /**
     * Run function for snippet hooks. Generates HTML to insert into a page using AJAX.
     *
     * @return Tempcode The snippet
     */
    public function run() : object
    {
        if (!addon_installed('composr_tutorials')) {
            return new Tempcode();
        }

        require_code('tutorials');

        $tutorial_name = get_param_string('tutorial_name');

        $metadata = get_tutorial_metadata($tutorial_name);
        $_tutorial = templatify_tutorial($metadata, false);

        return do_template('TUTORIAL_BOX', $_tutorial);
    }
}
