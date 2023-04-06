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
 * 2023-04-06 jj5 - this minimizes the delay for errors during testing.
 */

namespace KickassCrypto\Traits;

/**
 * 2023-04-07 jj5 - these traits are defined for debugging purposes; will delay for the minimum
 * amount of time when delay is invoked due to error.
 */
trait KICKASS_DEBUG_WITHOUT_DELAY {

  /**
   * 2023-04-07 jj5 - delay for the shortest time allowed.
   *
   * @param int $ns_min the minimum time nominated by the caller, this is what we use.
   *
   * @param int $ns_max the maximum time nominated by the caller, this is ignored.
   */
  protected function do_delay( $ns_min, $ns_max ) {

    $this->php_time_nanosleep( 0, $ns_min );

  }
}
