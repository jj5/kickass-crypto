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
# 2023-04-03 jj5 - this script runs our tests indicating which config file to load.
#
##################################################################################################

main() {

  set -euo pipefail;

  pushd "$( dirname "$0" )" >/dev/null;

  php fast.php KickassSodiumRoundTrip KICKASS_CRYPTO_CONFIG_PROBLEM_MISSING_SODIUM_SECRET_CURR
  php fast.php KickassSodiumRoundTrip KICKASS_CRYPTO_CONFIG_PROBLEM_INVALID_SODIUM_SECRET_CURR
  php fast.php KickassSodiumRoundTrip KICKASS_CRYPTO_CONFIG_PROBLEM_INVALID_SODIUM_SECRET_PREV

  php fast.php KickassSodiumAtRest KICKASS_CRYPTO_CONFIG_PROBLEM_MISSING_SODIUM_SECRET_LIST
  php fast.php KickassSodiumAtRest KICKASS_CRYPTO_CONFIG_PROBLEM_INVALID_SODIUM_SECRET_LIST

  php fast.php KickassOpenSslRoundTrip KICKASS_CRYPTO_CONFIG_PROBLEM_MISSING_OPENSSL_SECRET_CURR
  php fast.php KickassOpenSslRoundTrip KICKASS_CRYPTO_CONFIG_PROBLEM_INVALID_OPENSSL_SECRET_CURR
  php fast.php KickassOpenSslRoundTrip KICKASS_CRYPTO_CONFIG_PROBLEM_INVALID_OPENSSL_SECRET_PREV

  php fast.php KickassOpenSslAtRest KICKASS_CRYPTO_CONFIG_PROBLEM_MISSING_OPENSSL_SECRET_LIST
  php fast.php KickassOpenSslAtRest KICKASS_CRYPTO_CONFIG_PROBLEM_INVALID_OPENSSL_SECRET_LIST

}

main "$@";
