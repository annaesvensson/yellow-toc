<?php
// Toc extension, https://github.com/annaesvensson/yellow-toc

class YellowToc {
    const VERSION = "0.8.7";
    public $yellow;         // access to API

    // Handle initialisation
    public function onLoad($yellow) {
        $this->yellow = $yellow;
        $this->yellow->system->setDefault("tocLevel", "6"); // shows headings up to nth level
        $this->yellow->system->setDefault("tocNumbering", "1"); // if 1, ToC is a numbered list
    }

    // Handle page content in HTML format
    public function onParseContentHtml($page, $text) {
        $callback = function ($matches) use ($page) {
            $location = $page->getPage("main")->getLocation(true);
            $rawData = $page->getPage("main")->parserData;
            preg_match_all("/<h(\d) id=\"([^\"]+)\">(.*?)<\/h\d>/i", $rawData, $matches, PREG_SET_ORDER);
            if ( $this->yellow->system->get("tocNumbering")) {
                $listType = "ol";
            } else {
                $listType = "ul";
            }
            $output = "<$listType class=\"toc\">";
            $prevLevel = 0;
            $nestedList = 0;
            foreach ($matches as $match) {
                if ($match[1] < $prevLevel) {
                    $nestedList = 0;
                    $output .= "</$listType>";
                } elseif ($prevLevel != 0 && $match[1] > $prevLevel) {
                    ++$nestedList;
                    $output .= "<$listType>";
                }
                $output .= "<li><a href=\"$location#$match[2]\">$match[3]</a></li>\n";
                $prevLevel = $match[1];
            }
            for ($i = 0; $i < $nestedList; $i++) {
                $output .= "</$listType>\n";
            }
            $output .= "</$listType>\n";
            return $output;
        };
        return preg_replace_callback("/<p>\[toc\]<\/p>\n/i", $callback, $text);
    }

    // Handle page extra data
    public function onParsePageExtra($page, $name) {
        $output = null;
        if ($name=="header") {
            $extensionLocation = $this->yellow->system->get("coreServerBase").$this->yellow->system->get("coreExtensionLocation");
            $output = "<link rel=\"stylesheet\" type=\"text/css\" media=\"all\" href=\"{$extensionLocation}toc.css\" />\n";
        }
        return $output;
        return $name=="toc" ? $this->onParseContentHtml($page, "<p>[toc]</p>\n") : null;
    }
}
