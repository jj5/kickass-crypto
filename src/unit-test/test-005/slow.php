#!/usr/bin/env php
<?php

// 2023-03-30 jj5 - this test takes the service locators for a spin and makes sure they are
// mutually independent (i.e. don't share keys)... because tests for failure are included these
// tests will trigger a delay which is why they are defined in this slow.php file.

require_once __DIR__ . '/etc/config.php';
require_once __DIR__ . '/../../../inc/test-host.php';

function run_test() {

  define( 'KICKASS_CRYPTO_DISABLE_LOG', true );

  $plaintext = 'test';

  $ciphertext_1 = kickass_round_trip()->encrypt( $plaintext );
  $ciphertext_2 = kickass_at_rest()->encrypt( $plaintext );

  assert( kickass_round_trip()->decrypt( $ciphertext_1 ) === $plaintext );
  assert( kickass_round_trip()->decrypt( $ciphertext_2 ) === false );

  assert( kickass_at_rest()->decrypt( $ciphertext_1 ) === false );
  assert( kickass_at_rest()->decrypt( $ciphertext_2 ) === $plaintext );

}

main( $argv );
