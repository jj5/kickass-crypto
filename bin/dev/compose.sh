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
# 2023-04-03 jj5 - this script will run the library's unit tests. By default only the fast tests
# are run. Pass --slow to also run the slow tests, and --silly to run the silly tests. But don't
# run the silly tests... they're silly.
#
# 2023-04-03 jj5 - NOTE: if a test has a shell script, that is run. Otherwise the PHP script is
# run. Each test in a src/test/test-XXX directory can have fast, slow, and silly components,
# and they will be run in that order, if they are available.
#
##################################################################################################

main() {

  set -euo pipefail;

  pushd "$( dirname "$0" )/../../" >/dev/null;
  pushd "bin/" >/dev/null;
  source inc/env.sh;
  popd >/dev/null;

  composer require symfony/flex

}

main "$@";
