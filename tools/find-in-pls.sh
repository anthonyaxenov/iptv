#!/bin/bash

#################################################
#
# IPTV channel finder (one playlist)
#
# Usage:
#     ./find-in-pls.sh disney local/pls.m3u
#     ./find-in-pls.sh disney https://example.com/pls.m3u
#
# 1st argument is channel name pattern.
#
# 2nd argument is playlist file name or URL.
# If it is an URL it will be saved in /tmp and
# checked as local file.
#
# Both *.m3u and *.m3u8 are supported.
#
#################################################

awk '
    BEGIN {
        IGNORECASE=1
        channel = ARGV[1]
        playlist = ARGV[2]
        found_count = 0
        found_last = 0
        regex_ch = tolower(sprintf("^#EXTINF:.+,\s*(.*%s.*)", channel))
        regex_url = "^https?:\/\/.*$"

        print "--------------------"
        print "\033[20m\033[97mPlaylist:\033[0m " playlist
        print "\033[20m\033[97mChannel to find:\033[0m " channel

        if (playlist ~ /^http(s)?:\/\/.*/) {
            parts_count = split(playlist, parts, "/")
            file_name = parts[parts_count]
            code = system("wget " playlist " -qO /tmp/" file_name " > /dev/null")
            if (code == 0) {
                print "Saved in /tmp/" file_name
            } else {
                print "ERROR: cannot download playlist: " playlist
                exit 1
            }
            playlist = "/tmp/" file_name
        }

        ARGV[1] = playlist
        delete ARGV[2]
        print "--------------------"
    }
    {
        sub("\r$", "", $0) # crlf -> lf
        if (tolower($0) ~ tolower(regex_ch)) {
            found_count++
            #print "\n\033[32m" FNR " FOUND:\033[0m\t" $0
            print "\n" $0
            found_last = FNR
        }
        if (found_last > 0) {
            if (tolower($0) ~ tolower(regex_url)) {
                #print "\t\t" $0
                print $0 "\n"
                found_last = 0
            }
        }
    }
    END {
        if (found_count == 0) {
            print "\033[91mNothing found\033[0m"
        } else {
            print "--------------------"
            print "\033[20m\033[97mPlaylist:\033[0m " playlist
            print "\033[20m\033[97mChannel found:\033[0m " channel
            print "\033[20m\033[97mFound:\033[0m\033[32m " found_count "\033[0m"
        }
    }
' $1 $2
