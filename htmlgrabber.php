<?php
/*
html_grabber by tranquillo

robtranquillo@gmx.de
twitter.com/robtranquillo
github.com/robtranquillo

last update: 7 May 2015
*/


///////////////////////////////
// returns the needed tag from a string
// on false returns false
// [opt] limit = reand only to this length 
function get_html_tag($tag, $b, $limit = -1)
{
	$tag_open = '<'.$tag.'>';
	$tag_close = '</'.$tag.'>';
	$tagstart = strpos( $b, $tag_open ) + strlen($tag_open);
	$tagend = strpos( $b, $tag_close, $tagstart );
	$taglen = $tagend - $tagstart; 
	
	if($limit > 0 && $tagstart > $limit) return; //break if limit is reached
	
	$tag = substr( $b, $tagstart, $taglen );
	if( $tagstart > 0 && $tagend > $tagstart ) return $tag; //return tag, even if tag is empty
	else return false;
}

?>
