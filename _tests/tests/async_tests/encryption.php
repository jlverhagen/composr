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
class encryption_test_set extends cms_test_case
{
    public function testEncryptionOpenSSL()
    {
        require_code('encryption');

        if (!is_encryption_available()) {
            $this->assertTrue(false, 'openssl needed');
            return;
        }

        set_option('encryption_key', get_file_base() . '/_tests/assets/encryption/public.pem');
        set_option('decryption_key', get_file_base() . '/_tests/assets/encryption/private.pem');

        $in = 'test';
        $passphrase = 'test';

        $out = encrypt_data($in);
        $this->assertTrue($out != $in);

        $this->assertTrue(is_data_encrypted($out));

        $cycled = decrypt_data($out, $passphrase);
        $this->assertTrue($cycled == $in);
    }
}
