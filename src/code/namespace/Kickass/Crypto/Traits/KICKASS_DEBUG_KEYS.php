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
// 2023-04-02 jj5 - if you include this trait you'll be set up with a test key and a valid
// config. The secret key isn't kept anywhere so you won't be able to decrypt data after your
// test completes.
//
\************************************************************************************************/

namespace Kickass\Crypto\Traits;

trait KICKASS_DEBUG_KEYS {

  protected function is_valid_config( &$problem = null ) { $problem = null; return true; }

  protected function get_passphrase_list() {
    static $list = null;
    if ( $list === null ) {
      $secret = self::GenerateSecret();
      $passphrase = $this->convert_secret_to_passphrase( $secret );
      $list = [ $passphrase ];
    }
    return $list;
  }
}
