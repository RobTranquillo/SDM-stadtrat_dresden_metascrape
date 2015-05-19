<?php
/*
 * pdf > pdftohtml and write the meta data in html
 * than look for html tags in there and write these
 * into the .scrapeinfo file
 *  
 */

include('htmlgrabber.php'); //supports some html grabbing functions

// basic example
$downloadpath = 'downloads';
$temppath = 'pdftooutput'; //Attention, all files in there will be delete!

$files = get_pdf_files($downloadpath);
$all = count($files);
$i=0;
foreach( $files AS $document )
{
	$i++;
	echo "\n ------------------------------------ 
		  \n processing $document ($i / $all) \n";
	$htmlfile = pdftohtml($document, $temppath);
	$infos = get_metadata($htmlfile, $document);
	put_metadata_to_scrapeinfo($infos, $document);
	
}



////////////////////////////////////////////
function put_metadata_to_scrapeinfo($htmlinfo, $document)
{		
	///// look for .scrapeinfo file to update
	$scrapeinfopath = str_replace(array('.zip','.pdf','.png','.jpg'), '.scrapeinfo', $document);
	if( file_exists( $scrapeinfopath ) ) 
	{
		//// edit the metadata into the scrapeinfo file
		$scrapeinfo = parse_ini_file( $scrapeinfopath, false, INI_SCANNER_RAW );
		$new_scrapeinfo = array_merge( $scrapeinfo, $htmlinfo);
		$newlines = '';
		foreach($new_scrapeinfo AS $key => $val) 
		{
			$newlines .= "$key = $val\n";
		}
		file_put_contents( $scrapeinfopath, $newlines);
	}
	else echo "ScrapeInfoFile not found: $scrapeinfo";
}

////////////////////////////////////////////
/*
 */
function get_metadata($htmlfile)
{
	if( file_exists($htmlfile) )
	{
		///// get the title from html file
		$handle = @fopen($htmlfile, "r");
		$buffer = fread($handle, 4096);
		// get title
		$title = get_html_tag('title', $buffer);
		if(strlen($title)>0) $infos['meta_title'] = $title;
	}
	return $infos;
}

////////////////////////////
// pdftohtml with option -i for no images
// because some pdfs produce 99k images and 
// rm cant del so much 
function pdftohtml($file, $temppath)
{
	$outfile = $temppath.'/new.html';
	shell_exec("rm $temppath/*.*");
	echo shell_exec("pdftohtml -i -nodrm $file $outfile");
	return $outfile;
}



////////////////////////////
function get_pdf_files($dir)
{
    $farr = array();
	if ($handle = opendir( $dir )) {
        while (false !== ($entry = readdir($handle))) {
			if(substr($entry,-4) == '.pdf')
				array_push( $farr, $dir.'/'.$entry );
        }
    }
    return $farr;
}

echo "\n";

?>
