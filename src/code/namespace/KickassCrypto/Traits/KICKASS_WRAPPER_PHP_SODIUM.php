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
 * 2023-04-04 jj5 - these are wrappers for the Sodium library functions.
 */

namespace KickassCrypto\Traits;

/**
 * 2023-04-07 jj5 - thin wrappers around PHP functions for the Sodium library.
 *
 * 2023-04-07 jj5 - the functions in this trait are implemented using the typed final wrapper
 * idiom discussed at the following link:
 *
 * @link https://github.com/jj5/kickass-crypto#typed-final-wrapper-idiom
 *
 * 2023-04-07 jj5 - this file doesn't extensively document itself as the relevant reference is
 * the PHP documentation for the relevant function.
 */
trait KICKASS_WRAPPER_PHP_SODIUM {

  protected final function php_sodium_crypto_secretbox( $plaintext, $nonce, $passphrase ) {

    try {

      return $this->do_php_sodium_crypto_secretbox( $plaintext, $nonce, $passphrase );

    }
    catch ( \Throwable $ex ) {

      $this->notify( $ex, __FILE__, __LINE__, __FUNCTION__ );

      throw $ex;

    }
  }

  protected function do_php_sodium_crypto_secretbox( $plaintext, $nonce, $passphrase ) {

    return sodium_crypto_secretbox( $plaintext, $nonce, $passphrase );

  }

  protected final function php_sodium_crypto_secretbox_open( $plaintext, $nonce, $passphrase ) {

    try {

      return $this->do_php_sodium_crypto_secretbox_open( $plaintext, $nonce, $passphrase );

    }
    catch ( \Throwable $ex ) {

      $this->notify( $ex, __FILE__, __LINE__, __FUNCTION__ );

      throw $ex;

    }
  }

  protected function do_php_sodium_crypto_secretbox_open( $plaintext, $nonce, $passphrase ) {

    return sodium_crypto_secretbox_open( $plaintext, $nonce, $passphrase );

  }
}
