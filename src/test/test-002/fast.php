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
 * 2023-03-30 jj5 - this test checks that config problems are properly handled...
 *
 * @link https://github.com/jj5/kickass-crypto
 */

//define( 'DEBUG', true );

require_once __DIR__ . '/../../../inc/test-host.php';

class TestKickassSodiumRoundTrip extends \KickassCrypto\Sodium\KickassSodiumRoundTrip {

  use \KickassCrypto\Traits\KICKASS_DEBUG_LOG;

}

class TestKickassSodiumAtRest extends \KickassCrypto\Sodium\KickassSodiumAtRest {

  use \KickassCrypto\Traits\KICKASS_DEBUG_LOG;

}

class TestKickassOpenSslRoundTrip extends \KickassCrypto\OpenSsl\KickassOpenSslRoundTrip {

  use \KickassCrypto\Traits\KICKASS_DEBUG_LOG;

}

class TestKickassOpenSslAtRest extends \KickassCrypto\OpenSsl\KickassOpenSslAtRest {

  use \KickassCrypto\Traits\KICKASS_DEBUG_LOG;

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
  catch ( \KickassCrypto\KickassCryptoException $ex ) {

    $expected_code = KICKASS_CRYPTO_EXCEPTION_INVALID_CONFIG;
    $expected_message = KICKASS_CRYPTO_EXCEPTION_MESSAGE[ $expected_code ];

    assert( $ex->getCode() === $expected_code );
    assert( $ex->getMessage() === $expected_message );

  }
}

main( $argv );
