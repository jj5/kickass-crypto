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
 * 2023-04-05 jj5 - these tests make sure we can decode things that were encrypted using a
 * different data encoding than the one that we're currently using.
 *
 * @link https://github.com/jj5/kickass-crypto
 */

//define( 'DEBUG', true );

require_once __DIR__ . '/etc/config.php';
require_once __DIR__ . '/../../../inc/test-host.php';

class TestJsonWithoutPhps extends \KickassCrypto\OpenSsl\KickassOpenSslRoundTrip {

  use \KickassCrypto\Traits\KICKASS_DEBUG_LOG;

  protected function do_get_config_data_encoding( $default ) {

    return KICKASS_CRYPTO_DATA_ENCODING_JSON;

  }

  protected function do_delay( $ns_min, $ns_max ) {

    $this->php_time_nanosleep( 0, KICKASS_CRYPTO_DELAY_NANOSECONDS_MIN );

  }
}

class TestJsonWithPhps extends \KickassCrypto\OpenSsl\KickassOpenSslRoundTrip {

  use \KickassCrypto\Traits\KICKASS_DEBUG_LOG;

  protected function do_get_config_data_encoding( $default ) {

    return KICKASS_CRYPTO_DATA_ENCODING_JSON;

  }

  protected function do_get_config_phps_enable( $default ) {

    return true;

  }

  protected function do_delay( $ns_min, $ns_max ) {

    $this->php_time_nanosleep( 0, KICKASS_CRYPTO_DELAY_NANOSECONDS_MIN );

  }
}

class TestPhpsWithoutPhps extends \KickassCrypto\OpenSsl\KickassOpenSslRoundTrip {

  use \KickassCrypto\Traits\KICKASS_DEBUG_LOG;

  protected function do_get_config_data_encoding( $default ) {

    return KICKASS_CRYPTO_DATA_ENCODING_PHPS;

  }

  protected function do_delay( $ns_min, $ns_max ) {

    $this->php_time_nanosleep( 0, KICKASS_CRYPTO_DELAY_NANOSECONDS_MIN );

  }
}

class TestPhpsWithPhps extends \KickassCrypto\OpenSsl\KickassOpenSslRoundTrip {

  use \KickassCrypto\Traits\KICKASS_DEBUG_LOG;

  protected function do_get_config_data_encoding( $default ) {

    return KICKASS_CRYPTO_DATA_ENCODING_PHPS;

  }

  protected function do_get_config_phps_enable( $default ) {

    return true;

  }

  protected function do_delay( $ns_min, $ns_max ) {

    $this->php_time_nanosleep( 0, KICKASS_CRYPTO_DELAY_NANOSECONDS_MIN );

  }
}

class TestTextWithoutPhps extends \KickassCrypto\OpenSsl\KickassOpenSslRoundTrip {

  use \KickassCrypto\Traits\KICKASS_DEBUG_LOG;

  protected function do_get_config_data_encoding( $default ) {

    return KICKASS_CRYPTO_DATA_ENCODING_TEXT;

  }

  protected function do_delay( $ns_min, $ns_max ) {

    $this->php_time_nanosleep( 0, KICKASS_CRYPTO_DELAY_NANOSECONDS_MIN );

  }
}

class TestTextWithPhps extends \KickassCrypto\OpenSsl\KickassOpenSslRoundTrip {

  use \KickassCrypto\Traits\KICKASS_DEBUG_LOG;

  protected function do_get_config_data_encoding( $default ) {

    return KICKASS_CRYPTO_DATA_ENCODING_TEXT;

  }

  protected function do_get_config_phps_enable( $default ) {

    return true;

  }

  protected function do_delay( $ns_min, $ns_max ) {

    $this->php_time_nanosleep( 0, KICKASS_CRYPTO_DELAY_NANOSECONDS_MIN );

  }
}

function run_test() {

  $json_with = new TestJsonWithPhps();
  $json_without = new TestJsonWithoutPhps();
  $phps_with = new TestPhpsWithPhps();
  $phps_without = new TestPhpsWithoutPhps();
  $text_with = new TestTextWithPhps();
  $text_without = new TestTextWithoutPhps();

  global $secret;

  $secret = 'secret';

  test_success( $json_with, $json_without );
  test_success( $json_with, $phps_without );
  test_success( $json_with, $text_without );

  test_success( $json_without, $json_with );
  test_success( $json_without, $phps_with );
  test_success( $json_without, $text_with );

  test_error( $phps_with, $phps_without );
  test_error( $phps_with, $json_without );
  test_error( $phps_with, $text_without );

  test_error( $phps_without, $phps_with );
  test_error( $phps_without, $json_with );
  test_error( $phps_without, $text_with );

  test_success( $text_with, $json_without );
  test_success( $text_with, $phps_without );
  test_success( $text_with, $text_without );

  test_success( $text_without, $json_with );
  test_success( $text_without, $phps_with );
  test_success( $text_without, $text_with );

}

function test_success( $encryptor, $decryptor ) {

  global $secret;

  $ciphertext = $encryptor->encrypt( $secret );

  $plaintext = $decryptor->decrypt( $ciphertext );

  assert( $plaintext === $secret );

}

function test_error( $encryptor, $decryptor ) {

  global $secret;

  $ciphertext = $encryptor->encrypt( $secret );

  $plaintext = $decryptor->decrypt( $ciphertext );

  assert( $plaintext !== $secret );

}

main( $argv );
