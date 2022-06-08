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
#################################################

mkdir -p downloaded && \
    cd downloaded && \
    grep -P "pls='(.*)'" ../playlists.ini | sed "s/^pls=//g" | sed "s/'//g" | tr -d '\r' | xargs wget
