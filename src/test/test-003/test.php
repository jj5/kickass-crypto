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

/************************************************************************************************\
//
// 2023-04-05 jj5 - this script is just for looking at specific cases which ordinarily will run
// in fast.php.
//
\************************************************************************************************/

define( 'DEBUG', true );

require_once __DIR__ . '/../../../inc/test-host.php';
require_once __DIR__ . '/lib/include.php';

function run_test() {

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
}

main( $argv );
