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
 * 2023-03-30 jj5 - we define an exception class for this component so that we can associate
 * custom data with our exceptions... note that not all exceptions will have associated data.
 *
 * @link https://github.com/jj5/kickass-crypto
 */

namespace KickassCrypto;

/**
 * 2023-04-05 jj5 - this exception is thrown from crypto service constructors (and elsewhere) if
 * there's a problem that will prevent them from operating.
 */
class KickassCryptoException extends \Exception {

  private $data;

  /**
   * 2023-04-07 jj5 - creates a new instance of this class; calls the parent constructor and
   * records the associated data, if any.
   *
   * @param string $message the exception message.
   *
   * @param int $code the exception code.
   *
   * @param Throwable $previous the previous exception or null if none.
   *
   * @param mixed $data optional data to associate with this exception.
   */
  public function __construct( $message, $code = 0, $previous = null, $data = null ) {

    parent::__construct( $message, $code, $previous );

    $this->data = $data;

  }

  /**
   * 2023-04-07 jj5 - an accessor for the associated data, if any.
   *
   * @return mixed can be antying.
   */
  public function getData() { return $this->data; }

}
