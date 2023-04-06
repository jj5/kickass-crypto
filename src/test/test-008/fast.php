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
 * 2023-03-31 jj5 - this test does some very rudiementary testing of our class counter telemetry.
 *
 * @link https://github.com/jj5/kickass-crypto
 */

//define( 'DEBUG', true );

require_once __DIR__ . '/../../../inc/test-host.php';

class TestCrypto extends \KickassCrypto\OpenSsl\KickassOpenSslRoundTrip {

  use \KickassCrypto\Traits\KICKASS_DEBUG;

}

function run_test() {

  $crypto = new TestCrypto();

  $ciphertext = $crypto->encrypt( 'test' );

  ob_start();

  \KickassCrypto\KickassCrypto::ReportTelemetry();

  $output = ob_get_clean();

  if ( $output !== get_expected_output() ) {

    var_dump( $output );

  }

  assert( $output === get_expected_output() );

}

function get_expected_output() {

  return ltrim("
= Functions =

__construct..: 1
encrypt......: 1

= Classes =

KickassCrypto\OpenSsl\KickassOpenSslRoundTrip..: 1

= Lengths =

5525..: 1
");

}

main( $argv );
