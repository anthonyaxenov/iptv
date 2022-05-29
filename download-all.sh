#!/bin/bash
mkdir files
cd files
grep -P "pls='(.*)'" ../playlists.ini | sed "s/^pls=//g" | sed "s/'//g" | tr -d '\r' | xargs wget
