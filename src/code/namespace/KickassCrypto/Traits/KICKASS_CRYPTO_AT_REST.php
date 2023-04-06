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
 * 2023-04-04 jj5 - sets you up with support for at-rest use cases...
 *
 */

namespace KickassCrypto\Traits;

/**
 * 2023-04-07 jj5 - defines how the passphrase list is generated for at-rest use cases.
 */
trait KICKASS_CRYPTO_AT_REST {

  /**
   * 2023-04-07 jj5 - generates a passphrase list by hashing each secret in the secret list.
   *
   * @return array the list of strings of hashed secret keys to use as the passphrase list.
   */
  protected function generate_passphrase_list() {

    $secret_list = $this->get_config_secret_list();
    $result = [];

    foreach ( $secret_list as $secret ) {

      $result[] = $this->convert_secret_to_passphrase( $secret );

    }

    return $result;

  }
}