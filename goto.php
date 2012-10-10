<?php
$xml = '<foo><bar>lol1</bar><bar><baz>lol2</baz></bar><bar><baz><bar>lol3</bar></baz></bar><bar><baz><bar>lol4</bar></baz></bar></foo>';

echo 'Code:<br/>';
highlight_file(__FILE__);

$i=0;
$intag = false;
$closetag = false;

$level = -1;
$curtag = 0;
$elems = array();
$current = array();
$data = array();
$nested = array();
$depths = array(0,0);

top:
$chr = $xml[$i];
if(!$intag) goto notendtag;
if($chr !== '>') goto moretag;
$intag = false;
if(!$closetag) goto notclosetag;
$level--;
$closetag = false;
$current[$level] = '';
goto aftertag;
notclosetag:
$elems[][$tag] = $level;
$level++;
$current[$level] = $tag;
if(!isset($data[$curtag]['nodeName'])) $data[$curtag]['nodeName'] = '';
$data[$curtag]['nodeName'] .= $tag;
$data[$curtag]['level'] = $level;
goto aftertag;
moretag:
$tag .= $chr;
aftertag:
goto end;
notendtag:
if($chr !== '<') goto notstarttag;
$intag = true;
$curtag++;
if($xml[$i+1] !== '/') goto afterclosing;
$closetag = true;
$i++;
afterclosing:
$tag = '';
goto end;
notstarttag:
if(!isset($data[$curtag]['nodeValue'])) $data[$curtag]['nodeValue'] = '';
$data[$curtag]['nodeValue'] .= $chr;
$data[$curtag]['level'] = $level;
end:
$i++;
if($i < strlen($xml)) goto top;

reset($data);
rendertop:
$key = key($data);
$arr = current($data);

if($arr['level'] != 0) goto notfirst;
$nested[$key] = $arr;
$depths[$arr['level'] + 1] = $key;
goto endrender;
notfirst:
$parent =& $nested;
$i=0;
recurse:
$i++;
$parent =& $parent[$depths[$i]];
if($i<$arr['level']) goto recurse;
$parent[$key] = $arr;
$depths[$arr['level'] + 1] = $key;
endrender:
if(next($data) !== FALSE) goto rendertop;

striplevel($nested);

function striplevel(&$arr) {
    reset($arr);
    nextelem:
    $key = key($arr);
    $elem =& $arr[$key];
    if(!is_array($elem)) goto notarray;
    if(array_key_exists('level', $elem)) unset($elem['level']);
    striplevel($elem);
    notarray:
    if(next($arr) !== FALSE) goto nextelem;
}

echo '<br/><br/>Input:<br/>' . htmlentities($xml) . '<br/><br/><br/>Output:';
echo '<pre>' . htmlentities(print_r($nested,true)) . '</pre>';
