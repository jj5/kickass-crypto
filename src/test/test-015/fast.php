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
\************************************************************************************************/

define( 'DEBUG', true );

require_once __DIR__ . '/../../../inc/test-host.php';
require_once __DIR__ . '/etc/config.php';

class CryptoTest extends \Kickass\Crypto\Module\Sodium\KickassSodiumRoundTrip {

  protected function do_data_encode( $input, $data_encoding ) {

    return serialize( $input );

  }

  protected function do_data_decode( string $input, $data_encoding ) {

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
