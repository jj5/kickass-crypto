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
# 2023-04-03 jj5 - this script counts how many times "kickass" appears in the code and updates
# the documentation.
#
##################################################################################################

main() {

  set -euo pipefail;

  pushd "$( dirname "$0" )/../../" >/dev/null;

  # 2023-04-03 jj5 - we count the "kickass" in the ASCII banners!
  #
  local banner=$( grep -Ri 'By jj5' bin src 2>/dev/null | wc -l );

  local kickass_1=$( egrep -Ri '.*kickass' bin src 2>/dev/null | wc -l );
  local kickass_2=$( egrep -Ri '.*kickass.*kickass' bin src 2>/dev/null | wc -l );
  local kickass_3=$( egrep -Ri '.*kickass.*kickass.*kickass' bin src 2>/dev/null | wc -l );
  local kickass_4=$( egrep -Ri '.*kickass.*kickass.*kickass.*kickass' bin src 2>/dev/null | wc -l );

  # 2023-04-03 jj5 - the +1 is for the last line above (it has five kickasses in it, I'm gonna
  # assume there are no other instances of five kickasses on a single line).

  local count=$( printf "%'d" $(( banner + kickass_1 + kickass_2 + kickass_3 + kickass_4 + 1 )) );

  if false; then

    echo $banner;
    echo $kickass_1;
    echo $kickass_2;
    echo $kickass_3;
    echo $kickass_4;

  fi

  echo $count;

  sed -i "s/appears in the source code [^ ]* times/appears in the source code $count times/" \
    README.md

}

main "$@";
