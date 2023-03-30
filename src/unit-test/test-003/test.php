#!/usr/bin/env php
<?php

// 2023-03-30 jj5 - this test takes the various errors for a spin, all code paths which should
// result in an error should be exercised...

require_once __DIR__ . '/../../host/unit-test.php';

define( 'KICKASS_CRYPTO_DEBUG', true );

class TestCrypto extends KickassCryptoRoundTrip {

  protected function is_valid_config( &$problem = null ) { $problem = null; return true; }

  protected function do_delay(
    int $ns_max = KICKASS_CRYPTO_DELAY_NS_MAX,
    int $ns_min = KICKASS_CRYPTO_DELAY_NS_MIN
  ) {

    // 2023-03-30 jj5 - we disable the delay during testing...

  }
}

class ValidCrypto extends TestCrypto {

  protected function get_passphrase_list() {

    static $result = null;

    if ( $result === null ) { $result = [ $this->calc_passphrase( 'whatever' ) ]; }

    return $result;

  }
}

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
    KICKASS_CRYPTO_ERROR_INVALID_BASE64_ENCODING,
    function() {
      return new class extends TestCrypto {
        public function test() {
          return $this->decode( 'KA1/!@#$%^&*()_' );
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_BASE64_DECODE_FAILED,
    function() {
      return new class extends TestCrypto {
        public function test() {
          return $this->decode( 'KA1/123=' );
        }
        protected function php_base64_decode( $input ) { return false; }
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
          return $this->decrypt( 'KA1/test' );
        }
        protected function get_passphrase_list() { return [ 'invalid' ]; }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_WEAK_RESULT,
    function() {
      return new class extends ValidCrypto {
        public function test() {
          $passphrase = $this->get_encryption_passphrase();
          return $this->encrypt_string( 'test', $passphrase );
        }
        protected function php_openssl_random_pseudo_bytes( $length, &$strong_result ) {
          $strong_result = false;
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
          return $this->encrypt_string( 'test', $passphrase );
        }
        protected function php_openssl_random_pseudo_bytes( $length, &$strong_result ) {
          $strong_result = true;
          return '123';
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_INVALID_IV_LENGTH_2,
    function() {
      return new class extends ValidCrypto {
        public function test() {
          $binary = str_repeat( '0', $this->get_const_taglen() ) . '0';
          return $this->parse_data( $binary, $iv, $tag, $ciphertext );
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
          return $this->encrypt_string( 'test', $passphrase );
        }
        protected function php_openssl_encrypt( $plaintext, $cipher, $passphrase, $options, $iv, &$tag ) {
          $tag = 'invalid';
          return false;
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_INVALID_TAG_LENGTH_2,
    function() {
      return new class extends ValidCrypto {
        public function test() {
          return $this->parse_data( 'invalid', $iv, $tag, $ciphertext );
        }
        protected function php_openssl_encrypt( $plaintext, $cipher, $passphrase, $options, $iv, &$tag ) {
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
        protected function encrypt_string( $compressed, $passphrase ) {
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
        protected function php_openssl_encrypt( $plaintext, $cipher, $passphrase, $options, $iv, &$tag ) {
          parent::php_openssl_encrypt( $plaintext, $cipher, $passphrase, $options, $iv, $tag );
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
        protected function php_openssl_encrypt( $plaintext, $cipher, $passphrase, $options, $iv, &$tag ) {
          $iv = null;
          return parent::php_openssl_encrypt( $plaintext, $cipher, $passphrase, $options, $iv, $tag );
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
        protected function php_openssl_encrypt( $plaintext, $cipher, $passphrase, $options, $iv, &$tag ) {
          $iv = '';
          return parent::php_openssl_encrypt( $plaintext, $cipher, $passphrase, $options, $iv, $tag );
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
          return $this->encrypt( 'test'  );
        }
        protected function encrypt_string( $compressed, $passphrase ) {
          $this->count++;
          if ( $this->count === 1 ) { return parent::encrypt_string( $compressed, $passphrase ); }
          return false;
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_ENCRYPTION_FAILED_3,
    function() {
      return new class extends ValidCrypto {
        private $count = 0;
        public function test() {
          $passphrase = $this->get_encryption_passphrase();
          return $this->encrypt_string( 'test', $passphrase  );
        }
        protected function php_openssl_encrypt( $plaintext, $cipher, $passphrase, $options, $iv, &$tag ) {
          parent::php_openssl_encrypt( $plaintext, $cipher, $passphrase, $options, $iv, $tag );
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
          return $this->encrypt_string( 'test', $passphrase );
        }
        protected function php_openssl_random_pseudo_bytes( $length, &$strong_result ) {
          $strong_result = true;
          return '';
        }
        protected function get_const_ivlen() {
          $this->iv_count++;
          if ( $this->iv_count === 1 ) { return parent::get_const_ivlen(); }
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
          return $this->encrypt_string( 'test', $passphrase );
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
          return $this->decrypt_string( $binary, $this->get_encryption_passphrase() );
        }
        protected function php_openssl_decrypt(
          $ciphertext, $cipher, $passphrase, $options, $iv, $tag
        ) {
          $cipher = 'invalid';
          return parent::php_openssl_decrypt(
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
          return $this->decrypt_string( $binary, $this->get_encryption_passphrase() );
        }
        protected function php_openssl_decrypt(
          $ciphertext, $cipher, $passphrase, $options, $iv, $tag
        ) {
          $iv = '';
          return parent::php_openssl_decrypt(
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
    KICKASS_CRYPTO_ERROR_EXCEPTION_RAISED_5,
    function() {
      return new class extends ValidCrypto {
        public function test() {
          return $this->delay();
        }
        public function do_delay(
          int $ns_max = KICKASS_CRYPTO_DELAY_NS_MAX,
          int $ns_min = KICKASS_CRYPTO_DELAY_NS_MIN
        ) {
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
    KICKASS_CRYPTO_ERROR_INVALID_CIPHERTEXT_2,
    function() {
      return new class extends ValidCrypto {
        public function test() {
          $binary = str_repeat( '0', $this->get_const_taglen() ) .
            str_repeat( '0', $this->get_const_ivlen() );
          return $this->parse_data( $binary, $iv, $tag, $ciphertext );
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_INVALID_DATA,
    function() {
      return new class extends ValidCrypto {
        public function test() {
          return $this->decrypt_string( 'invalid', 'invalid' );
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_INVALID_PARTS,
    function() {
      return new class extends ValidCrypto {
        public function test() {
          return $this->try_decrypt( 'invalid', 'invalid' );
        }
        protected function decrypt_string( $binary, $key ) {
          return 'invalid';
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_SERIALIZE_FAILED,
    function() {
      return new class extends ValidCrypto {
        public function test() {
          return $this->encrypt( 'test' );
        }
        protected function php_serialize( $input ) {
          return false;
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_SERIALIZE_TOO_LARGE,
    function() {
      return new class extends ValidCrypto {
        public function test() {
          return $this->encrypt( str_repeat( '0', $this->get_config_serialize_limit() ) );
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_UNSERIALIZE_FAILED,
    function() {
      return new class extends ValidCrypto {
        public function test() {
          $ciphertext = $this->encrypt( 'test' );
          return $this->decrypt( $ciphertext );
        }
        protected function php_unserialize( $input ) {
          return false;
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_DEFLATE_FAILED,
    function() {
      return new class extends ValidCrypto {
        public function test() {
          return $this->encrypt( 'test' );
        }
        protected function php_gzdeflate( $buffer ) {
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
        protected function php_gzinflate( $buffer ) {
          return false;
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_NO_VALID_KEY,
    function() {
      return new class extends ValidCrypto {
        public function test() {
          $test = "KA1/4cw25Y/6+5FIbfUOwHnkaGk5SHerXpBYdd6He9xjCRlzqzpUZAaU4U3kGZ0zKeym73d0DaXXlgcTugMDTOT+LThg8AfE54fkmSZBx7ne7Ulz";
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
        protected function decrypt_string( string $binary, string $key ) {
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
          return $this->try_decrypt( $binary, $this->get_encryption_passphrase() );
        }
        protected function decrypt_string( string $binary, string $key ) {
          $this->count++;
          if ( $this->count === 1 ) { return parent::decrypt_string( $binary, $key ); }
          return false;
        }
      };
    }
  );

  test_error(
    KICKASS_CRYPTO_ERROR_DECRYPTION_FAILED_3,
    function() {
      return new class extends ValidCrypto {
        private $count = 0;
        public function test() {
          $ciphertext = $this->encrypt( 'test' );
          $binary = $this->decode( $ciphertext );
          return $this->decrypt_string( $binary, $this->get_encryption_passphrase() );
        }
        protected function php_openssl_decrypt( $ciphertext, $cipher, $passphrase, $options, $iv, $tag ) {
          return false;
        }
      };
    }
  );

}

function test_error( string $expected_error, callable $create_crypto, $data = null ) {

  $crypto = $create_crypto();

  assert( $crypto->get_error() === null );
  assert( count( $crypto->get_error_list() ) === 0 );

  $result = $crypto->test();

  if ( $result !== false ) {

    var_dump([
      'class' => get_class( $crypto ),
      'result' => $result,
    ]);

  }

  assert( $result === false );

  $error = $crypto->get_error();
  $error_list = $crypto->get_error_list();

  assert( count( $error_list ) > 0 );

  if ( $error !== $expected_error ) {

    var_dump([
      'error_list' => $error_list,
      'expected_error' => $expected_error,
    ]);

  }

  assert( $error === $expected_error );

}

main( $argv );
