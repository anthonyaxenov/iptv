package main

import (
	"github.com/pawanpaudel93/go-m3u-parser/m3uparser"
)

func main() {
	userAgent := "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.198 Safari/537.36"
	timeout := 5
	parser := m3uparser.M3uParser{UserAgent: userAgent, Timeout: timeout}
	parser.ParseM3u("https://iptv.axenov.dev/d1", true, true)
	//parser.FilterBy("status", []string{"GOOD"}, true)
	parser.SortBy("title", true)

	parser.ToFile("rowdy.json")
	//fmt.Println(parser.GetStreamsJSON())
}
