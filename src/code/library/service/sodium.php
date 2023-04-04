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
// 2023-03-30 jj5 - these two service locator functions will automatically create appropriate
// encryption components for each use case. If you want to override with a different
// implementation you can pass in a new instance, or you can manage construction yourself and
// access some other way. These functions are how you should ordinarily access this library.
//
\************************************************************************************************/

function kickass_round_trip( $set = false ) : \Kickass\Crypto\Contract\IKickassCrypto {

  static $instance = null;

  if ( $set !== false ) { $instance = $set; }

  if ( $instance === null ) {

    // 2023-04-03 jj5 - prefer Sodium...

    $instance = new \Kickass\Crypto\Module\Sodium\KickassSodiumRoundTrip();

  }

  return $instance;

}

function kickass_at_rest( $set = false ) : \Kickass\Crypto\Contract\IKickassCrypto {

  static $instance = null;

  if ( $set !== false ) { $instance = $set; }

  if ( $instance === null ) {

    // 2023-04-03 jj5 - prefer Sodium...

    $instance = new \Kickass\Crypto\Module\Sodium\KickassSodiumAtRest();

  }

  return $instance;

}
