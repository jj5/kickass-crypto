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
# 2023-04-05 jj5 - this script run phpDocumentor.phar to generate our documentation.
#
##################################################################################################

main() {

  set -euo pipefail;

  pushd "$( dirname "$0" )/../../" >/dev/null;
  pushd "bin/" >/dev/null;
  source inc/env.sh;
  popd >/dev/null;

  rm -rf doc/phpdoc;
  rm -rf log/phpdoc;

  mkdir -p doc/phpdoc;
  mkdir -p log/phpdoc;

  lib/phpdoc/phpDocumentor.phar run --force -d src/code -t doc/phpdoc --template doc/phpdoc-template/default/

  pushd doc/phpdoc >/dev/null;

  mkdir images;

  pushd images >/dev/null;

  wget https://www.progclub.net/favicon.ico 2>/dev/null;

}

main "$@";
