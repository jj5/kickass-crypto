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
// 2023-03-30 jj5 - this test takes the round-trip crypto for a spin using various chunk sizes...
//
\************************************************************************************************/

require_once __DIR__ . '/../../../inc/test-host.php';

class TestCryptoRoundTrip extends \KickassCrypto\Module\OpenSsl\KickassOpenSslRoundTrip {

  private $chunk_size = 0;

  protected function do_get_config_chunk_size( $default = KICKASS_CRYPTO_DEFAULT_CHUNK_SIZE ) {

    return $this->chunk_size;

  }

  public function set_chunk_size( $chunk_size ) { $this->chunk_size = $chunk_size; }

  protected function get_config_secret_curr() {

    static $secret = null;

    if ( $secret === null ) { $secret = self::GenerateSecret(); }

    return $secret;

  }
}

function run_test() {

  $crypto = new TestCryptoRoundTrip();

  for ( $chunk_size = 8; $chunk_size < 128; $chunk_size += 8 ) {

    $crypto->set_chunk_size( $chunk_size );

    for ( $n = 1; $n <= 10; $n++ ) {

      $test_string = str_repeat( 'test', $n );

      $ciphertext = $crypto->encrypt( $test_string );

      if ( $ciphertext === false ) {

        var_dump( $crypto->get_error_list() );

      }

      $plaintext = $crypto->decrypt( $ciphertext );

      if ( $plaintext === false ) {

        var_dump( $crypto->get_error_list() );

      }

      assert( $plaintext === $test_string );

    }
  }
}

main( $argv );
