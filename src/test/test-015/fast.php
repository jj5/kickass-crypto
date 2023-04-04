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
// 2023-04-04 jj5 - this script takes the serialization overrides for a spin...
//
// 2023-04-05 jj5 - NOTE: this code remains valid but it was written before PHP serialization
// support was implemented. As PHP serialization support is now done you don't need to do it
// yourself such as its done in this script.
//
\************************************************************************************************/

define( 'DEBUG', true );

require_once __DIR__ . '/../../../inc/test-host.php';
require_once __DIR__ . '/etc/config.php';

class CryptoTest extends \Kickass\Crypto\Module\Sodium\KickassSodiumRoundTrip {

  protected function do_data_encode( $input, $data_encoding ) {

    return serialize( $input );

  }

  protected function do_data_decode( string $input, $data_encoding, &$is_false ) {

    $is_false = false;

    return unserialize( $input );

  }
}

function run_test() {

  $crypto = new CryptoTest();

  $ciphertext = $crypto->encrypt( 'test' );

  $plaintext = $crypto->decrypt( $ciphertext );

  assert( $plaintext === 'test' );

}

main( $argv );
