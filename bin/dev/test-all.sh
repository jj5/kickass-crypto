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
# 2023-03-30 jj5 - this script doesn't exactly do what it says on the tin, as it doesn't run the
# silly tests. But you don't need to run the silly tests. They take ages, and they're silly. This
# script just runs the standard (fast) tests and the longer (slow) tests. This is the script to
# run to validate the library before a release.
#
##################################################################################################

main() {

  set -euo pipefail;

  pushd "$( dirname "$0" )" >/dev/null;
  pushd "../" >/dev/null;
  source inc/env.sh;
  popd >/dev/null;

  bash test.sh --slow;

}

main "$@";
