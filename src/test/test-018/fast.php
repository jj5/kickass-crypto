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
// 2023-03-30 jj5 - this test verifies that we can encrypt and decrypt both simple and complex
// values using PHP serialization.
//
// 2023-04-04 jj5 - All the tests here should run relatively quickly because they succeed and
// don't cause any delay.
//
\************************************************************************************************/

require_once __DIR__ . '/etc/config.php';
require_once __DIR__ . '/../../../inc/test-host.php';

class TestOpenSslRoundTrip extends \Kickass\Crypto\Module\OpenSsl\KickassOpenSslRoundTrip {

  use \Kickass\Crypto\Traits\KICKASS_DEBUG_LOG;

  protected function do_delay(
    int $ns_max = KICKASS_CRYPTO_DELAY_NANOSECONDS_MAX,
    int $ns_min = KICKASS_CRYPTO_DELAY_NANOSECONDS_MIN
  ) {

    $this->php_time_nanosleep( 0, KICKASS_CRYPTO_DELAY_NANOSECONDS_MIN );

  }
}

function run_test() {

  $crypto = new TestOpenSslRoundTrip();

  $result = $crypto->encrypt( true );

  assert( $crypto->get_error() === KICKASS_CRYPTO_ERROR_PHPS_ENCODING_DISABLED );

}

main( $argv );
