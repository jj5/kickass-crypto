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
// 2023-04-04 jj5 - the service locators will default to using the Sodium module. These are the
// service locators which will be included by default.
//
\************************************************************************************************/

/**
 * 2023-04-05 jj5 - this is the round-trip service locator defined for use by the Sodium module.
 * This component will use the round-trip keys defined for the Sodium module, those keys are
 * defined with the CONFIG_SODIUM_SECRET_CURR configuration constant (required) and the
 * CONFIG_SODIUM_SECRET_PREV configuration constant (optional).
 *
 * @param KickassCrypto\IKickassCrypto $set pass a valid instance to reconfigure the
 * service locator with a new service instance.
 *
 * @return KickassCrypto\IKickassCrypto the crypto interface.
 *
 * @throws KickassCrypto\KickassCryptoException if the environment is determined to be
 * unsupported during construction.
*/
function kickass_round_trip( $set = false ) : \KickassCrypto\IKickassCrypto {

  static $instance = null;

  if ( $set !== false ) { $instance = $set; }

  if ( $instance === null ) {

    $instance = new \KickassCrypto\Sodium\KickassSodiumRoundTrip();

  }

  return $instance;

}

/**
 * 2023-04-05 jj5 - this is the at-rest service locator defined for use by the Sodium module.
 * This component will use the at-rest keys defined for the Sodium module, those keys are
 * defined with the CONFIG_SODIUM_SECRET_LIST configuration constant.
 *
 * @param KickassCrypto\IKickassCrypto $set pass a valid instance to reconfigure the
 * service locator with a new service instance.
 *
 * @return KickassCrypto\IKickassCrypto the crypto interface.
 *
 * @throws KickassCrypto\KickassCryptoException if the environment is determined to be
 * unsupported during construction.
*/
function kickass_at_rest( $set = false ) : \KickassCrypto\IKickassCrypto {

  static $instance = null;

  if ( $set !== false ) { $instance = $set; }

  if ( $instance === null ) {

    $instance = new \KickassCrypto\Sodium\KickassSodiumAtRest();

  }

  return $instance;

}
