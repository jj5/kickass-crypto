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
// 2023-04-04 jj5 - bit twiddling helpers...
//
\************************************************************************************************/

/**
 * 2023-04-05 jj5 - this function tests to see if a given flag is set within a set of flags.
 *
 * @param int $flags the flags which are specified.
 * @param int $flag the flag which you're interested in.
 * @return boolean true if the flag is set.
 */
function kickass_crypto_is_set( int $flags, int $flag ) {

  return $flag === ( $flags & $flag );

}

/**
 * 2023-04-05 jj5 - this function splits a set of flags into a list of singular flag values.
 * @param int $flags the flags which are specified.
 * @return array the list of singular flag values.
 */
function kickass_crypto_bits_split( int $flags ) {

  $result = [];

  for ( $n = 0; $n < 63; $n++ ) {

    $flag = pow( 2, $n );

    if ( kickass_crypto_is_set( $flags, $flag ) ) { $result[] = $flag; }

  }

  return $result;

}
