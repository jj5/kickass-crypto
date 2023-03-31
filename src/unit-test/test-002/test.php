#!/usr/bin/env php
<?php

// 2023-03-30 jj5 - this test checks that config problems are properly handled...

require_once __DIR__ . '/../../../inc/test-host.php';

function run_test( $argv ) {

  $class = $argv[ 1 ];
  $code_const = $argv[ 2 ];

  $code = constant( $code_const );

  $config = __DIR__ . '/etc/' . $code_const . '.php';

  require $config;

  try {

    switch ( $class ) {

      case 'KickassCryptoRoundTrip':

        $crypto = new KickassCryptoRoundTrip();

        break;

      case 'KickassCryptoAtRest':

        $crypto = new KickassCryptoAtRest();

        break;

    }

    assert( false );

  }
  catch ( KickassException $ex ) {

    $expected_code = KICKASS_CRYPTO_EXCEPTION_INVALID_CONFIG;
    $expected_message = KICKASS_CRYPTO_EXCEPTION_MESSAGE[ $expected_code ];

    assert( $ex->getCode() === $expected_code );
    assert( $ex->getMessage() === $expected_message );

  }
}

main( $argv );
