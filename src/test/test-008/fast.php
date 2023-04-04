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
// 2023-03-31 jj5 - this test does some very rudiementary testing of our class counter telemetry.
//
\************************************************************************************************/

require_once __DIR__ . '/../../../inc/test-host.php';

class TestCrypto extends \Kickass\Crypto\Module\OpenSsl\KickassOpenSslRoundTrip {

  use \Kickass\Crypto\Traits\KICKASS_DEBUG;

}

function run_test() {

  $crypto = new TestCrypto();

  $ciphertext = $crypto->encrypt( 'test' );

  ob_start();

  \Kickass\Crypto\Framework\KickassCrypto::ReportTelemetry();

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

Kickass\Crypto\Module\OpenSsl\KickassOpenSslRoundTrip..: 1

= Lengths =

5525..: 1
");

}

main( $argv );
