#!/bin/bash

#################################################
#
# IPTV Playlist check tool
#
# Usage:
#     ./check-pls.sh local/pls.m3u
#     ./check-pls.sh https://example.com/pls.m3u
#
# 1st argument is playlist file name or URL.
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

awk '
    BEGIN {
        total_count=0
        success_count=0
        fail_count=0
        print "\033[20m\033[97mPlaylist:\033[0m " ARGV[1]
        if (ARGV[1] ~ /^http(s)?:\/\/.*/) {
            parts_count = split(ARGV[1], parts, "/")
            file_name = parts[parts_count]
            code = system("wget " ARGV[1] " -qO /tmp/" file_name " > /dev/null")
            if (code == 0) {
                print "Saved in /tmp/" file_name
            } else {
                print "ERROR: cannot download playlist: " ARGV[1]
                exit 1
            }
            ARGV[1] = "/tmp/" file_name
        }
        print ""
        print "\033[20m\033[97mNote 1:\033[0m operation may take some time, press CTRL+C to stop."
        print "\033[20m\033[97mNote 2:\033[0m results may be inaccurate, you should use proper IPTV software to re-check."
        print "\033[20m\033[97mNote 3:\033[0m error codes listed here - https://everything.curl.dev/usingcurl/returns"
        print "--------------------"
    }
    {
        sub("\r$", "", $0) # crlf -> lf
        if ($0 ~ /^#EXTINF:.+,/) {
            total_count++
            channel_name = substr($0, index($0, ",") + 1, length($0))
            print "[" total_count "] " channel_name "..."
        }
        if ($0 ~ /^http(s)?:\/\/.*/) {
            url = sprintf("%c%s%c", 34, $0, 34) # 34 is "
            cmd = "curl -fs --max-time 5 -w \"%{http_code}\" --max-filesize 5000 -o /dev/null " url
            cmd | getline http_code
            code = close(cmd)
            if (http_code == "000") {
                http_code = "-"
            }
            if (code == 0 || code == 63) {
                print "\t- \033[32mOK:\033[0m " url
                success_count++
            } else {
                print "\t- \033[91mERROR\033[0m " code " (" http_code "): " url
                fail_count++
            }
        }
    }
    END {
        print "--------------------"
        print "\033[20m\033[97mPlaylist:\033[0m " ARGV[1]
        print "- Success:\t\033[32m" success_count "\033[0m/" total_count
        print "- Failed: \t\033[91m" fail_count "\033[0m/" total_count
    }
' $1
