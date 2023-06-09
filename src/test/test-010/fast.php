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
 * 2023-04-02 jj5 - this test takes binary parsing for a spin
 *
 * @link https://github.com/jj5/kickass-crypto
 */

//define( 'DEBUG', true );

require_once __DIR__ . '/../../../inc/test-host.php';

class TestCrypto extends \KickassCrypto\OpenSsl\KickassOpenSslRoundTrip {

  use \KickassCrypto\Traits\KICKASS_DEBUG;

  private $ivlen = 1;
  private $taglen = 1;

  protected function get_const_openssl_iv_length() { return $this->ivlen; }
  protected function get_const_openssl_tag_length() { return $this->taglen; }

  public function set( $ivlen, $taglen ) {

    $this->ivlen = $ivlen;
    $this->taglen = $taglen;

  }

  public function test() {

    $this->set( 0, 0 );

    $this->test_binary( '2', '', '2', '' );
    $this->test_binary( '22', '', '22', '' );
    $this->test_binary( '222', '', '222', '' );

    $this->set( 1, 1 );

    $this->test_binary( '123', '1', '2', '3' );
    $this->test_binary( '1223', '1', '22', '3' );
    $this->test_binary( '12223', '1', '222', '3' );

    $this->set( 2, 2 );

    $this->test_binary( '11233', '11', '2', '33' );
    $this->test_binary( '112233', '11', '22', '33' );
    $this->test_binary( '1122233', '11', '222', '33' );

  }

  protected function test_binary( $binary, $expect_iv, $expect_ciphertext, $expect_tag ) {

    if ( false ) {

      var_dump([
        'binary' => $binary,
        'expect_iv' => $expect_iv,
        'expect_ciphertext' => $expect_ciphertext,
        'expect_tag' => $expect_tag,
      ]);

    }

    $this->parse_binary( $binary, $iv, $ciphertext, $tag );

    if (
      $expect_iv !== $iv ||
      $expect_ciphertext !== $ciphertext ||
      $expect_tag !== $tag
    ) {

      var_dump([
        'binary' => $binary,
        'iv' => $iv,
        'ciphertext' => $ciphertext,
        'tag' => $tag,
        'expect_iv' => $expect_iv,
        'expect_ciphertext' => $expect_ciphertext,
        'expect_tag' => $expect_tag,
      ]);

    }

    assert( $expect_iv === $iv );
    assert( $expect_ciphertext === $ciphertext );
    assert( $expect_tag === $tag );

  }
}

function run_test() {

  define( 'KICKASS_CRYPTO_DISABLE_IV_LENGTH_VALIDATION', true );

  $crypto = new TestCrypto();

  $crypto->test();

}

main( $argv );
