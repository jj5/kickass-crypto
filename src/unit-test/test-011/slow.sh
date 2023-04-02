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
# 2023-04-03 jj5 - this script runs our tests, indicating which sleep method to use.
#
##################################################################################################

QUIET=1

main() {

  set -euo pipefail;

  pushd "$( dirname "$0" )" >/dev/null;

  run_test nano

  run_test micro

}

run_test() {

  local tempfile=$( mktemp );
  local test="$1";

  if php slow.php "$test" 2> "$tempfile"; then

    grep "KickassCrypto.php: emergency delay: ${test}sleep" "$tempfile" >/dev/null || {

      error "test failed: $test";

      cat "$tempfile";

      rm "$tempfile";

      exit 54

    };

    report "test successful: $test";

  else

    local error="$?"

    error "test failed with error level: $error";

    exit "$error";

  fi

  rm "$tempfile";

}

error() {

  echo "$@";

}

report() {

  [ "$QUIET" == '1' ] && { return 0; }

  echo "$@";

}

main "$@";
