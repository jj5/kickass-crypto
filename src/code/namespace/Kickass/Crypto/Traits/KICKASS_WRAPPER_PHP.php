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
// 2023-03-30 jj5 - these are indirections to PHP functions. The main reason for using these is
// so that we can use them to inject errors during testing... some PHP functions such as
// is_int(), intval() and round() are called directly and not via these indirections. If you need
// to be able to inject invalid return values during testing this is the place to make such
// arrangements to do such things.
//
// 2023-03-31 jj5 - NOTE: these wrappers should do as little as possible and just defer entirely
// to the PHP implementation. One exception is that I like to initialize variables passed by
// reference to null, this is probably not necessary but it gives me the warm and fuzzies.
//
// 2023-03-31 jj5 - NOTE: when defining default variables you should use the same default values
// as the library functions you are calling use, or just don't provide a default value at all;
// that's a sensible enough option, you can make the wrapper demand a value from the caller if
// you want.
//
// 2023-04-02 jj5 - NOTE: the only assumption this trait makes about its environment is that a
// catch() method has been defined to notify exceptions. After exceptions are notified they are
// rethrown.
//
\************************************************************************************************/

namespace Kickass\Crypto\Traits;

trait KICKASS_WRAPPER_PHP {

  protected final function php_base64_encode( $input ) {

    try {

      return $this->do_php_base64_encode( $input );

    }
    catch ( \Throwable $ex ) {

      $this->catch( $ex, __FILE__, __LINE__, __FUNCTION__ );

      throw $ex;

    }
  }

  protected function do_php_base64_encode( $input ) {

    return base64_encode( $input );

  }

  protected final function php_base64_decode( $input, $strict ) {

    try {

      return $this->do_php_base64_decode( $input, $strict );

    }
    catch ( \Throwable $ex ) {

      $this->catch( $ex, __FILE__, __LINE__, __FUNCTION__ );

      throw $ex;

    }
  }

  protected function do_php_base64_decode( $input, $strict ) {

    return base64_decode( $input, $strict );

  }

  protected final function php_json_encode( $value, $flags, $depth = 512 ) {

    try {

      return $this->do_php_json_encode( $value, $flags, $depth );

    }
    catch ( \Throwable $ex ) {

      $this->catch( $ex, __FILE__, __LINE__, __FUNCTION__ );

      throw $ex;

    }
  }

  protected function do_php_json_encode( $value, $flags, $depth = 512 ) {

    return json_encode( $value, $flags, $depth );

  }

  protected final function php_json_decode( $json, $associative, $depth, $flags ) {

    try {

      return $this->do_php_json_decode( $json, $associative, $depth, $flags );

    }
    catch ( \Throwable $ex ) {

      $this->catch( $ex, __FILE__, __LINE__, __FUNCTION__ );

      throw $ex;

    }
  }

  protected function do_php_json_decode( $json, $associative, $depth, $flags ) {

    return json_decode( $json, $associative, $depth, $flags );

  }

  protected final function php_serialize( $value ) {

    try {

      return $this->do_php_serialize( $value );

    }
    catch ( \Throwable $ex ) {

      $this->catch( $ex, __FILE__, __LINE__, __FUNCTION__ );

      throw $ex;

    }
  }

  protected function do_php_serialize( $value ) {

    return serialize( $value );

  }

  protected final function php_unserialize( $value ) {

    try {

      return $this->do_php_unserialize( $value );

    }
    catch ( \Throwable $ex ) {

      $this->catch( $ex, __FILE__, __LINE__, __FUNCTION__ );

      throw $ex;

    }
  }

  protected function do_php_unserialize( $value ) {

    return unserialize( $value );

  }

  protected final function php_random_int( $min, $max ) {

    try {

      return $this->do_php_random_int( $min, $max );

    }
    catch ( \Throwable $ex ) {

      $this->catch( $ex, __FILE__, __LINE__, __FUNCTION__ );

      throw $ex;

    }
  }

  protected function do_php_random_int( $min, $max ) {

    return random_int( $min, $max );

  }

  protected final function php_random_bytes( $length ) {

    try {

      return $this->do_php_random_bytes( $length );

    }
    catch ( \Throwable $ex ) {

      $this->catch( $ex, __FILE__, __LINE__, __FUNCTION__ );

      throw $ex;

    }
  }

  protected function do_php_random_bytes( $length ) {

    return random_bytes( $length );

  }

  protected final function php_time_nanosleep( $seconds, $nanoseconds ) {

    try {

      return $this->do_php_time_nanosleep( $seconds, $nanoseconds );

    }
    catch ( \Throwable $ex ) {

      $this->catch( $ex, __FILE__, __LINE__, __FUNCTION__ );

      throw $ex;

    }
  }

  protected function do_php_time_nanosleep( $seconds, $nanoseconds ) {

    return time_nanosleep( $seconds, $nanoseconds );

  }

  protected final function php_sapi_name() {

    try {

      return $this->do_php_sapi_name();

    }
    catch ( \Throwable $ex ) {

      $this->catch( $ex, __FILE__, __LINE__, __FUNCTION__ );

      throw $ex;

    }
  }

  protected function do_php_sapi_name() {

    return php_sapi_name();

  }
}
