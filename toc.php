<?php
// Toc extension, https://github.com/annaesvensson/yellow-toc

class YellowToc {
    const VERSION = "0.8.7";
    public $yellow;         // access to API
    
    // Handle initialisation
    public function onLoad($yellow) {
        $this->yellow = $yellow;
    }
    
    // Handle page content in HTML format
    public function onParseContentHtml($page, $text) {
        $callback = function ($matches) use ($page) {
            $output = "<ul class=\"toc\">\n";
            $major = $minor = 0;
            $location = $page->getPage("main")->getLocation(true);
            $rawData = $page->getPage("main")->parserData;
            preg_match_all("/<h(\d) id=\"([^\"]+)\">(.*?)<\/h\d>/i", $rawData, $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {
                switch ($match[1]) {
                    case 2: ++$major; $minor = 0;
                            $output .= "<li><a href=\"$location#$match[2]\">$major. $match[3]</a></li>\n";
                            break;
                    case 3: ++$minor;
                            $output .= "<li><a href=\"$location#$match[2]\">$major.$minor. $match[3]</a></li>\n";
                            break;
                }
            }
            $output .= "</ul>\n";
            return $output;
        };
        return preg_replace_callback("/<p>\[toc\]<\/p>\n/i", $callback, $text);
    }
    
    // Handle page extra data
    public function onParsePageExtra($page, $name) {
        return $name=="toc" ? $this->onParseContentHtml($page, "<p>[toc]</p>\n") : null;
    }
}
