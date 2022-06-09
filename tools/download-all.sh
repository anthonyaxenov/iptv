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

rm -rf ./downloaded
mkdir -p ./downloaded && \
    cd ./downloaded && \
    grep -P "pls='(.*)'" ../playlists.ini | sed "s/^pls=//g" | sed "s/'//g" | tr -d '\r' | xargs wget
