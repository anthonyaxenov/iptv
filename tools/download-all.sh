#!/bin/bash

#################################################
#
# IPTV Playlist download tool
#
# Usage:
#     ./download-all.sh
#
# All playlists from playlists.ini will be
# downloaded in ./downloaded directory
#
# Anthony Axenov (c) 2022
# The MIT License:
# https://github.com/anthonyaxenov/iptv/blob/master/LICENSE
#
#################################################

TOOLS_DIR="$( cd -- "$( dirname -- "${BASH_SOURCE[0]:-$0}"; )" &> /dev/null && pwd 2> /dev/null; )";
DL_DIR="$TOOLS_DIR/downloaded"
INI_FILE="$(dirname "$TOOLS_DIR")/playlists.ini"

rm -rf "$DL_DIR" && \
    mkdir -p "$DL_DIR" && \
    cd "$DL_DIR" && \
    cat "$INI_FILE" \
        | grep -P "pls\s*=\s*'(.*)'" \
        | sed "s#^pls\s*=\s*##g" \
        | sed "s#'##g" \
        | tr -d '\r' \
        | xargs wget
