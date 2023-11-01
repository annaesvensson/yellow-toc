<?php
// Toc extension, https://github.com/annaesvensson/yellow-toc

class YellowToc {
    const VERSION = "0.8.11";
    public $yellow;         // access to API
    
    // Handle initialisation
    public function onLoad($yellow) {
        $this->yellow = $yellow;
        $this->yellow->system->setDefault("tocHeadingNumber", "1");
        $this->yellow->system->setDefault("tocHeadingLevels", "5");
    }
    
    // Handle page content in HTML format
    public function onParseContentHtml($page, $text) {
        $callback = function ($matches) use ($page) {
            $output = "<ul class=\"toc\">\n";
            $level1 = $level2 = $level3 = $level4 = $level5 = 0;
            $headingNumber = $this->yellow->system->get("tocHeadingNumber");
            $location = $page->getPage("main")->getLocation(true);
            $rawData = $page->getPage("main")->parserData;
            preg_match_all("/<h(\d) id=\"([^\"]+)\">(.*?)<\/h\d>/i", $rawData, $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {
                if ($match[1]>$this->yellow->system->get("tocHeadingLevels")+1) continue;
                $match[3] = strip_tags($match[3]);
                switch ($match[1]) {
                    case 2: ++$level1; $level2 = $level3 = $level4 = $level5 = 0;
                            $prefix = $headingNumber ? "$level1. " : "";
                            $output .= "<li class=\"toc1\"><a href=\"$location#$match[2]\">$prefix$match[3]</a></li>\n";
                            break;
                    case 3: ++$level2; $level3 = $level4 = $level5 = 0;
                            $prefix = $headingNumber ? "$level1.$level2. " : "";
                            $output .= "<li class=\"toc2\"><a href=\"$location#$match[2]\">$prefix$match[3]</a></li>\n";
                            break;
                    case 4: ++$level3; $level4 = $level5 = 0;
                            $prefix = $headingNumber ? "$level1.$level2.$level3. " : "";
                            $output .= "<li class=\"toc3\"><a href=\"$location#$match[2]\">$prefix$match[3]</a></li>\n";
                            break;
                    case 5: ++$level4; $level5 = 0;
                            $prefix = $headingNumber ? "$level1.$level2.$level3.$level4. " : "";
                            $output .= "<li class=\"toc4\"><a href=\"$location#$match[2]\">$prefix$match[3]</a></li>\n";
                            break;
                    case 6: ++$level5;
                            $prefix = $headingNumber ? "$level1.$level2.$level3.$level4.$level5. " : "";
                            $output .= "<li class=\"toc5\"><a href=\"$location#$match[2]\">$prefix$match[3]</a></li>\n";
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
