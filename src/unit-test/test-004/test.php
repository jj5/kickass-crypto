#!/usr/bin/env php
<?php

// 2023-03-30 jj5 - this test takes the round-trip crypto for a spin using various chunk sizes...

require_once __DIR__ . '/../../host/unit-test.php';

define( 'KICKASS_CRYPTO_DEBUG', true );

class TestCryptoRoundTrip extends KickassCryptoRoundTrip {

  private $chunk_size = 0;

  protected function get_config_chunk_size( $default = KICKASS_CRYPTO_DEFAULT_CHUNK_SIZE ) {

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

      $plaintext = $crypto->decrypt( $ciphertext );

      assert( $plaintext === $test_string );

    }
  }
}

main( $argv );
