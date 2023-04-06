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
 * 2023-04-02 jj5 - if you include this trait you'll be set up with a test key and a valid
 * config. The secret key isn't kept anywhere so you won't be able to decrypt data after your
 * test completes.
 */

namespace KickassCrypto\Traits;

/**
 * 2023-04-07 jj5 - these traits are defined for debugging purposes; they generate single use
 * secret keys and declare a valid environment so you don't have to worry about providing a valid
 *  config file; and the delay for the minimum amount of time when delay is invoked due to error.
 */
trait KICKASS_DEBUG_KEYS {

  /**
   * 2023-04-07 jj5 - the environment is considered valid and there is no problem. Honest.
   *
   * @param string $problem always null.
   * @return boolean always true.
   */
  protected function do_is_valid_config( &$problem ) { $problem = null; return true; }

  /**
   * 2023-04-07 jj5 - a list of one element containing a single use passphrase. As this
   * passphrase isn't stored anywhere it will be impossible to decrypt data encrypted using it
   * after the process has exited.
   *
   * @staticvar array $list the cached passphrase list to use for debugging.
   *
   * @return array the list of passphrases.
   */
  protected function do_get_passphrase_list() {

    static $list = null;

    if ( $list === null ) {

      $secret = self::GenerateSecret();

      $passphrase = $this->convert_secret_to_passphrase( $secret );

      $list = [ $passphrase ];

    }

    return $list;

  }
}
