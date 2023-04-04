#!/bin/bash

##################################################################################################
#                                                                                                #
#  ____  __.__        __                           _________                        __           #
# |    |/ _|__| ____ |  | _______    ______ ______ \_   ___ \_______ ___.__._______/  |_  ____   #
# |      < |  |/ ___\|  |/ /\__  \  /  ___//  ___/ /    \  \/\_  __ <   |  |\____ \   __\/  _ \  #
# |    |  \|  \  \___|    <  / __ \_\___ \ \___ \  \     \____|  | \/\___  ||  |_> >  | (  <_> ) #
# |____|__ \__|\___  >__|_ \(____  /____  >____  >  \______  /|__|   / ____||   __/|__|  \____/  #
#         \/       \/     \/     \/     \/     \/          \/        \/     |__|                 #
#                                                                                                #
#                                                                                        By jj5  #
#                                                                                                #
##################################################################################################

##################################################################################################
#
# 2023-03-31 jj5 - this shell script invokes various instances of fast.php for both tests which
# are expected to succeed and tests which are expected to fail. In order to get a list of
# available tests we run the fast.php without the test index specified and it reports the list
# of available tests for the given mode. The two modes are 'work' and 'fail', the former are
# expected to succeed and the latter are expected to fail.
#
##################################################################################################

QUIET=1
DEBUG=0

main() {

  set -euo pipefail;

  pushd "$( dirname "$0" )" >/dev/null;

  # 2023-03-31 jj5 - we run the fast.php script with the mode as 'fail' and no test specified
  # to get the list of tests, then we run the tests
  #
  for spec in $( php fast.php fail || true ); do

    test_fail $spec

  done

  # 2023-03-31 jj5 - we run the fast.php script with the mode as 'work' and no test specified
  # to get the list of tests, then we run the tests
  #
  for spec in $( php fast.php work || true ); do

    test_work $spec

  done

}

test_fail() {

  local test="$1";

  local output=/dev/null

  if [ "$DEBUG" == 1 ]; then

    output=/dev/stdout

  fi

  php fast.php fail $test 2>$output || {

    local error="$?";

    report "test failed, as expected.";

    [ "$error" == '83' ] && {

      report "error level was: $error, as expected.";

      return 0;

    };

    error "error level was: $error, this was unexpected.";

    return 1;

  }

  error "test fail:$test did not fail as expected.";

}

test_work() {

  local test="$1";

  local output=/dev/null

  if [ "$DEBUG" == 1 ]; then

    output=/dev/stdout

  fi

  php fast.php work $test 2>$output && {

    report "test worked, as expected.";

    return 0;

  }

  local error="$?";

  error "test work:$test failed, which was not expected.";

  error "error level was: $error.";

  return 1;

}

error() {

  echo "$@";

}

report() {

  [ "$QUIET" == '1' ] && { return 0; }

  echo "$@";

}

main "$@";
