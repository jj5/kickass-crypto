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
 * 2023-04-04 jj5 - sets you up with support for round-tripping...
 *
 * @link https://github.com/jj5/kickass-crypto
 */

namespace KickassCrypto\Traits;

/**
 * 2023-04-07 jj5 - defines how the passphrase list is generated for round-tripo use cases.
 */
trait KICKASS_CRYPTO_ROUND_TRIP {

  /**
   * 2023-04-07 jj5 - generates a passphrase list by hashing one or both secrets.
   *
   * @return array the list of strings of hashed secret keys to use as the passphrase list.
   */
  protected function generate_passphrase_list() {

    $secret_curr = $this->get_config_secret_curr();
    $secret_prev = $this->get_config_secret_prev();

    $result = [ $this->convert_secret_to_passphrase( $secret_curr ) ];

    if ( $secret_prev ) {

      $result[] = $this->convert_secret_to_passphrase( $secret_prev );

    }

    return $result;

  }
}