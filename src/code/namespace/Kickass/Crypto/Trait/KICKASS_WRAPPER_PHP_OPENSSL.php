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
// 2023-04-04 jj5 - these are wrappers for the OpenSSL library functions.
//
\************************************************************************************************/

namespace Kickass\Crypto\Traits;

trait KICKASS_WRAPPER_PHP_OPENSSL {

  protected final function php_openssl_get_cipher_methods() {
    try {

      return $this->do_php_openssl_get_cipher_methods();

    }
    catch ( \Throwable $ex ) {

      $this->catch( $ex, __FILE__, __LINE__, __FUNCTION__ );

      throw $ex;

    }
  }

  protected function do_php_openssl_get_cipher_methods() {

    return openssl_get_cipher_methods();

  }

  protected final function php_openssl_cipher_iv_length( $cipher ) {

    try {

      return $this->do_php_openssl_cipher_iv_length( $cipher );

    }
    catch ( \Throwable $ex ) {

      $this->catch( $ex, __FILE__, __LINE__, __FUNCTION__ );

      throw $ex;

    }
  }

  protected function do_php_openssl_cipher_iv_length( $cipher ) {

    return openssl_cipher_iv_length( $cipher );

  }

  protected final function php_openssl_error_string() {

    try {

      return $this->do_php_openssl_error_string();

    }
    catch ( \Throwable $ex ) {

      $this->catch( $ex, __FILE__, __LINE__, __FUNCTION__ );

      throw $ex;

    }
  }

  protected function do_php_openssl_error_string() {

    return openssl_error_string();

  }

  protected final function php_openssl_encrypt(
    $plaintext,
    $cipher,
    $passphrase,
    $options,
    $iv,
    &$tag
  ) {

    $tag = null;

    try {

      return $this->do_php_openssl_encrypt( $plaintext, $cipher, $passphrase, $options, $iv, $tag );

    }
    catch ( \Throwable $ex ) {

      $this->catch( $ex, __FILE__, __LINE__, __FUNCTION__ );

      throw $ex;

    }
  }

  protected function do_php_openssl_encrypt(
    $plaintext,
    $cipher,
    $passphrase,
    $options,
    $iv,
    &$tag
  ) {

    $tag = null;

    return openssl_encrypt( $plaintext, $cipher, $passphrase, $options, $iv, $tag );

  }

  protected final function php_openssl_decrypt(
    $ciphertext,
    $cipher,
    $passphrase,
    $options,
    $iv,
    $tag
  ) {

    try {

      return $this->do_php_openssl_decrypt( $ciphertext, $cipher, $passphrase, $options, $iv, $tag );

    }
    catch ( \Throwable $ex ) {

      $this->catch( $ex, __FILE__, __LINE__, __FUNCTION__ );

      throw $ex;

    }
  }

  protected function do_php_openssl_decrypt(
    $ciphertext,
    $cipher,
    $passphrase,
    $options,
    $iv,
    $tag
  ) {

    return openssl_decrypt( $ciphertext, $cipher, $passphrase, $options, $iv, $tag );

  }
}
