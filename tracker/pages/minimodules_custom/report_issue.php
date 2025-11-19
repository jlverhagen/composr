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
 * @package    cms_homesite_tracker
 */

i_solemnly_declare(I_UNDERSTAND_SQL_INJECTION | I_UNDERSTAND_XSS | I_UNDERSTAND_PATH_INJECTION);

if (!addon_installed('cms_homesite_tracker') || !addon_installed('cms_homesite')) {
    warn_exit(do_lang_tempcode('INTERNAL_ERROR', escape_html('82e2c878e1c55dfa835b2234cc823a30')));
}

// Do not allow reports from Guests due to excessive spam
if (is_guest()) {
    access_denied('NOT_AS_GUEST');
}

require_code('decision_tree');
require_code('cms_homesite_tracker');
require_code('cms_homesite');
require_lang('decision_tree');
require_lang('tracker');

if (addon_installed('captcha')) {
    require_code('captcha');
}

global $BASE_URL;
$BASE_URL = get_custom_base_url();

// Submit?
$type = get_param_string('type', 'browse');
if ($type == 'submit') {
    require_code('version2');

    $_category = post_param_string('category');
    $_severity = explode(':', post_param_string('severity'));
    $severity = $_severity[0];
    $summary = post_param_string('summary');
    $description = post_param_string('description');

    $addon = post_param_string('addon', 'core');
    $version = get_version_dotted__from_anything(post_param_string('version', ''));
    $additional_information = post_param_string('additional_information', '');
    $search = post_param_integer('search', 0);
    $search_tutorials = post_param_integer('search_tutorials', 0);
    $remote_access = post_param_integer('remote_access', 0);

    $steps_to_reproduce = '';
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'steps_to_reproduce_') !== 0) {
            continue;
        }

        if (trim($value) == '') {
            continue;
        }

        if (trim($steps_to_reproduce) != '') {
            $steps_to_reproduce .= "\n";
        }
        $steps_to_reproduce .= post_param_string($key);
    }

    // Map category values from the form to their category title language string code
    $categories = [
        'Core software / bundled addons / default theme' => 'TRACKER_CATALOGUE_CATEGORY_1',
        'Downloadable (non-bundled) addons or themes' => 'TRACKER_CATALOGUE_CATEGORY_2',
        'Content in a Documentation / Tutorial' => 'TRACKER_CATALOGUE_CATEGORY_3',
        'Homesite' => 'TRACKER_CATALOGUE_CATEGORY_4',
        'Custom code' => null, // Never gets added as an issue
    ];

    // Get category
    $category = $GLOBALS['SITE_DB']->query_select_value('catalogue_categories', 'id', ['c_name' => 'tracker', $GLOBALS['SITE_DB']->translate_field_ref('cc_title') => do_lang($categories[$_category])]);

    // Add confirmation tick boxes if applicable
    if ($search == 1) {
        if ($additional_information != '') {
            $additional_information .= "\n\n";
        }
        $additional_information .= 'I confirm I searched the tracker for an existing issue.';
    }
    if ($search_tutorials == 1) {
        if ($additional_information != '') {
            $additional_information .= "\n\n";
        }
        $additional_information .= 'I confirm I searched the tutorials for an existing one.';
    }
    if ($remote_access == 1) {
        if ($additional_information != '') {
            $additional_information .= "\n\n";
        }
        $additional_information .= 'I grant permission for the core developers to investigate this issue remotely on my site, in accordance with the server access policies ( ' . $BASE_URL . '/server-access.htm ), via the FTP credentials provided on my member profile (developers: you must exchange an e-mail contact with the user and send a digital copy [not just a link] of the server access policy for them to agree to via e-mail before accessing the server).';
    }

    // Create the tracker issue
    $tracker_id = create_tracker_issue(
        $version,
        $summary,
        $severity,
        $description,
        $additional_information,
        $addon,
        $category,
        null,
        $steps_to_reproduce,
    );

    // Inform the member it has been done with a redirect to it.
    $decision_tree = [
        'submit' => [
            'title' => 'Issue Submitted',
            'text' => 'Thank you for submitting an issue! Your issue is [url="#' . strval($tracker_id[1]) . '"]' . $BASE_URL . '/catalogues/entry/tracker-' . strval($tracker_id[1]) . '.htm[/url] on the tracker. You can click the issue number to be directed to it. Be sure to save or bookmark the page for future reference.' . "\n\n" . 'If you have any screenshots or relevant files to attach to the issue (such as errors and stack traces), you can do so in a follow-up comment on the issue.',
        ]
    ];

    $ob = object_factory('Source_decision_tree', false, [$decision_tree, 'submit'], true);
} else {
    $decision_tree = [
        'start' => [
            'title' => 'Report an Issue or Feature / Suggestion',
            'text' => 'Thank you for taking the time to report an issue or a feature / suggestion for ' . brand_name() . '. Your feedback is what helps improve the software and make it the best software it can be for everyone. This wizard will guide you through the process of making an issue. If you prefer, you can make an issue (entry) directly in the tracker catalogue. This wizard aims to simplify the process by asking questions specific to your selections.' . "\n\n" . 'Please refer to the relevant section of the [page="docs:tut-software-feedback"]providing feedback tutorial[/page] for guidance on making an effective report / issue. At any time, click the question mark next to a field for guidance on what to fill out.',
            'form_method' => 'POST',
            'questions' => [
                'search' => [
                    'label' => 'Searched the tracker for existing issues?',
                    'description' => 'Did you already search the tracker to see if your issue was already reported by someone else? You can do so from your Admin Zone dashboard in the version block (there is a link to view reported issues), or at ' . $BASE_URL . '/tracker . We encourage you do so, but we do not require it especially if the interface is overwhelming.',
                    'type' => 'tick',
                    'default' => '',
                    'options' => '',
                    'required' => true,
                ],
                'category' => [
                    'label' => 'Category',
                    'description' => 'Which of these best describes the category of the issue you are reporting or the request you are making?',
                    'type' => 'list',
                    'default' => 'Core software / bundled addons / default theme',
                    'default_list' => [
                        'Core software / bundled addons / default theme',
                        'Downloadable (non-bundled) addons or themes',
                        'Content in a Documentation / Tutorial',
                        'Homesite',
                        'Custom code',
                    ],
                    'options' => 'widget=radio',
                    'required' => true,
                ],
            ],
            'next' => [
                // Parameter, Value, Target
                ['category', 'Custom code', 'custom_code'],
                ['category', 'Core software / bundled addons / default theme', 'core_software'],
                ['category', 'Content in a Documentation / Tutorial', 'doc_issue'],
                ['category', 'Downloadable (non-bundled) addons or themes', 'nb_issue'],
                ['category', 'Homesite', 'site_issue'],
            ],
        ],

        'custom_code' => [
            'title' => 'Issues for custom code are not supported',
            'text' => 'We apologize, but the issue tracker is not for reporting issues with custom code which is not part of the core software or a non-bundled addon. Please consider [page=":partners"]hiring a developer[/page] for your needs.',
        ],

        'core_software' => [
            'expects_parameters' => [
                'category'
            ],
            'title' => 'Basic Issue Information (Core software)',
            'text' => 'Step 2 of 3: Please provide the following basic information about your issue.',
            'form_method' => 'POST',
            'questions' => [
                'addon' => [
                    'label' => 'Addon',
                    'description' => 'Choose the relevant addon for this issue. If you do not know, you can make a best guess; developers can always correct this later.',
                    'type' => 'addon',
                    'default' => '',
                    'options' => 'auto_sort=on',
                    'required' => true,
                ],
                'version' => [
                    'label' => 'Software version',
                    'description' => 'Please specify what version of ' . brand_name() . ' you are running (this can be found in your Admin Zone dashboard).',
                    'type' => 'short_text',
                    'default' => get_latest_version_dotted(),
                    'options' => '',
                    'required' => false,
                ],
                'severity' => [
                    'label' => 'Severity / Issue Type',
                    'description' => 'Please choose the type / severity of the issue you are reporting.',
                    'type' => 'list',
                    'default' => 'Feature / Request',
                    'default_list' => [
                        'feature: For feature requests and suggestions on improving the software',
                        'trivial: For typos and other issues that do not affect the operation of the software',
                        'minor: For issues that affect software operation but not to the point entire features are unusable',
                        'major: For issues that render entire features unusable or cause corruption to the site',
                        'security: For reporting security vulnerabilities in the software'
                    ],
                    'options' => 'widget=radio',
                    'required' => true,
                ],
            ],
            'next' => [
                // Parameter, Value, Target
                ['severity', 'feature: For feature requests and suggestions on improving the software', 'feature'],
                ['severity', 'trivial: For typos and other issues that do not affect the operation of the software', 'bug'],
                ['severity', 'minor: For issues that affect software operation but not to the point entire features are unusable', 'bug'],
                ['severity', 'major: For issues that render entire features unusable or cause corruption to the site', 'bug'],
                ['severity', 'security: For reporting security vulnerabilities in the software', 'security'],
            ],
        ],

        'nb_issue' => [
            'expects_parameters' => [
                'category'
            ],
            'title' => 'Basic Issue Information (Non-bundled addons)',
            'text' => 'Step 2 of 3: Please provide the following basic information about your issue.',
            'form_method' => 'POST',
            'notice' => [
                'The responsibility of implementing issues for a non-bundled addon falls on the author of the addon and not the core developers. Issues for non-bundled addons usually have lower priority than bundled ones.'
            ],
            'questions' => [
                'addon' => [
                    'label' => 'Addon',
                    'description' => 'Please choose the relevant non-bundled addon. For themes, and addons that are not listed on the homesite, leave this blank and specify the name in the summary (on the next step).',
                    'type' => 'addon',
                    'default' => '',
                    'options' => 'auto_sort=on',
                    'required' => false,
                ],
                'version' => [
                    'label' => 'Software version',
                    'description' => 'Please specify what version of ' . brand_name() . ' you are running (this can be found in your Admin Zone dashboard).',
                    'type' => 'short_text',
                    'default' => get_latest_version_dotted(),
                    'options' => '',
                    'required' => false,
                ],
                'severity' => [
                    'label' => 'Severity / Issue Type',
                    'description' => 'Please choose the type / severity of the issue you are reporting.',
                    'type' => 'list',
                    'default' => 'Feature / Request',
                    'default_list' => [
                        'feature: For feature requests and suggestions on improving a non-bundled addon',
                        'trivial: For typos and other issues that do not affect the operation of the addon',
                        'minor: For issues that affect addon operation but not to the point entire features are unusable',
                        'major: For issues that render entire features / addons unusable or cause corruption to the site',
                        'security: For reporting security vulnerabilities in an addon'
                    ],
                    'options' => 'widget=radio',
                    'required' => true,
                ],
            ],
            'next' => [
                // Parameter, Value, Target
                ['severity', 'feature: For feature requests and suggestions on improving a non-bundled addon', 'feature'],
                ['severity', 'trivial: For typos and other issues that do not affect the operation of the addon', 'bug'],
                ['severity', 'minor: For issues that affect addon operation but not to the point entire features are unusable', 'bug'],
                ['severity', 'major: For issues that render entire features / addons unusable or cause corruption to the site', 'bug'],
                ['severity', 'security: For reporting security vulnerabilities in an addon', 'security'],
            ],
        ],

        'site_issue' => [
            'expects_parameters' => [
                'category'
            ],
            'title' => 'Basic Issue Information (Homesite)',
            'text' => 'Step 2 of 3: Please provide the following basic information about your issue.',
            'form_method' => 'POST',
            'questions' => [
                'severity' => [
                    'label' => 'Severity / Issue Type',
                    'description' => 'Please choose the type / severity of the issue you are reporting.',
                    'type' => 'list',
                    'default' => 'Feature / Request',
                    'default_list' => [
                        'feature: For feature requests and suggestions on improving the homesite',
                        'trivial: For typos and other issues that do not affect the operation of the homesite',
                        'minor: For issues that affect the the homesite\'s operation but not to the point entire features are unusable',
                        'major: For issues that render entire features unusable or cause corruption to the homesite',
                        'security: For reporting security vulnerabilities in the homesite'
                    ],
                    'options' => 'widget=radio',
                    'required' => true,
                ],
            ],
            'next' => [
                // Parameter, Value, Target
                ['severity', 'feature: For feature requests and suggestions on improving the homesite', 'feature'],
                ['severity', 'trivial: For typos and other issues that do not affect the operation of the homesite', 'bug'],
                ['severity', 'minor: For issues that affect the the homesite\'s operation but not to the point entire features are unusable', 'bug'],
                ['severity', 'major: For issues that render entire features unusable or cause corruption to the homesite', 'bug'],
                ['severity', 'security: For reporting security vulnerabilities in the homesite', 'security'],
            ],
        ],

        'feature' => [
            'expects_parameters' => [
                'category',
                'severity'
            ],
            'title' => 'Feature / Request Details',
            'text' => 'Step 3 of 3: I will now ask you a few questions about your feature / request. If you need help understanding what to put in a field, click the ? icon.',
            'inform' => [
                'An issue will be created after you proceed from this screen. You can then include relevant uploads / files in a follow-up comment on the issue.'
            ],
            'notice' => [
                'You should put error messages or copied text in a \[code\] tag (or post as a screenshot later) to ensure the Comcode and Tempcode does not get processed.',
            ],
            'warn' => [
                'Do not ever submit account credentials or other secrets or keys in an issue.'
            ],
            'form_method' => 'POST',
            'questions' => [
                'summary' => [
                    'label' => 'Concise sentence / summary',
                    'description' => 'Please state your feature / request in one concise sentence; this will help developers quickly understand your request at a glance. For example, you might say "Implement support for the Doggy Biscuits API".',
                    'type' => 'short_text',
                    'default' => '',
                    'options' => '',
                    'required' => true,
                ],
                'description' => [
                    'label' => 'Describe your feature / request',
                    'description' => 'Elaborate your feature / request in more details here. What would you like to see implemented? How should it be implemented? What should it do? etc.',
                    'type' => 'long_trans',
                    'default' => '',
                    'options' => '',
                    'required' => true,
                ],
                'additional_information' => [
                    'label' => 'How will this benefit the software? + Additional Info',
                    'description' => 'Please provide any additional information about your request here. For example, you can elaborate on why you believe this feature / request will improve the overall ' . brand_name() . ' software for everyone.',
                    'type' => 'long_trans',
                    'default' => '',
                    'options' => '',
                    'required' => true,
                ],
            ],
            'needs_captcha' => ((addon_installed('captcha')) && (get_option('captcha_on_feedback') == '1') && (use_captcha())),
            'next' => build_url(['page' => 'report_issue', 'type' => 'submit'], get_module_zone('report_issue')),
        ],

        'bug' => [
            'expects_parameters' => [
                'category',
                'severity'
            ],
            'title' => 'Bug Report Details',
            'text' => 'Step 3 of 3: I will now ask you a few questions about your bug / issue report. If you need help understanding what to put in a field, click the ? icon.',
            'inform' => [
                'An issue will be created after you proceed from this screen. You can then include relevant uploads / files in a follow-up comment on the issue.'
            ],
            'notice' => [
                'You should put error messages or copied text in a \[code\] tag (or post as a screenshot later) to ensure the Comcode and Tempcode does not get processed.',
            ],
            'warn' => [
                'Do not ever submit account credentials or other secrets or keys in an issue.',
            ],
            'form_method' => 'POST',
            'questions' => [
                'summary' => [
                    'label' => 'Concise sentence / summary',
                    'description' => 'Please state the bug in one concise sentence; this will help developers quickly understand your issue at a glance. For example, you might say "Uploading an entry to a member gallery triggers a critical error".',
                    'type' => 'short_text',
                    'default' => '',
                    'options' => '',
                    'required' => true,
                ],
                'description' => [
                    'label' => 'Explain the bug / issue',
                    'description' => 'Elaborate on the bug / issue in more details here. What did you attempt to do? What did you expect to happen? What actually happened? What error messages did you get?',
                    'type' => 'long_trans',
                    'default' => '',
                    'options' => '',
                    'required' => true,
                ],
                'steps_to_reproduce' => [
                    'label' => 'How do you reproduce the bug / issue?',
                    'description' => 'If possible / applicable, please explain how one can reproduce this bug / issue. Please list steps in sequential order, one per box, and note any special details (such as config options that need to be set).',
                    'type' => 'short_trans_multi',
                    'default' => '',
                    'options' => '',
                    'required' => false,
                ],
                'additional_information' => [
                    'label' => 'Additional Info / Workarounds / Server Environment',
                    'description' => 'Please provide any additional information about the bug / issue here. For example, you can provide relevant non-sensitive details about your server environment... PHP version, web server and version, RAM/CPU, etc (not relevant if reporting a homesite issue). Or if you found a workaround, you can mention it here.',
                    'type' => 'long_trans',
                    'default' => '',
                    'options' => '',
                    'required' => false,
                ],
                'remote_access' => [
                    'label' => 'Allow remote access to my site for investigation (see ?)',
                    'description' => 'If this is an issue you think would need the developers to investigate remotely on your server, tick this box. By doing so, you agree to the [page="_SEARCH:server_access"]server access policy[/page]. And you should ensure, immediately after submitting your issue, that your FTP credentials on your member profile are up-to-date (they are encrypted so only active core developers can see them).',
                    'type' => 'tick',
                    'default' => '',
                    'options' => '',
                    'required' => true,
                ],
            ],
            'needs_captcha' => ((addon_installed('captcha')) && (get_option('captcha_on_feedback') == '1') && (use_captcha())),
            'next' => build_url(['page' => 'report_issue', 'type' => 'submit'], get_module_zone('report_issue')),
        ],

        'security' => [
            'expects_parameters' => [
                'category',
                'severity'
            ],
            'title' => 'Security Vulnerability Details',
            'text' => 'Step 3 of 3: I will now ask you a few questions about your security vulnerability report. If you need help understanding what to put in a field, click the ? icon.',
            'inform' => [
                'An issue will be created after you proceed from this screen. You can then include relevant uploads / files in a follow-up comment on the issue.'
            ],
            'notice' => [
                'Security issues will be reported to the tracker privately; only the staff can view security issues. You will be notified when your issue is resolved or if the developers need more information.',
                'You should put error messages or copied text in a \[code\] tag (or post as a screenshot later) to ensure the Comcode and Tempcode does not get processed.',
            ],
            'warn' => [
                'Please follow responsible practices for disclosing security vulnerabilities, located at ' . $BASE_URL . '/docs/tut-software-feedback.htm#title__46 . Do not publicly disclose the vulnerability anywhere until a confirmed patch has been released by the Core Development Team.',
                'Do not ever submit account credentials or other secrets or keys in an issue.',
            ],
            'form_method' => 'POST',
            'questions' => [
                'summary' => [
                    'label' => 'Concise sentence / summary',
                    'description' => 'Please state the security vulnerability in one concise sentence; this will help developers quickly understand your issue at a glance. For example, you might say "XSS injection vulnerability on the tickets creation page".',
                    'type' => 'short_text',
                    'default' => '',
                    'options' => '',
                    'required' => true,
                ],
                'description' => [
                    'label' => 'Explain the vulnerability',
                    'description' => 'Elaborate on the security vulnerability in more details here. What did you attempt to do? What did you expect to happen? What actually happened? What error messages did you get? How did the vulnerability affect the stability of your site?',
                    'type' => 'long_trans',
                    'default' => '',
                    'options' => '',
                    'required' => true,
                ],
                'steps_to_reproduce' => [
                    'label' => 'How do you expose / exploit this vulnerability?',
                    'description' => 'Please explain how one can reproduce / verify this security vulnerability. Please list steps in sequential order, one per box, and note any special details (such as config options that need to be set or that one must gain access to a privileged account first). This field is required for security vulnerability reports.',
                    'type' => 'short_trans_multi',
                    'default' => '',
                    'options' => '',
                    'required' => true,
                ],
                'additional_information' => [
                    'label' => 'Additional Info / Workarounds / Server Environment',
                    'description' => 'Please provide any additional information about the vulnerability here. For example, you can provide relevant non-sensitive details about your server environment... PHP version, web server and version, RAM/CPU, etc (not relevant if reporting a vulnerability with the homesite). Or you can provide workarounds to negate the security hole until it is patched.',
                    'type' => 'long_trans',
                    'default' => '',
                    'options' => '',
                    'required' => false,
                ],
                'remote_access' => [
                    'label' => 'Allow remote access to my site for investigation (see ?)',
                    'description' => 'If this is an issue you think would need the developers to investigate remotely on your server, tick this box. By doing so, you agree to the [page="_SEARCH:server_access"]server access policy[/page]. And you should ensure, immediately after submitting your issue, that your FTP credentials on your member profile are up-to-date (they are encrypted so only active core developers can see them).',
                    'type' => 'tick',
                    'default' => '',
                    'options' => '',
                    'required' => true,
                ],
            ],
            'needs_captcha' => ((addon_installed('captcha')) && (get_option('captcha_on_feedback') == '1') && (use_captcha())),
            'next' => build_url(['page' => 'report_issue', 'type' => 'submit'], get_module_zone('report_issue')),
        ],

        'doc_issue' => [
            'expects_parameters' => [
                'category'
            ],
            'title' => 'Basic Issue Information (Documentation)',
            'text' => 'Step 2 of 3: I will now ask a few quick questions so I can best guide you to the next screen for your issue.',
            'form_method' => 'POST',
            'questions' => [
                'severity' => [
                    'label' => 'Issue Type',
                    'description' => 'Please choose the type of the issue you are reporting. Note that for tutorial pages, major bugs and security holes do not apply. If there is a bug with the tutorial system itself, it should be reported under Downloadable (non-bundled) addons or themes.',
                    'type' => 'list',
                    'default' => '',
                    'default_list' => [
                        'feature: For suggesting new official tutorials or additions to existing ones',
                        'trivial: For reporting typos or inaccuracies in existing tutorials',
                        'minor: For reporting issues with tutorials not rendering properly',
                    ],
                    'options' => 'widget=radio',
                    'required' => true,
                ],
            ],
            'next' => [
                // Parameter, Value, Target
                ['severity', 'feature: For suggesting new official tutorials or additions to existing ones', 'doc_new'],
                ['severity', 'trivial: For reporting typos or inaccuracies in existing tutorials', 'doc_fix'],
                ['severity', 'minor: For reporting issues with tutorials not rendering properly', 'bug'],
            ],
        ],

        'doc_new' => [
            'expects_parameters' => [
                'category',
                'severity',
            ],
            'title' => 'Suggest a New Tutorial',
            'text' => 'Step 3 of 3: I will now ask you a few questions about your new tutorial suggestion. If you need help understanding what to put in a field, click the ? icon.',
            'inform' => [
                'Did you know? You can create your own off-site tutorials and link them to the tutorial index. Just go to ' . $BASE_URL . '/docs/tutorials.htm and scroll down to "Need better information?"',
                'An issue will be created after you proceed from this screen. You can then include relevant uploads / files in a follow-up comment on the issue.'
            ],
            'notice' => [
                'You should put error messages or copied text in a \[code\] tag (or post as a screenshot later) to ensure the Comcode and Tempcode does not get processed.',
            ],
            'warn' => [
                'Do not ever submit account credentials or other secrets or keys in an issue.'
            ],
            'form_method' => 'POST',
            'questions' => [
                'search_tutorials' => [
                    'label' => 'Did you search for an existing tutorial?',
                    'description' => 'Please tick this box to confirm you already searched the tutorials at ' . $BASE_URL . '/docs/tutorials.htm and did not find a tutorial that exists pertaining to what you are about to suggest.',
                    'type' => 'tick',
                    'default' => '',
                    'options' => '',
                    'required' => true,
                ],
                'summary' => [
                    'label' => 'Concise sentence / summary',
                    'description' => 'Please state your new tutorial request in one concise sentence; this will help developers quickly understand your issue at a glance. For example, you might say "New documentation on how to set-up LiteSpeed".',
                    'type' => 'short_text',
                    'default' => '',
                    'options' => '',
                    'required' => true,
                ],
                'description' => [
                    'label' => 'Description of Tutorial / Additions',
                    'description' => 'Elaborate on what information you would like to see in this new tutorial.',
                    'type' => 'long_trans',
                    'default' => '',
                    'options' => '',
                    'required' => true,
                ],
                'additional_information' => [
                    'label' => 'How will this benefit the community?',
                    'description' => 'Please let us know why you feel this new tutorial will benefit the ' . brand_name() . ' community at large (why it should be an official tutorial instead of one you can make yourself off-site and add via off-site links).',
                    'type' => 'long_trans',
                    'default' => '',
                    'options' => '',
                    'required' => true,
                ],
            ],
            'needs_captcha' => ((addon_installed('captcha')) && (get_option('captcha_on_feedback') == '1') && (use_captcha())),
            'next' => build_url(['page' => 'report_issue', 'type' => 'submit'], get_module_zone('report_issue')),
        ],

        'doc_fix' => [
            'expects_parameters' => [
                'category',
                'severity',
            ],
            'title' => 'Report an Error in a Tutorial',
            'text' => 'Step 3 of 3: I will now ask you a few questions about your tutorial issue. If you need help understanding what to put in a field, click the ? icon.',
            'inform' => [
                'An issue will be created after you proceed from this screen. You can then include relevant uploads / files in a follow-up comment on the issue.'
            ],
            'notice' => [
                'You should put error messages or copied text in a \[code\] tag (or post as a screenshot later) to ensure the Comcode and Tempcode does not get processed.',
            ],
            'warn' => [
                'Do not ever submit account credentials or other secrets or keys in an issue.'
            ],
            'form_method' => 'POST',
            'questions' => [
                'summary' => [
                    'label' => 'Concise sentence / summary',
                    'description' => 'Please state your issue in one concise sentence; this will help developers quickly understand your issue at a glance. For example, you might say "Minimum supported PHP version indicated in tutorial is wrong".',
                    'type' => 'short_text',
                    'default' => '',
                    'options' => '',
                    'required' => true,
                ],
                'description' => [
                    'label' => 'Explain the tutorial error(s) and their corrections',
                    'description' => 'Explain what error(s) you found in the tutorial and to what they should be corrected (if you can find the correct information).',
                    'type' => 'long_trans',
                    'default' => '',
                    'options' => '',
                    'required' => true,
                ],
                'additional_information' => [
                    'label' => 'Additional Information',
                    'description' => 'Please provide any additional information you have pertaining to this issue, if applicable.',
                    'type' => 'long_trans',
                    'default' => '',
                    'options' => '',
                    'required' => false,
                ],
            ],
            'needs_captcha' => ((addon_installed('captcha')) && (get_option('captcha_on_feedback') == '1') && (use_captcha())),
            'next' => build_url(['page' => 'report_issue', 'type' => 'submit'], get_module_zone('report_issue')),
        ],
    ];

    $ob = object_factory('Source_decision_tree', false, [$decision_tree, 'start'], true);
}

$tpl = $ob->run();
$tpl->evaluate_echo();
