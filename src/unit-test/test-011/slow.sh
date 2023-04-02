#!/bin/bash

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

      error "Unexpected output:";

      cat "$tempfile";

    };

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
