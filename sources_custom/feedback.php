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
 * @package    custom_ratings
 */

/**
 * Standard code module initialisation function.
 *
 * @ignore
 */
function init__feedback()
{
    if (!addon_installed('custom_ratings')) {
        return;
    }

    define('MAX_LIKES_TO_SHOW', 20);

    define('RATING_TYPE__STAR_CHOICE', 0);
    define('RATING_TYPE__LIKE_DISLIKE', 1);

    global $RATINGS_STRUCTURE;
    $RATINGS_STRUCTURE = [
        'catalogue_entry__links' => [
            RATING_TYPE__LIKE_DISLIKE,
            [
                '' => '',
            ],
        ],
        'images' => [
            RATING_TYPE__STAR_CHOICE,
            [
                '' => 'General',
                'scenery' => 'Scenery',
                'quality' => 'Quality',
                'art' => 'Artiness',
            ],
        ],
    ];

    global $REVIEWS_STRUCTURE;
    $REVIEWS_STRUCTURE = [
        'news' => [
            'Informative',
            'Insightful',
        ],
    ];
}
