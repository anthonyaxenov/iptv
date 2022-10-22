#!/bin/bash

#################################################
#
# IPTV channel finder (one playlist)
#
# Usage:
#     ./find-in-pls.sh "disney" local/pls.m3u
#     ./find-in-pls.sh "disney" https://example.com/pls.m3u
#
# 1st argument is channel name pattern.
#
# 2nd argument is playlist file name or URL.
# If it is an URL it will be saved in /tmp and
# checked as local file.
#
# Both *.m3u and *.m3u8 are supported.
#
# Anthony Axenov (c) 2022
# The MIT License:
# https://github.com/anthonyaxenov/iptv/blob/master/LICENSE
#
#################################################

channel="$1"
playlist="$2"
playlist_url="$playlist"
regex_ch="^#extinf:\s*-?[01]\s*.*,(.*${channel,,}.*)"
regex_url="^https?:\/\/.*$"

is_downloaded=0
download_dir="/tmp/$(date '+%s%N')"

found_count=0
found_last=0
line_count=1

if [[ "$playlist" =~ $regex_url ]]; then
    mkdir -p "$download_dir"
    cd "$download_dir"
    wget "$playlist" -q > /dev/null
    if [ $? -eq 0 ]; then
        is_downloaded=1
        playlist="$download_dir/$(ls -1 "$download_dir")"
        cd -
    else
        echo "ERROR: cannot download playlist: $playlist"
        exit 1
    fi
fi

echo "--------------------"
echo -e "\033[20m\033[97mChannel:\033[0m $channel"
echo -e "\033[20m\033[97mPlaylist:\033[0m $playlist_url"
echo -e "\033[20m\033[97mRegex:\033[0m $regex_ch"
echo "--------------------"

while read line; do
    if [[ "${line,,}" =~ $regex_ch ]]; then
        echo -e "\n\033[32m$line_count FOUND:\033[0m\t$line"
        ((found_count += 1))
        found_last=$found_count
    fi
    if [ $found_last -gt 0 ]; then
        if [[ "${line,,}" =~ $regex_url ]]; then
            echo -e "\t\t$line"
            found_last=0
        fi
    fi
    ((line_count += 1))
done < $playlist

if [ $found_count -eq 0 ]; then
    echo -e "\033[91mNothing found\033[0m"
else
    echo "--------------------"
    echo -e "\033[20m\033[97mPlaylist:\033[0m $playlist_url"
    echo -e "\033[20m\033[97mChannel:\033[0m $channel"
    echo -e "\033[20m\033[97mFound:\033[0m\033[32m $found_count\033[0m"
fi

if [ $is_downloaded -eq 1 ]; then
    rm -rf "$download_dir"
fi
