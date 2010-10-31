<?php
class RestResponseInstrumenter {
    
    private $insturmentations = array();
    
    public function __construct($baseUrl) {        
        $restBasePattern = '/services/data/v\d+\.\d';

        // add link url to any rest url
        $this->insturmentations[] = new Insturmentation("\"(" . $restBasePattern . ".*)\"",
                                                 		"\"<a class=\'RestLinkable\' href=" . $baseUrl . "?url=$1&autoExec=0>$1</a>\"");

        // add autoExec to everything but query and search
        $this->insturmentations[] = new Insturmentation('(url=' . $restBasePattern . '(?!/query|/search|.*/.*\{ID\}).*&)autoExec=0',
                                                         '$1autoExec=1');

        // query url                                                         
        $this->insturmentations[] = new Insturmentation('(' . $restBasePattern . '/query)<',
                                                         '$1</a>&nbsp;<a class=\'miniLink RestLinkable\' href=' . $baseUrl . '?url=$1?q=SELECT%2Bid,name,profile.name%2BFROM%2Buser%2BWHERE%2Busername=\'' . $_SESSION['getUserInfo']->userName . '\'&autoExec=1>SAMPLE<');  

        // search url                                                         
        $this->insturmentations[] = new Insturmentation('(' . $restBasePattern . '/search)<',
                                                         '$1</a>&nbsp;<a class=\'miniLink RestLinkable\' href=' . $baseUrl . '?url=$1?q=FIND%2B%7B' . $_SESSION['getUserInfo']->userName . '%7D%2BIN%2BALL%2BFIELDS&autoExec=1>SAMPLE<');  

    }

    public function instrument($s) {
        foreach ($this->insturmentations as $i) {
            $s = $i->instrument($s);
        }
        return $s;
    }
    
}

class Insturmentation {
    public $pattern;
    public $replacement; 
    
    function __construct($pattern, $replacement) {
        $this->pattern = $pattern;
        $this->replacement = $replacement;
    }
    
    function instrument($s) {
        return preg_replace('@' . $this->pattern . '@', $this->replacement, $s);      
    }
}
?>