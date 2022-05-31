#!/bin/bash

#################################################
#
# IPTV Playlist check tool
#
# Usage:
#     ./check-pls.sh local/pls.m3u
#     ./check-pls.sh https://example.com/pls.m3u
#
# Both *.m3u and *.m3u8 are supported.
#
# If argument is link to playlist it will be
# saved in /tmp and then check as local file.
#
#################################################

awk '
    BEGIN {
        total_count=0
        success_count=0
        fail_count=0
        print "Playlist: " ARGV[1]
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
        print "Note 1: operation may take some time."
        print "Note 2: press CTRL+C to skip current channel or CTRL+Z to kill process."
        print "Note 3: results may be inaccurate, you should use proper IPTV software to re-check."
        print "Note 4: error codes listed here - https://everything.curl.dev/usingcurl/returns"
        print "--------------------"
    }
    {
        sub("\r$", "", $0) # crlf -> lf
        if ($0 ~ /^#EXTINF\:.+,/) {
            total_count++
            channel_name = substr($0, index($0, ",") + 1, length($0))
            print "[" total_count "] " channel_name "..."
        }
        if ($0 ~ /^http(s)?:\/\/.*/) {
            url = sprintf("%c%s%c", 34, $0, 34) # 34 == "
            code = system("curl -s --max-time 5 --max-filesize 5000 -o /dev/null " url)
            if (code == 0 || code == 63) {
                print "\t- OK: " url
                success_count++
            } else {
                print "\t- ERROR (" code "): " url
                fail_count++
            }
        }
    }
    END {
        print "--------------------"
        print "Check stats"
        print "- Success:\t" success_count "/" total_count
        print "- Failed: \t" fail_count "/" total_count
    }
' $1
