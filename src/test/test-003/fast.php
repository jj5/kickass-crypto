#!/usr/bin/env php
<?php

/************************************************************************************************\
*                                                                                                *
*  ____  __.__        __                           _________                        __           *
* |    |/ _|__| ____ |  | _______    ______ ______ \_   ___ \_______ ___.__._______/  |_  ____   *
* |      < |  |/ ___\|  |/ /\__  \  /  ___//  ___/ /    \  \/\_  __ <   |  |\____ \   __\/  _ \  *
* |    |  \|  \  \___|    <  / __ \_\___ \ \___ \  \     \____|  | \/\___  ||  |_> >  | (  <_> ) *
* |____|__ \__|\___  >__|_ \(____  /____  >____  >  \______  /|__|   / ____||   __/|__|  \____/  *
*         \/       \/     \/     \/     \/     \/          \/        \/     |__|                 *
*                                                                                                *
*                                                                                        By jj5  *
*                                                                                                *
\************************************************************************************************/

/**
 * 2023-03-30 jj5 - this test takes the various errors for a spin, all code paths which should
 * result in an error should be exercised... the code paths which will run quickly are in this
 * test, fast.php, other tests which include a delay should be added in slow.php if necessary.
 *
 * @link https://github.com/jj5/kickass-crypto
 */

//define( 'DEBUG', true );

require_once __DIR__ . '/../../../inc/test-host.php';
require_once __DIR__ . '/lib/include.php';

function get_ignored_errors() {

  // 2023-04-03 jj5 - this function is defined to return a list of all errors which are defined
  // in the library but which are not tested for by this test. The reasons for this might vary,
  // but at the moment there are three errors which can no longer happen, but for which tests
  // are still done, because I didn't want to remove code that was already done and expressed
  // some of the intent, also if a bug is introduced maybe these errors which "can't happen" will
  // actually happen, but I can't test for them at the moment because... they can't happen.

  return [
    KICKASS_CRYPTO_ERROR_IV_LENGTH_INVALID_2,
    KICKASS_CRYPTO_ERROR_TAG_LENGTH_INVALID_2,
    KICKASS_CRYPTO_ERROR_CIPHERTEXT_INVALID_2,
  ];

}

function run_test() {

  test_error(
    KICKASS_CRYPTO_ERROR_JSON_ENCODING_FAILED,
    function() {
      return new class extends TestCrypto {
        public function test() {
          return $this->data_encode( true, $this->get_data_encoding() );
        }
        protected function do_php_json_last_error( ) {
          return 123;
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_JSON_ENCODING_FAILED_2,
    function() {
      return new class extends TestCrypto {
        public function test() {
          return $this->data_encode( true, $this->get_data_encoding() );
        }
        protected function do_php_json_encode( $value, $flags, $depth = 512 ) {
          return false;
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_JSON_ENCODING_FAILED_3,
    function() {
      return new class extends TestCrypto {
        public function test() {
          return $this->data_encode( true, $this->get_data_encoding() );
        }
        protected function do_php_json_encode( $value, $flags, $depth = 512 ) {
          throw new Exception( 'fail' );
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_JSON_ENCODING_FAILED_4,
    function() {
      return new class extends TestCrypto {
        public function test() {
          return $this->data_encode( true, $this->get_data_encoding() );
        }
        protected function do_json_encode( $value ) {
          throw new Exception( 'fail' );
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_PHPS_ENCODING_FAILED,
    function() {
      return new class extends TestCrypto {
        public function test() {
          return $this->data_encode( true, $this->get_data_encoding() );
        }
        protected function do_get_data_encoding() {
          return KICKASS_CRYPTO_DATA_ENCODING_PHPS;
        }
        protected function do_get_config_phps_enable( $default ) { return true; }
        protected function do_php_serialize( $value ) {
          return false;
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_PHPS_ENCODING_FAILED_2,
    function() {
      return new class extends TestCrypto {
        public function test() {
          return $this->data_encode( true, $this->get_data_encoding() );
        }
        protected function do_get_data_encoding() {
          return KICKASS_CRYPTO_DATA_ENCODING_PHPS;
        }
        protected function do_get_config_phps_enable( $default ) { return true; }
        protected function do_php_serialize( $value ) {
          throw new Exception( 'fail' );
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_PHPS_ENCODING_FAILED_3,
    function() {
      return new class extends TestCrypto {
        public function test() {
          return $this->data_encode( true, $this->get_data_encoding() );
        }
        protected function do_get_data_encoding() {
          return KICKASS_CRYPTO_DATA_ENCODING_PHPS;
        }
        protected function do_get_config_phps_enable( $default ) { return true; }
        protected function do_phps_encode( $value ) {
          throw new Exception( 'fail' );
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_PHPS_ENCODING_DISABLED,
    function() {
      return new class extends TestCrypto {
        public function test() {
          return $this->do_encrypt( true );
        }
        protected function do_get_config_data_encoding( $default ) {
          return KICKASS_CRYPTO_DATA_ENCODING_PHPS;
        }
        protected function do_is_valid_data_encoding( $data_encoding ) { return false; }
        protected function do_get_config_phps_enable( $default ) { return false; }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_PHPS_ENCODING_DISABLED_2,
    function() {
      return new class extends TestCrypto {
        public function test() {
          return $this->phps_encode( true );
        }
        protected function do_get_data_encoding() {
          return KICKASS_CRYPTO_DATA_ENCODING_PHPS;
        }
        protected function do_get_config_phps_enable( $default ) { return false; }
          protected function do_php_serialize( $value ) {
            return false;
          }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_JSON_DECODING_FAILED,
    function() {
      return new class extends TestCrypto {
        public function test() {
          return $this->data_decode( 'true' );
        }
        protected function do_json_decode( $json, &$is_false ) {
          throw new Exception( 'fail' );
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_JSON_DECODING_FAILED_2,
    function() {
      return new class extends TestCrypto {
        public function test() {
          return $this->do_json_decode( 'true', $is_false );
        }
        protected function do_php_json_last_error() { return 123; }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_JSON_DECODING_FAILED_3,
    function() {
      return new class extends TestCrypto {
        public function test() {
          return $this->data_decode( 'true' );
        }
        protected function do_php_json_decode( $json, $associative, $depth, $flags ) {
          return false;
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_JSON_DECODING_FAILED_4,
    function() {
      return new class extends TestCrypto {
        public function test() {
          return $this->data_decode( 'true' );
        }
        protected function do_php_json_decode( $json, $associative, $depth, $flags ) {
          throw new Exception( 'fail' );
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_DATA_ENCODING_FAILED,
    function() {
      return new class extends ValidCrypto {
        public function test() {
          return $this->encrypt( 'test' );
        }
        protected function do_data_encode( $input, $data_encoding ) {
          return false;
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_DATA_ENCODING_FAILED_2,
    function() {
      return new class extends TestCrypto {
        public function test() {
          return $this->data_encode( true, $this->get_data_encoding() );
        }
        protected function do_data_encode( $input, $data_encoding ) {
          throw new \Exception( 'fail' );
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_DATA_ENCODING_FAILED_3,
    function() {
      return new class extends TestCrypto {
        public function test() {
          return $this->do_data_encode( 'input', 'xxxx' );
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_DATA_ENCODING_FAILED_4,
    function() {
      return new class extends TestCrypto {
        private $count = 0;
        public function test() {
          define( 'KICKASS_CRYPTO_TEST_DATA_ENCODE', true );
          return $this->do_data_encode( 'input', false );
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_PHPS_DECODING_FAILED,
    function() {
      return new class extends TestCrypto {
        public function test() {
          return $this->phps_decode( 'true', $is_false );
        }
        protected function do_phps_decode( $input, &$is_false ) {
          throw new Exception( 'fail' );
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_PHPS_DECODING_FAILED_2,
    function() {
      return new class extends TestCrypto {
        public function test() {
          return $this->phps_decode( 'true', $is_false );
        }
        protected function do_php_unserialize( $input ) {
          return false;
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_MESSAGE_ENCODING_FAILED,
    function() {
      return new class extends TestCrypto {
        public function test() {
          return $this->message_encode( 'whatever' );
        }
        protected function do_message_encode( $binary ) {
          throw new Exception( 'fail' );
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_MESSAGE_ENCODING_FAILED_2,
    function() {
      return new class extends TestCrypto {
        public function test() {
          return $this->do_message_encode( 'whatever' );
        }
        protected function do_php_base64_encode( $binary ) { return 123; }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_MESSAGE_ENCODING_FAILED_3,
    function() {
      return new class extends TestCrypto {
        public function test() {
          return $this->do_message_encode( 'whatever' );
        }
        protected function do_get_const_data_format() { return 'Z'; }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_MESSAGE_ENCODING_FAILED_4,
    function() {
      return new class extends TestCrypto {
        public function test() {
          return $this->do_message_encode( 'whatever' );
        }
        protected function do_php_base64_encode( $binary ) { return ''; }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_MESSAGE_DECODING_FAILED,
    function() {
      return new class extends TestCrypto {
        public function test() {
          return $this->message_decode( 'whatever' );
        }
        protected function do_message_decode( $binary ) {
          throw new Exception( 'fail' );
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_DATA_DECODING_FAILED_2,
    function() {
      return new class extends TestCrypto {
        public function test() {
          return $this->data_decode( 'true' );
        }
        protected function do_data_decode( $json, $data_encoding, &$is_false ) {
          throw new \Exception( 'fail' );
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_DATA_DECODING_FAILED_3,
    function() {
      return new class extends TestCrypto {
        public function test() {
          return $this->data_decode( 'true', 'invalid' );
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_DATA_DECODING_FAILED_4,
    function() {
      return new class extends TestCrypto {
        public function test() {
          define( 'KICKASS_CRYPTO_TEST_DATA_DECODE', true );
          return $this->data_decode( 'true', false );
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_MESSAGE_ENCODING_INVALID,
    function() {
      return new class extends TestCrypto {
        public function test() {
          return $this->message_decode( 'invalid' );
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_MESSAGE_ENCODING_UNKNOWN,
    function() {
      return new class extends TestCrypto {
        public function test() {
          return $this->message_decode( 'KA2/invalid' );
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_BASE64_DECODING_FAILED,
    function() {
      return new class extends TestCrypto {
        public function test() {
          return $this->message_decode( 'XKA0/!@#$%^&*()_' );
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_BASE64_DECODING_FAILED,
    function() {
      return new class extends TestCrypto {
        public function test() {
          return $this->message_decode( 'XKA0/123=' );
        }
        protected function do_php_base64_decode( $input, $strict ) { return false; }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_BASE64_DECODING_FAILED_2,
    function() {
      return new class extends TestCrypto {
        public function test() {
          return $this->message_decode( 'XKA0/123=' );
        }
        protected function do_php_base64_decode( $input, $strict ) { return null; }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_BASE64_DECODING_FAILED_2,
    function() {
      return new class extends TestCrypto {
        public function test() {
          return $this->message_decode( 'XKA0/123=' );
        }
        protected function do_php_base64_decode( $input, $strict ) { return ''; }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_BASE64_DECODING_FAILED_2,
    function() {
      return new class extends TestCrypto {
        public function test() {
          return $this->message_decode( 'XKA0/123=' );
        }
        protected function do_php_base64_decode( $input, $strict ) { return []; }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_BASE64_DECODING_FAILED_2,
    function() {
      return new class extends TestCrypto {
        public function test() {
          return $this->message_decode( 'XKA0/123=' );
        }
        protected function do_php_base64_decode( $input, $strict ) { return 0; }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_CANNOT_ENCRYPT_FALSE,
    function() {
      return new class extends TestCrypto {
        public function test() {
          return $this->encrypt( false );
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_PASSPHRASE_MISSING,
    function() {
      return new class extends TestCrypto {
        public function test() {
          return $this->do_encrypt( 'test' );
        }
        protected function do_get_passphrase_list() { return []; }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_PASSPHRASE_LENGTH_INVALID,
    function() {
      return new class extends TestCrypto {
        public function test() {
          return $this->do_encrypt( 'test' );
        }
        protected function do_get_passphrase_list() { return [ 'invalid' ]; }
        protected function do_get_encryption_passphrase() { return 'invalid'; }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_NO_VALID_KEY,
    function() {
      return new class extends TestCrypto {
        public function test() {
          return $this->decrypt( 'XKA0/test' );
        }
        protected function do_get_passphrase_list() { return [ 'invalid' ]; }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_CHUNK_SIZE_INVALID,
    function() {
      return new class extends TestCrypto {
        public function test() {
          return $this->do_encrypt( 'test' );
        }
        protected function do_get_config_chunk_size( $default ) {
          return -123;
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_CHUNK_SIZE_INVALID,
    function() {
      return new class extends TestCrypto {
        public function test() {
          return $this->do_encrypt( 'test' );
        }
        protected function do_get_config_chunk_size( $default ) {
          return KICKASS_CRYPTO_DEFAULT_CHUNK_SIZE_MAX + 1;
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_BINARY_LENGTH_INVALID,
    function() {
      return new class extends ValidCrypto {
        public function test() {
          $binary = str_repeat(
            '0',
            $this->get_const_openssl_iv_length() - 1
          );
          return $this->parse_binary( $binary, $iv, $ciphertext, $tag );
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_BINARY_LENGTH_INVALID,
    function() {
      return new class extends ValidCrypto {
        public function test() {
          return $this->parse_binary( 'invalid', $iv, $ciphertext, $tag );
        }
        protected function do_php_openssl_encrypt(
          $plaintext,
          $cipher,
          $passphrase,
          $options,
          $iv,
          &$tag
        ) {
          $tag = 'invalid';
          return false;
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_IV_LENGTH_INVALID,
    function() {
      return new class extends ValidCrypto {
        public function test() {
          $passphrase = $this->get_encryption_passphrase();
          return $this->do_encrypt_string( 'test', $passphrase );
        }
        protected function do_php_random_bytes( $length ) {
          return '123';
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_TAG_LENGTH_INVALID,
    function() {
      return new class extends ValidCrypto {
        public function test() {
          $passphrase = $this->get_encryption_passphrase();
          return $this->do_encrypt_string( 'test', $passphrase );
        }
        protected function do_php_openssl_encrypt(
          $plaintext,
          $cipher,
          $passphrase,
          $options,
          $iv,
          &$tag
        ) {
          $tag = 'invalid';
          return false;
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_DATA_ENCODING_INVALID,
    function() {
      return new class extends TestCrypto {
        public function test() {
          return $this->do_encrypt( true );
        }
        protected function do_get_config_data_encoding( $default ) {
          return KICKASS_CRYPTO_DATA_ENCODING_PHPS;
        }
        protected function do_is_valid_data_encoding( $data_encoding ) { return false; }
        protected function do_get_config_phps_enable( $default ) { return true; }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_DATA_ENCODING_INVALID_2,
    function() {
      return new class extends ValidCrypto {
        public function test() {
          return $this->encrypt( 'test'  );
        }
        protected function do_get_data_encoding() { return 'invalid'; }
        protected function do_get_config_phps_enable( $default ) { return true; }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_DATA_ENCODING_INVALID_3,
    function() {
      return new class extends ValidCrypto {
        public function test() {
          return $this->do_decode_message( '00000001|invalid|true', $data_encoding );
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_ENCRYPTION_FAILED,
    function() {
      return new class extends ValidCrypto {
        public function test() {
          return $this->encrypt( 'test'  );
        }
        protected function do_encrypt_string( $compressed, $passphrase ) {
          return false;
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_ENCRYPTION_FAILED,
    function() {
      return new class extends ValidCrypto {
        public function test() {
          return $this->encrypt( 'test'  );
        }
        protected function do_php_openssl_encrypt(
          $plaintext,
          $cipher,
          $passphrase,
          $options,
          $iv,
          &$tag
        ) {
          parent::do_php_openssl_encrypt( $plaintext, $cipher, $passphrase, $options, $iv, $tag );
          return false;
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_ENCRYPTION_FAILED,
    function() {
      return new class extends ValidCrypto {
        public function test() {
          return $this->encrypt( 'test'  );
        }
        protected function do_php_openssl_encrypt(
          $plaintext,
          $cipher,
          $passphrase,
          $options,
          $iv,
          &$tag
        ) {
          $iv = null;
          return parent::do_php_openssl_encrypt(
            $plaintext,
            $cipher,
            $passphrase,
            $options,
            $iv,
            $tag
          );
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_ENCRYPTION_FAILED,
    function() {
      return new class extends ValidCrypto {
        public function test() {
          assert( $this->get_openssl_error() === null );
          $result = $this->encrypt( 'test'  );
          assert( $this->get_openssl_error() !== null );
          return $result;
        }
        protected function do_php_openssl_encrypt(
          $plaintext,
          $cipher,
          $passphrase,
          $options,
          $iv,
          &$tag
        ) {
          $iv = '';
          return parent::do_php_openssl_encrypt(
            $plaintext,
            $cipher,
            $passphrase,
            $options,
            $iv,
            $tag
          );
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_ENCRYPTION_FAILED_2,
    function() {
      return new class extends ValidCrypto {
        private $count = 0;
        public function test() {
          $passphrase = $this->get_encryption_passphrase();
          return $this->do_encrypt_string( 'test', $passphrase  );
        }
        protected function do_php_openssl_encrypt(
          $plaintext,
          $cipher,
          $passphrase,
          $options,
          $iv,
          &$tag
        ) {
          parent::do_php_openssl_encrypt( $plaintext, $cipher, $passphrase, $options, $iv, $tag );
          return false;
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_EXCEPTION_RAISED,
    function() {
      return new class extends ValidCrypto {
        private $iv_count = 0;
        public function test() {
          $passphrase = $this->get_encryption_passphrase();
          return $this->do_encrypt_string( 'test', $passphrase );
        }
        protected function do_php_random_bytes( $length ) {
          return '';
        }
        protected function get_const_openssl_iv_length() {
          $this->iv_count++;
          if ( $this->iv_count === 1 ) { return parent::get_const_openssl_iv_length(); }
          return 0;
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_EXCEPTION_RAISED,
    function() {
      return new class extends ValidCrypto {
        private $cipher_count = 0;
        public function test() {
          $passphrase = $this->get_encryption_passphrase();
          return $this->do_encrypt_string( 'test', $passphrase );
        }
        protected function get_const_openssl_cipher() {
          $this->cipher_count++;
          if ( $this->cipher_count === 1 ) { return parent::get_const_openssl_cipher(); }
          return 'invalid';
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_EXCEPTION_RAISED_2,
    function() {
      return new class extends ValidCrypto {
        public function test() {
          $ciphertext = $this->encrypt( 'test' );
          $binary = $this->message_decode( $ciphertext );
          return $this->do_decrypt_string( $binary, $this->get_encryption_passphrase() );
        }
        protected function do_php_openssl_decrypt(
          $ciphertext, $cipher, $passphrase, $options, $iv, $tag
        ) {
          $cipher = 'invalid';
          return parent::do_php_openssl_decrypt(
            $ciphertext, $cipher, $passphrase, $options, $iv, $tag
          );
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_EXCEPTION_RAISED_2,
    function() {
      return new class extends ValidCrypto {
        public function test() {
          $ciphertext = $this->encrypt( 'test' );
          $binary = $this->message_decode( $ciphertext );
          return $this->do_decrypt_string( $binary, $this->get_encryption_passphrase() );
        }
        protected function do_php_openssl_decrypt(
          $ciphertext, $cipher, $passphrase, $options, $iv, $tag
        ) {
          $iv = '';
          return parent::do_php_openssl_decrypt(
            $ciphertext, $cipher, $passphrase, $options, $iv, $tag
          );
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_EXCEPTION_RAISED_3,
    function() {
      return new class extends ValidCrypto {
        public function test() {
          return $this->encrypt( 'test' );
        }
        public function do_encrypt( $input ) {
          throw new \Exception( 'fail' );
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_EXCEPTION_RAISED_4,
    function() {
      return new class extends ValidCrypto {
        public function test() {
          return $this->decrypt( 'test' );
        }
        public function do_decrypt( $input ) {
          throw new \Exception( 'fail' );
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_CIPHERTEXT_INVALID,
    function() {
      return new class extends ValidCrypto {
        public function test() {
          return $this->decrypt( 'invalid'  );
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_BINARY_LENGTH_INVALID,
    function() {
      return new class extends ValidCrypto {
        public function test() {
          $binary = str_repeat(
            '0',
            $this->get_const_openssl_tag_length() + $this->get_const_openssl_iv_length()
          );
          return $this->parse_binary( $binary, $iv, $ciphertext, $tag );
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_BINARY_DATA_INVALID,
    function() {
      return new class extends ValidCrypto {
        public function test() {
          return $this->do_decrypt_string( 'invalid', 'invalid' );
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_MESSAGE_FORMAT_INVALID,
    function() {
      return new class extends ValidCrypto {
        public function test() {
          return $this->try_decrypt( 'invalid', 'invalid' );
        }
        protected function do_decrypt_string( $binary, $key ) {
          return 'invalid';
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_DATA_ENCODING_TOO_LARGE,
    function() {
      return new class extends ValidCrypto {
        public function test() {
          return $this->encrypt( str_repeat( '0', $this->get_config_data_length_max() ) );
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_DATA_DECODING_FAILED,
    function() {
      return new class extends ValidCrypto {
        public function test() {
          $ciphertext = $this->encrypt( 'test' );
          if ( ! $ciphertext ) {
            var_dump(  $this->get_error_list() );
          }
          $result = $this->decrypt( $ciphertext );
          return $result;
        }
        protected function do_data_decode( $input, $data_encoding, &$is_false ) {
          return false;
        }
      };
    }
  );

  /*
  test_error(
    KICKASS_CRYPTO_ERROR_DEFLATE_FAILED,
    function() {
      return new class extends ValidCrypto {
        public function test() {
          return $this->encrypt( 'test' );
        }
        protected function deflate( $buffer ) {
          return false;
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_INFLATE_FAILED,
    function() {
      return new class extends ValidCrypto {
        public function test() {
          $ciphertext = $this->encrypt( 'test' );
          $binary = $this->message_decode( $ciphertext );
          return $this->try_decrypt( $binary, $this->get_encryption_passphrase() );
        }
        protected function inflate( $buffer ) {
          return false;
        }
      };
    }
  );
  */

  test_error(
    KICKASS_CRYPTO_ERROR_NO_VALID_KEY,
    function() {
      return new class extends ValidCrypto {
        public function test() {
          $test = "XKA0/4cw25Y/6+5FIbfUOwHnkaGk5SHerXpBYdd6He9xjCRlzqzpUZAaU4U3kGZ0zKeym73d0DaXXlgcTugMDTOT+LThg8AfE54fkmSZBx7ne7Ulz";
          return $this->decrypt( $test );
        }
        protected function do_get_passphrase_list() { return []; }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_DECRYPTION_FAILED,
    function() {
      return new class extends ValidCrypto {
        public function test() {
          $ciphertext = $this->encrypt( 'test' );
          $binary = $this->message_decode( $ciphertext );
          return $this->try_decrypt( $binary, $this->get_encryption_passphrase() );
        }
        protected function do_decrypt_string( $binary, $key ) {
          return false;
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_DECRYPTION_FAILED_2,
    function() {
      return new class extends ValidCrypto {
        private $count = 0;
        public function test() {
          $ciphertext = $this->encrypt( 'test' );
          $binary = $this->message_decode( $ciphertext );
          return $this->do_decrypt_string( $binary, $this->get_encryption_passphrase() );
        }
        protected function do_php_openssl_decrypt(
          $ciphertext,
          $cipher,
          $passphrase,
          $options,
          $iv,
          $tag
        ) {
          return false;
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_MESSAGE_FORMAT_INVALID,
    function() {
      return new class extends ValidCrypto {
        public function test() {
          return $this->decode_message( 'invalid', $data_encoding );
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_MESSAGE_DATA_LENGTH_SPEC_INVALID,
    function() {
      return new class extends ValidCrypto {
        public function test() {
          return $this->decode_message( '123456789|json|true', $data_encoding );
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_MESSAGE_DATA_LENGTH_RANGE_INVALID,
    function() {
      return new class extends ValidCrypto {
        public function test() {
          assert( $this->decode_message( '00000001|json| ', $data_encoding ) === ' ' );
          return $this->decode_message( '00000000|json|', $data_encoding );
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_MESSAGE_DATA_LENGTH_RANGE_INVALID,
    function() {
      return new class extends ValidCrypto {
        public function test() {
          return $this->decode_message( 'ffffffff|json|true', $data_encoding );
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_MESSAGE_DATA_LENGTH_RANGE_INVALID,
    function() {
      return new class extends ValidCrypto {
        public function test() {
          return $this->decode_message( '80000000|json|true', $data_encoding );
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_MESSAGE_LENGTH_INVALID,
    function() {
      return new class extends ValidCrypto {
        public function test() {
          return $this->decode_message( '7fffffff|json|true', $data_encoding );
        }
      };
    }
  );

}

main( $argv );
