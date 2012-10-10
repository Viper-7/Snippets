<?php
    
    $content = <<<EOI
#Created by the Bamboo Continuous Integration Server
#Wed Nov 10 14:54:39 UTC 2010
build.number=149
build.timestamp="2010-11-10 14\:54\:39"
EOI;

    var_dump(parse_your_format_name_here($content));

    function parse_your_format_name_here($content) {
        $replace = array('\:' => ':');
        $content = str_replace(array_keys($replace), array_values($replace), $content);
        
        $data = @parse_ini_string($content);
        $out = array();
        
        if($data) {
            foreach($data as $key => $value) {
                $parts = explode('.', $key);
                $token =& $out;        
                foreach($parts as $part) {
                    $token =& $token[$part];
                }
                $token = $value;
                unset($token);
            }                
        }

        return $out;
    }
    

?>
