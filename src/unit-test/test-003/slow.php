#!/usr/bin/env php
<?php

// 2023-03-30 jj5 - this test takes the various errors for a spin, all code paths which should
// result in an error should be exercised... the code paths which will include a random delay
// are in this test, slow.php, other tests which will run quickly are in test.php.

require_once __DIR__ . '/../../../inc/test-host.php';

require_once __DIR__ . '/lib/include.php';

function run_test() {

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

}

main( $argv );
