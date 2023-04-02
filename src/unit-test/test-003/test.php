#!/usr/bin/env php
<?php

// 2023-03-30 jj5 - this test takes the various errors for a spin, all code paths which should
// result in an error should be exercised... the code paths which will run quickly are in this
// test, test.php, other tests which include a delay are in slow.php.

require_once __DIR__ . '/../../../inc/test-host.php';

require_once __DIR__ . '/lib/include.php';

function run_test() {

  test_error(
    KICKASS_CRYPTO_ERROR_INVALID_ENCODING,
    function() {
      return new class extends TestCrypto {
        public function test() {
          return $this->decode( 'invalid' );
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_UNKNOWN_ENCODING,
    function() {
      return new class extends TestCrypto {
        public function test() {
          return $this->decode( 'KA2/invalid' );
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_BASE64_DECODE_FAILED,
    function() {
      return new class extends TestCrypto {
        public function test() {
          return $this->decode( 'KA0/!@#$%^&*()_' );
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_BASE64_DECODE_FAILED,
    function() {
      return new class extends TestCrypto {
        public function test() {
          return $this->decode( 'KA0/123=' );
        }
        protected function do_php_base64_decode( $input, $strict ) { return false; }
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
    KICKASS_CRYPTO_ERROR_INVALID_PASSPHRASE,
    function() {
      return new class extends TestCrypto {
        public function test() {
          return $this->do_encrypt( 'test' );
        }
        protected function get_passphrase_list() { return []; }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_INVALID_PASSPHRASE_LENGTH,
    function() {
      return new class extends TestCrypto {
        public function test() {
          return $this->do_encrypt( 'test' );
        }
        protected function get_passphrase_list() { return [ 'invalid' ]; }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_INVALID_PASSPHRASE_LENGTH_2,
    function() {
      return new class extends TestCrypto {
        public function test() {
          return $this->decrypt( 'KA0/test' );
        }
        protected function get_passphrase_list() { return [ 'invalid' ]; }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_INVALID_CHUNK_SIZE,
    function() {
      return new class extends TestCrypto {
        public function test() {
          return $this->do_encrypt( 'test' );
        }
        protected function get_config_chunk_size( $default = KICKASS_CRYPTO_DEFAULT_CHUNK_SIZE ) {
          return true;
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_INVALID_CHUNK_SIZE,
    function() {
      return new class extends TestCrypto {
        public function test() {
          return $this->do_encrypt( 'test' );
        }
        protected function get_config_chunk_size( $default = KICKASS_CRYPTO_DEFAULT_CHUNK_SIZE ) {
          return false;
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_INVALID_CHUNK_SIZE,
    function() {
      return new class extends TestCrypto {
        public function test() {
          return $this->do_encrypt( 'test' );
        }
        protected function get_config_chunk_size( $default = KICKASS_CRYPTO_DEFAULT_CHUNK_SIZE ) {
          return 0;
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_INVALID_CHUNK_SIZE,
    function() {
      return new class extends TestCrypto {
        public function test() {
          return $this->do_encrypt( 'test' );
        }
        protected function get_config_chunk_size( $default = KICKASS_CRYPTO_DEFAULT_CHUNK_SIZE ) {
          return 0.0;
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_INVALID_CHUNK_SIZE,
    function() {
      return new class extends TestCrypto {
        public function test() {
          return $this->do_encrypt( 'test' );
        }
        protected function get_config_chunk_size( $default = KICKASS_CRYPTO_DEFAULT_CHUNK_SIZE ) {
          return 1.0;
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_INVALID_CHUNK_SIZE,
    function() {
      return new class extends TestCrypto {
        public function test() {
          return $this->do_encrypt( 'test' );
        }
        protected function get_config_chunk_size( $default = KICKASS_CRYPTO_DEFAULT_CHUNK_SIZE ) {
          return KICKASS_CRYPTO_DEFAULT_CHUNK_SIZE_MAX + 1;
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_INVALID_BINARY_LENGTH,
    function() {
      return new class extends ValidCrypto {
        public function test() {
          $binary = str_repeat(
            '0',
            $this->get_const_iv_length() - 1
          );
          return $this->parse_binary( $binary, $iv, $ciphertext, $tag );
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_INVALID_BINARY_LENGTH,
    function() {
      return new class extends ValidCrypto {
        public function test() {
          return $this->parse_binary( 'invalid', $iv, $ciphertext, $tag );
        }
        protected function do_php_openssl_encrypt( $plaintext, $cipher, $passphrase, $options, $iv, &$tag ) {
          $tag = 'invalid';
          return false;
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_INVALID_IV_LENGTH,
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
    KICKASS_CRYPTO_ERROR_INVALID_TAG_LENGTH,
    function() {
      return new class extends ValidCrypto {
        public function test() {
          $passphrase = $this->get_encryption_passphrase();
          return $this->do_encrypt_string( 'test', $passphrase );
        }
        protected function do_php_openssl_encrypt( $plaintext, $cipher, $passphrase, $options, $iv, &$tag ) {
          $tag = 'invalid';
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
        protected function do_php_openssl_encrypt( $plaintext, $cipher, $passphrase, $options, $iv, &$tag ) {
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
        protected function do_php_openssl_encrypt( $plaintext, $cipher, $passphrase, $options, $iv, &$tag ) {
          $iv = null;
          return parent::do_php_openssl_encrypt( $plaintext, $cipher, $passphrase, $options, $iv, $tag );
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
        protected function do_php_openssl_encrypt( $plaintext, $cipher, $passphrase, $options, $iv, &$tag ) {
          $iv = '';
          return parent::do_php_openssl_encrypt( $plaintext, $cipher, $passphrase, $options, $iv, $tag );
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
        protected function do_php_openssl_encrypt( $plaintext, $cipher, $passphrase, $options, $iv, &$tag ) {
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
        protected function get_const_iv_length() {
          $this->iv_count++;
          if ( $this->iv_count === 1 ) { return parent::get_const_iv_length(); }
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
        protected function get_const_cipher() {
          $this->cipher_count++;
          if ( $this->cipher_count === 1 ) { return parent::get_const_cipher(); }
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
          $binary = $this->decode( $ciphertext );
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
          $binary = $this->decode( $ciphertext );
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
          throw new Exception( 'fail' );
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
          throw new Exception( 'fail' );
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_INVALID_CIPHERTEXT,
    function() {
      return new class extends ValidCrypto {
        public function test() {
          return $this->decrypt( 'invalid'  );
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_INVALID_BINARY_LENGTH,
    function() {
      return new class extends ValidCrypto {
        public function test() {
          $binary = str_repeat(
            '0',
            $this->get_const_tag_length() + $this->get_const_iv_length()
          );
          return $this->parse_binary( $binary, $iv, $ciphertext, $tag );
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_INVALID_DATA,
    function() {
      return new class extends ValidCrypto {
        public function test() {
          return $this->do_decrypt_string( 'invalid', 'invalid' );
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_INVALID_MESSAGE_FORMAT,
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
    KICKASS_CRYPTO_ERROR_DATA_ENCODING_FAILED,
    function() {
      return new class extends ValidCrypto {
        public function test() {
          return $this->encrypt( 'test' );
        }
        protected function do_json_encode( $input ) {
          return false;
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_DATA_ENCODING_TOO_LARGE,
    function() {
      return new class extends ValidCrypto {
        public function test() {
          return $this->encrypt( str_repeat( '0', $this->get_config_json_length_max() ) );
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
        protected function do_json_decode( $input ) {
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
          $binary = $this->decode( $ciphertext );
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
          $test = "KA0/4cw25Y/6+5FIbfUOwHnkaGk5SHerXpBYdd6He9xjCRlzqzpUZAaU4U3kGZ0zKeym73d0DaXXlgcTugMDTOT+LThg8AfE54fkmSZBx7ne7Ulz";
          return $this->decrypt( $test );
        }
        protected function get_passphrase_list() { return []; }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_DECRYPTION_FAILED,
    function() {
      return new class extends ValidCrypto {
        public function test() {
          $ciphertext = $this->encrypt( 'test' );
          $binary = $this->decode( $ciphertext );
          return $this->try_decrypt( $binary, $this->get_encryption_passphrase() );
        }
        protected function do_decrypt_string( string $binary, string $key ) {
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
          $binary = $this->decode( $ciphertext );
          return $this->do_decrypt_string( $binary, $this->get_encryption_passphrase() );
        }
        protected function do_php_openssl_decrypt( $ciphertext, $cipher, $passphrase, $options, $iv, $tag ) {
          return false;
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_INVALID_MESSAGE_FORMAT,
    function() {
      return new class extends ValidCrypto {
        public function test() {
          return $this->decode_message( 'invalid' );
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_INVALID_MESSAGE_JSON_LENGTH_SPEC,
    function() {
      return new class extends ValidCrypto {
        public function test() {
          return $this->decode_message( '123456789|true' );
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_INVALID_MESSAGE_JSON_LENGTH_RANGE,
    function() {
      return new class extends ValidCrypto {
        public function test() {
          assert( $this->decode_message( '00000001| ' ) === ' ' );
          return $this->decode_message( '00000000|' );
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_INVALID_MESSAGE_JSON_LENGTH_RANGE,
    function() {
      return new class extends ValidCrypto {
        public function test() {
          return $this->decode_message( 'ffffffff|true' );
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_INVALID_MESSAGE_JSON_LENGTH_RANGE,
    function() {
      return new class extends ValidCrypto {
        public function test() {
          return $this->decode_message( '80000000|true' );
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_INVALID_MESSAGE_LENGTH,
    function() {
      return new class extends ValidCrypto {
        public function test() {
          return $this->decode_message( '7fffffff|true' );
        }
      };
    }
  );

}

main( $argv );
