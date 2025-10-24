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
 * @package    testing_platform
 */

/**
 * Composr test case class (unit testing).
 */
class antispam_test_set extends cms_test_case
{
    public function setUp()
    {
        parent::setUp();

        require_code('antispam');
    }

    public function testHeuristics()
    {
        // NOTE: Do not forget to manually test the heavy pasting heuristic as it is not covered in this test; paste a bunch of stuff in a form and see if post_data gets paste added to it.

        $_POST['foo_alien_code'] = '[link]http://example.com[/link] <a href="http://example.com">foo</a>';
        $_POST['foo_autonomous'] = '[font="Times New Roman"]foo[/font]';
        set_option('spam_heuristic_country', 'IN');
        $_GET['keep_country'] = 'IN';
        unset($_SERVER['HTTP_ACCEPT']);
        $_POST['foo_keywords'] = 'Foo ViagrA Bar';
        $_SERVER['HTTP_USER_AGENT'] = 'Spambot';

        require_code('antispam');
        list($confidence, $scoring) = calculation_internal_heuristic_confidence();

        $this->assertTrue(strpos($scoring, 'alien_code') !== false);
        $this->assertTrue(strpos($scoring, 'autonomous') !== false);
        $this->assertTrue(strpos($scoring, 'country') !== false);
        if (!is_cli()) {
            $this->assertTrue(strpos($scoring, 'header_absence') !== false);
        }
        $this->assertTrue(strpos($scoring, 'keywords') !== false);
        $this->assertTrue(strpos($scoring, 'links') !== false);
        $this->assertTrue(strpos($scoring, 'user_agents') !== false);

        if (is_guest()) {
            $this->assertTrue(strpos($scoring, 'guest') !== false);
        } else {
            $this->assertTrue(strpos($scoring, 'guest') === false);
        }
    }

    public function testRBL()
    {
        list($result) = check_rbl('rbl.efnetrbl.org', '127.0.0.1');
        $this->assertTrue($result != ANTISPAM_RESPONSE_ERROR);
    }

    public function testHTTPBL()
    {
        // Disabled by default as it requires a key.
        /*
        require_code('antispam');
        $key = '';

        $prev_stale = get_option('spam_stale_threshold');
        set_option('spam_stale_threshold', '20', 0);

        // Arrays of HTTPBL IP query, expected ANTISPAM_RESPONSE_*, expected confidence score (float, 0.0 - 1.0).
        $tests = [
            ['127.1.1.0', ANTISPAM_RESPONSE_UNLISTED, null], // Test unlisted
            ['127.1.1.1', ANTISPAM_RESPONSE_ACTIVE, ((1.0 / 255.0) * 4.0)], // Test threat type 1
            ['127.1.1.2', ANTISPAM_RESPONSE_ACTIVE, ((1.0 / 255.0) * 4.0)], // Test threat type 2
            ['127.1.1.4', ANTISPAM_RESPONSE_ACTIVE, ((1.0 / 255.0) * 4.0)], // Test threat type 4
            ['127.1.40.1', ANTISPAM_RESPONSE_ACTIVE, ((40.0 / 255.0) * 4.0)], // Test threat level 40
            ['127.1.80.1', ANTISPAM_RESPONSE_ACTIVE, ((80.0 / 255.0) * 4.0)], // Test threat level 80
            ['127.10.1.1', ANTISPAM_RESPONSE_ACTIVE, ((1.0 / 255.0) * 4.0)], // Test 10 days old
            ['127.40.1.1', ANTISPAM_RESPONSE_STALE, null], // Test 40 days old
        ];
        foreach ($tests as $test) {
            $spam_check = check_rbl($key . '.*.dnsbl.httpbl.org', $test[0]);
            $this->assertTrue(($spam_check[0] == $test[1]), 'Expected ' . $test[0] . ' test to return constant ' . strval($test[1]) . ' but instead got ' . strval($spam_check[0]));
            if (($test[2] === null) && ($spam_check[1] !== null)) {
                $this->assertTrue(false, 'Expected ' . $test[0] . ' test to return unlisted but instead got a confidence of ' . float_to_raw_string($spam_check[1]));
            } elseif (($test[2] !== null) && ($spam_check[1] === null)) {
                $this->assertTrue(($spam_check[1] == $test[2]), 'Expected ' . $test[0] . ' test to return a confidence of ' . float_to_raw_string($test[2]) . ' but instead got unlisted');
            } elseif (($test[2] !== null) && ($spam_check[1] !== null)) {
                $this->assertTrue(($spam_check[1] == $test[2]), 'Expected ' . $test[0] . ' test to return a confidence of ' . float_to_raw_string($test[2]) . ' but instead got ' . float_to_raw_string($spam_check[1]));
            }
        }

        set_option('spam_stale_threshold', $prev_stale, 0);
        */

        $message = 'testHTTPBL needs manually enabled / tested; disabled by default as it requires a key.';
        $this->dump($message, 'INFO:');
    }

    public function testStopForumSpam()
    {
        list($result) = _check_stopforumspam('127.0.0.1');
        $this->assertTrue($result != ANTISPAM_RESPONSE_ERROR);
    }

    public function testTornevallSubmit()
    {
        $this->assertTrue(is_string(http_get_contents('https://api.tornevall.net/', ['timeout' => 20.0, 'trigger_error' => false])), 'Failed to call the Tornevall API.'); // Very rough, at least tells us URL still exists
    }

    public function testStopForumSpamSubmit()
    {
        $this->assertTrue(is_string(http_get_contents('https://www.stopforumspam.com/add.php', ['timeout' => 20.0, 'trigger_error' => false])), 'Failed to call the Stop Forum Spam API add endpoint'); // Very rough, at least tells us URL still exists
    }

    public function testBayes()
    {
        if (!addon_installed('bayes_antispam') || !addon_installed('bayes_common')) {
            $this->assertTrue(false, 'This test requires the bayes_common and bayes_antispam addons.');
            return;
        }

        require_code('bayes');
        require_code('files_spreadsheets_read');

        $table = uniqid('t_spam_', false);

        $GLOBALS['SITE_DB']->create_table($table, [
            'id' => '*AUTO',
            't_id' => 'SHORT_TEXT',
            't_lang' => 'LANGUAGE_NAME',
            't_category' => 'ID_TEXT',
            't_count' => 'REAL',
            't_last_date_and_time' => 'TIME',
        ]);

        $model = new CMS_Bayes_classifier($GLOBALS['SITE_DB'], $table, 'EN', 0.2);

        // Train our data, but only the first 500 lines as it takes a while to train
        $sheet_reader = spreadsheet_open_read(get_file_base() . '/_tests/assets/spreadsheets/spam.csv');
        $i = 0;
        do {
            $line = $sheet_reader->read_row();
            if ($line === false) {
                break;
            }

            $model->train($line['v2'], [$line['v1']]);
            $i++;
        } while (($line !== false) && ($i < 500));

        // Spam
        $prediction_a = $model->predict('To learn more, click this link!');
        $prediction_b = $model->predict('You just won a free phone');
        $prediction_c = $model->predict('You are entitled to a $100 gift card');
        $this->assertTrue(($prediction_a['spam'] >= 0.9), 'Expected prediction A to almost certainly be spam, but got ' . serialize($prediction_a));
        $this->assertTrue(($prediction_b['spam'] >= 0.9), 'Expected prediction B to almost certainly be spam, but got ' . serialize($prediction_b));
        $this->assertTrue(($prediction_c['spam'] >= 0.9), 'Expected prediction C to almost certainly be spam, but got ' . serialize($prediction_c));

        // Explicit ham
        $prediction_d = $model->predict('I will see you in 5 minutes');
        $prediction_e = $model->predict('I think my team really sucks this year; the quarterback got sacked a million times.');
        $prediction_f = $model->predict('Where did u get tat pasta bowl?');
        $this->assertTrue(($prediction_d['ham'] >= 0.9), 'Expected prediction D to almost certainly be ham, but got ' . serialize($prediction_d));
        $this->assertTrue(($prediction_e['ham'] >= 0.9), 'Expected prediction E to almost certainly be ham, but got ' . serialize($prediction_e));
        $this->assertTrue(($prediction_f['ham'] >= 0.9), 'Expected prediction F to almost certainly be ham, but got ' . serialize($prediction_f));

        // Implicit ham with a few spam words to try tripping up the model
        $prediction_g = $model->predict('I need to call my Mom before she gets upset with me');
        $prediction_h = $model->predict('My mp3 player died today');
        $prediction_i = $model->predict('Do u really think someone as ugly as me can go out on a date?');
        $this->assertTrue(($prediction_g['ham'] >= 0.8), 'Expected prediction G to probably be ham, but got ' . serialize($prediction_g));
        $this->assertTrue(($prediction_h['ham'] >= 0.8), 'Expected prediction H to probably be ham, but got ' . serialize($prediction_h));
        $this->assertTrue(($prediction_i['ham'] >= 0.8), 'Expected prediction I to probably be ham, but got ' . serialize($prediction_i));

        // Spam, but with the words garbled up to trick the model
        $prediction_j = $model->predict('You will receive many many happy returns on your mobile ringtone investment');
        $prediction_k = $model->predict('Play our quiz and win a lifetime trip for you and your partner!');
        $prediction_l = $model->predict('Called you, we tried. Our customer service line, you call.'); // Who let Yoda in the test suite? ;)
        $this->assertTrue(($prediction_j['spam'] > 0.7), 'Expected prediction J to probably be spam, but got ' . serialize($prediction_j));
        $this->assertTrue(($prediction_k['spam'] > 0.7), 'Expected prediction K to probably be spam, but got ' . serialize($prediction_k));
        $this->assertTrue(($prediction_l['spam'] > 0.7), 'Expected prediction L to probably be spam, but got ' . serialize($prediction_l));

        if ($this->debug) {
            var_dump($prediction_a, $prediction_b, $prediction_c, $prediction_d, $prediction_e, $prediction_f, $prediction_g, $prediction_h, $prediction_i, $prediction_j, $prediction_k, $prediction_l);
        } else {
            $GLOBALS['SITE_DB']->drop_table_if_exists($table);
            $GLOBALS['SITE_DB']->query_delete('bayes_doc_counts', ['b_table' => $table]);
        }
    }
}
