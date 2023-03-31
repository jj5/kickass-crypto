#!/usr/bin/env php
<?php

// 2023-03-30 jj5 - this test takes the various errors for a spin, all code paths which should
// result in an error should be exercised...

require_once __DIR__ . '/../../host/unit-test.php';

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
