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
// 2023-03-30 jj5 - this test checks that config problems are properly handled...
//
\************************************************************************************************/

require_once __DIR__ . '/../../../inc/test-host.php';

class TestKickassSodiumRoundTrip extends \Kickass\Crypto\Module\Sodium\KickassSodiumRoundTrip {

  use \Kickass\Crypto\Traits\KICKASS_DEBUG_LOG;

}

class TestKickassSodiumAtRest extends \Kickass\Crypto\Module\Sodium\KickassSodiumAtRest {

  use \Kickass\Crypto\Traits\KICKASS_DEBUG_LOG;

}

class TestKickassOpenSslRoundTrip extends \Kickass\Crypto\Module\OpenSsl\KickassOpenSslRoundTrip {

  use \Kickass\Crypto\Traits\KICKASS_DEBUG_LOG;

}

class TestKickassOpenSslAtRest extends \Kickass\Crypto\Module\OpenSsl\KickassOpenSslAtRest {

  use \Kickass\Crypto\Traits\KICKASS_DEBUG_LOG;

}

function run_test( $argv ) {

  $class = $argv[ 1 ];
  $code_const = $argv[ 2 ];

  $code = constant( $code_const );

  $config = __DIR__ . '/etc/' . $code_const . '.php';

  require $config;

  try {

    switch ( $class ) {

      case 'KickassSodiumRoundTrip':

        $crypto = new TestKickassSodiumRoundTrip();

        break;

      case 'KickassSodiumAtRest':

        $crypto = new TestKickassSodiumAtRest();

        break;

      case 'KickassOpenSslRoundTrip':

        $crypto = new TestKickassOpenSslRoundTrip();

        break;

      case 'KickassOpenSslAtRest':

        $crypto = new TestKickassOpenSslAtRest();

        break;

    }

    assert( false );

  }
  catch ( \Kickass\KickassException $ex ) {

    $expected_code = KICKASS_CRYPTO_EXCEPTION_INVALID_CONFIG;
    $expected_message = KICKASS_CRYPTO_EXCEPTION_MESSAGE[ $expected_code ];

    assert( $ex->getCode() === $expected_code );
    assert( $ex->getMessage() === $expected_message );

  }
}

main( $argv );
