<?php
/*
html_grabber by tranquillo

robtranquillo@gmx.de
twitter.com/robtranquillo
github.com/robtranquillo

*/


/////////////////////////////////
// get content of a html meta tag: <meta name="date" content="2013-07-31T23:14:47+00:00"/>
//
// return: the content from the needed meta tag: "2013-07.."
// on false returns false
// [opt] limit = read only to this length 
function get_meta_tag_content($metatag_name, $str, $limit = -1)
{
	$tagstart = strpos($str, " name=\"$metatag_name\" ");
	if( $tagstart === false ) return false;

	$contentstart = strpos( $str, 'content="', $tagstart ) + 9;
	$contentend   = strpos( $str, '"', $contentstart );
	$contentlen   = $contentend - $contentstart; 
	
	$content = substr( $str, $contentstart, $contentlen );	
	if( $contentstart > 0 && $contentend > $contentstart ) return $content; //return content if not empty
	else return false;
}



///////////////////////////////
// get the inner content of a html tag like: <title>foobar</title> 
//
// return: the content tag: "foobar"
// on false returns false
// [opt] limit = read only to this length 
// I'm guessing you are looking at the beginning of a UTF-8 sequence, quite possibly U+00A0 whose UTF-8 encoding is 194 160 (0xC2 0xA0).
function get_html_tag($tag, $str, $limit = -1)
{
	$tag_open = '<'.$tag.'>';
	$tag_close = '</'.$tag.'>';
	$tagstart = strpos( $str, $tag_open ) + strlen($tag_open);
	$tagend = strpos( $str, $tag_close, $tagstart );
	$taglen = $tagend - $tagstart; 
	
	if($limit > 0 && $tagstart > $limit) return; //break if limit is reached
	
	$tag = substr( $str, $tagstart, $taglen );
	$tag = str_replace('&#160;', ' ', $tag);
	$tag = preg_replace('/[\s\t\n\r\s]+/', ' ', $tag);
	if( $tagstart > 0 && $tagend > $tagstart ) return $tag; //return tag if not empty
	else return false;
}

?>
