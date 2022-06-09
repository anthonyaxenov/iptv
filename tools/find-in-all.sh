#!/bin/bash

#################################################
#
# IPTV channel finder (all playlists)
#
# Usage:
#     ./download-all.sh
#     ./find-in-all.sh "(disney|atv)"
#
# 1st argument is channel name pattern.
#
# Anthony Axenov (c) 2022
# The MIT License:
# https://github.com/anthonyaxenov/iptv/blob/master/LICENSE
#
#################################################

SCRIPT_DIR="$( cd -- "$( dirname -- "${BASH_SOURCE[0]:-$0}"; )" &> /dev/null && pwd 2> /dev/null; )";
[ ! -d ./downloaded ] && echo "Error: ./downloaded directory does not exist. Run $SCRIPT_DIR/tools/download-all.sh" && exit 1
[ ! "$(ls -A ./downloaded)" ] && echo "Error: ./downloaded directory is empty. Run $SCRIPT_DIR/tools/download-all.sh" && exit 2
for file in ./downloaded/*; do
    "$SCRIPT_DIR"/find-in-pls.sh "$1" "$file"
done
