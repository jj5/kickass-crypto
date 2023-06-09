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
 * 2023-04-06 jj5 - this test just takes the services for a spin with debugging enabled. When the
 * DEBUG constant is true that can have some subtle difference in the code, for example we use
 * different types of padding, so this is just to see that everything seems to be in order.
 *
 * @link https://github.com/jj5/kickass-crypto
 */

// 2023-04-07 jj5 - don't comment this out, this is important for this test!
//
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

}

main( $argv );
