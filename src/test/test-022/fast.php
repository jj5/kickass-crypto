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
// 2023-04-06 jj5 - this test just checks the error reporting and clearing works properly.
//
// 2023-04-06 jj5 - TODO: the tests in this script could be more comprehensive.
//
\************************************************************************************************/

define( 'DEBUG', true );

require_once __DIR__ . '/etc/config.php';
require_once __DIR__ . '/../../../inc/test-host.php';

function run_test() {

  for ( $n = 1; $n <= 100; $n++ ) {

    test( kickass_round_trip() );

    test( kickass_at_rest() );

  }
}

function test( $crypto ) {

  $secret = base64_encode( random_bytes( random_int( 100, 100 ) ) );

  $ciphertext = $crypto->encrypt( $secret );

  $plaintext = $crypto->decrypt( $ciphertext );

  assert( $secret === $plaintext );

  assert( $crypto->get_error() === null );
  assert( $crypto->get_error_list() === [] );

}

main( $argv );
