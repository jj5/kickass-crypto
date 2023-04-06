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
 * 2023-04-04 jj5 - this implements support for round-tripping with Sodium.
 *
 * @link https://github.com/jj5/kickass-crypto
 */

namespace KickassCrypto\Sodium;

/**
 * 2023-04-05 jj5 - this is the Sodium round-trip crypto service.
 */
class KickassSodiumRoundTrip extends KickassSodium {

  use \KickassCrypto\Traits\KICKASS_CRYPTO_ROUND_TRIP;

  /**
   * 2023-04-07 jj5 - gets the lis of passphrases to use for this use case; these are stashed in
   * a static variable which is used for all instances of this class, which should be fine
   * because these come from a config file so they're essentially global/static data anyway and
   * caching means we can avoid having to regenerate them if they're needed more than once.
   *
   * The passphrases are generated from the secret keys. The secret keys are what the user puts
   * in the config file, and the passphrases are a hash of a secret key used by the encryption
   * library, in this case Sodium. The user needn't know anything about passphrases, they are
   * an internal implementation detail. Good valid passphrases should be 32 bytes long and should
   * be highly random.
   *
   * This function generates the keys if it needs to by calling generate_passphrase_list()
   * which is defined on the round-trip traits used by this class, see
   * KickassCrypto\Traits\KICKASS_CRYPTO_ROUND_TRIP for details.
   *
   * @staticvar array $result the static variable that holds the generated list of passphrases.
   *
   * @return array the list of passphrases to use for encryption and decryption.
   */
  protected function do_get_passphrase_list() {

    // 2023-03-30 jj5 - we cache the generated passphrase list in a static variable so we don't
    // have to constantly regenerate it and because we don't want to put this sensitive data
    // into an instance field. If you don't want the passphrase list stored in a static variable
    // override this method and implement differently.

    static $result = null;

    if ( $result === null ) { $result = $this->generate_passphrase_list(); }

    return $result;

  }

  /**
   * 2023-04-07 jj5 - this defines what a valid configuration looks like; basically we just need
   * a valid current secret key and can have an optional extra previous secret key.
   *
   * @param string $problem the config problem, or null if no problem.
   *
   * @return boolean true when config is valid, false otherwise.
   */
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
