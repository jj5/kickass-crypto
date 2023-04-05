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
// 2023-04-03 jj5 - these are some components to use with both the fast and slow tests.
//
\************************************************************************************************/

class TestCrypto extends \KickassCrypto\Module\OpenSsl\KickassOpenSslRoundTrip {

  use \KickassCrypto\Traits\KICKASS_DEBUG;

  protected function do_is_valid_config( &$problem ) { $problem = null; return true; }

  protected function do_delay( $ns_min, $ns_max ) {

    $this->php_time_nanosleep( 0, KICKASS_CRYPTO_DELAY_NANOSECONDS_MIN );

  }
}

class ValidCrypto extends TestCrypto {

  protected function do_get_passphrase_list() {

    static $result = null;

    if ( $result === null ) { $result = [ $this->convert_secret_to_passphrase( 'whatever' ) ]; }

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
      'expected' => $expected_error,
      'class' => get_class( $crypto ),
      'result' => $result,
      'error_list' => $crypto->get_error_list(),
    ]);

    throw new \Exception( 'error: ' . json_encode( [
      'expected' => $expected_error,
      'class' => get_class( $crypto ),
      'result' => $result,
      'error_list' => $crypto->get_error_list(),
    ], JSON_PARTIAL_OUTPUT_ON_ERROR ) );

  }

  assert( $result === false );

  $error = $crypto->get_error();
  $error_list = $crypto->get_error_list();

  if ( count( $error_list ) === 0 ) {

    var_dump([
      'expected' => $expected_error,
      'class' => get_class( $crypto ),
      'result' => $result,
      'error_list' => $crypto->get_error_list(),
    ]);

    throw new \Exception( 'error: ' . json_encode( [
      'expected' => $expected_error,
      'class' => get_class( $crypto ),
      'result' => $result,
      'error_list' => $crypto->get_error_list(),
    ], JSON_PARTIAL_OUTPUT_ON_ERROR ) );

  }

  assert( count( $error_list ) > 0 );

  if ( $error !== $expected_error ) {

    throw new \Exception(
      'error: ' . json_encode( [ 'error' => $error, 'expected' => $expected_error ] )
    );

    var_dump([
      'error_list' => $error_list,
      'expected_error' => $expected_error,
    ]);

  }

  assert( $error === $expected_error );

}
