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
// 2023-04-04 jj5 - this implements support for round-tripping with Sodium.
//
\************************************************************************************************/

namespace Kickass\Crypto\Module\Sodium;

class KickassSodiumRoundTrip extends KickassSodium {

  use \Kickass\Crypto\Traits\KICKASS_CRYPTO_ROUND_TRIP;

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

    $secret_curr = $this->get_config_secret_curr();
    $secret_prev = $this->get_config_secret_prev();

    if ( ! $secret_curr ) {

      $problem = KICKASS_CRYPTO_CONFIG_PROBLEM_MISSING_SODIUM_SECRET_CURR;

      return false;

    }

    if ( ! $this->is_valid_secret( $secret_curr ) ) {

      $problem = KICKASS_CRYPTO_CONFIG_PROBLEM_INVALID_SODIUM_SECRET_CURR;

      return false;

    }

    if ( $secret_prev && ! $this->is_valid_secret( $secret_prev ) ) {

      $problem = KICKASS_CRYPTO_CONFIG_PROBLEM_INVALID_SODIUM_SECRET_PREV;

      return false;

    }

    $problem = null;

    return true;

  }
}
