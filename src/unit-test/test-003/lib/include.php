<?php

define( 'KICKASS_CRYPTO_DEBUG', true );

class TestCrypto extends KickassCryptoRoundTrip {

  protected function is_valid_config( &$problem = null ) { $problem = null; return true; }

  protected function do_delay(
    int $ns_max = KICKASS_CRYPTO_DELAY_NS_MAX,
    int $ns_min = KICKASS_CRYPTO_DELAY_NS_MIN
  ) {

    // 2023-03-30 jj5 - we disable the delay during testing...

  }
}

class ValidCrypto extends TestCrypto {

  protected function get_passphrase_list() {

    static $result = null;

    if ( $result === null ) { $result = [ $this->calc_passphrase( 'whatever' ) ]; }

    return $result;

  }
}

function test_error( string $expected_error, callable $create_crypto, $data = null ) {

  $crypto = $create_crypto();

  assert( $crypto->get_error() === null );
  assert( count( $crypto->get_error_list() ) === 0 );

  $result = $crypto->test();

  if ( $result !== false ) {

    var_dump([
      'class' => get_class( $crypto ),
      'result' => $result,
    ]);

  }

  assert( $result === false );

  $error = $crypto->get_error();
  $error_list = $crypto->get_error_list();

  assert( count( $error_list ) > 0 );

  if ( $error !== $expected_error ) {

    var_dump([
      'error_list' => $error_list,
      'expected_error' => $expected_error,
    ]);

  }

  assert( $error === $expected_error );

}
