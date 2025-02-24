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

TOOLS_DIR="$( cd -- "$( dirname -- "${BASH_SOURCE[0]:-$0}"; )" &> /dev/null && pwd 2> /dev/null; )";
DL_DIR="$TOOLS_DIR/downloaded"
[ ! -d "$DL_DIR" ] && echo "Error: 'tools/downloaded' directory does not exist. Run tools/download-all.sh" && exit 1
[ ! "$(ls -A "$DL_DIR")" ] && echo "Error: 'tools/downloaded' directory is empty. Run tools/download-all.sh" && exit 2
for file in $TOOLS_DIR/downloaded/*; do
    $TOOLS_DIR/find-in-pls.sh "$1" "$file"
done
