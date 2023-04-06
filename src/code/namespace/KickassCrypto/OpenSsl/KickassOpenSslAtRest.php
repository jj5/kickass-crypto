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
 * 2023-04-04 jj5 - this implements support for at-rest encryption with OpenSSL.
 *
 * @link https://github.com/jj5/kickass-crypto
 */

namespace KickassCrypto\OpenSsl;

/**
 * 2023-04-05 jj5 - this is the OpenSSL at-rest crypto service.
 */
class KickassOpenSslAtRest extends KickassOpenSsl {

  use \KickassCrypto\Traits\KICKASS_CRYPTO_AT_REST;

  protected function do_get_passphrase_list() {

    // 2023-03-30 jj5 - we cache the generated passphrase list in a static variable so we don't
    // have to constantly regenerate it and because we don't want to put this sensitive data
    // into an instance field. If you don't want the passphrase list stored in a static variable
    // override this method and implement differently.

    static $result = null;

    if ( $result === null ) { $result = $this->generate_passphrase_list(); }

    return $result;

  }

  protected function do_is_valid_config( &$problem ) {

    $secret_list = $this->get_config_secret_list();

    if ( $secret_list === false ) {

      $problem = KICKASS_CRYPTO_CONFIG_PROBLEM_MISSING_OPENSSL_SECRET_LIST;

      return false;

    }

    if ( ! is_array( $secret_list ) || count( $secret_list ) === 0 ) {

      $problem = KICKASS_CRYPTO_CONFIG_PROBLEM_INVALID_OPENSSL_SECRET_LIST;

      return false;

    }

    foreach ( $secret_list as $secret ) {

      if ( ! $this->is_valid_secret( $secret ) ) {

        $problem = KICKASS_CRYPTO_CONFIG_PROBLEM_INVALID_OPENSSL_SECRET_LIST;

        return false;

      }
    }

    $problem = null;

    return true;

  }
}
