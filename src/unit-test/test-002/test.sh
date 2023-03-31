#!/bin/bash

main() {

  set -euo pipefail;

  pushd "$( dirname "$0" )" >/dev/null;

  php test.php KickassCryptoRoundTrip KICKASS_CRYPTO_CONFIG_PROBLEM_MISSING_SECRET_CURR
  php test.php KickassCryptoRoundTrip KICKASS_CRYPTO_CONFIG_PROBLEM_INVALID_SECRET_CURR
  php test.php KickassCryptoRoundTrip KICKASS_CRYPTO_CONFIG_PROBLEM_INVALID_SECRET_PREV

  php test.php KickassCryptoAtRest KICKASS_CRYPTO_CONFIG_PROBLEM_MISSING_SECRET_LIST
  php test.php KickassCryptoAtRest KICKASS_CRYPTO_CONFIG_PROBLEM_INVALID_SECRET_LIST

}

main "$@";
