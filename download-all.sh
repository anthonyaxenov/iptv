#!/bin/bash

#################################################
#
# IPTV Playlist download tool
#
# Usage:
#     ./download-all.sh
#
# All playlists from playlists.ini will be
# downloaded in ./files directory
#
#################################################

mkdir files && \
    cd files && \
    grep -P "pls='(.*)'" ../playlists.ini | sed "s/^pls=//g" | sed "s/'//g" | tr -d '\r' | xargs wget
