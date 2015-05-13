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
$temppath = 'pdftooutput'; //all files will be delete!!!

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
	$scrapeinfo = str_replace(array('.zip','.pdf','.png','.jpg'), '.scrapeinfo', $document);
	if( file_exists( $scrapeinfo ) ) 
	{
		//// edit the metadata into the scrapeinfo file
		$scrapeinfo_arr = parse_ini_file( $scrapeinfo );
		foreach($htmlinfo AS $key => $info)
		{
			$scrapeinfo_arr[$key] = $info;
		}
		file_put_contents( $scrapeinfo, $lineTitel, FILE_APPEND);
	}
	else echo "ScrapeInfoFile not found: $scrapeinfo";
}

////////////////////////////////////////////
function get_metadata($htmlfile)
{
	if( file_exists($htmlfile) )
	{
		///// get the title from html file
		$handle = @fopen($htmlfile, "r");
		$buffer = fread($handle, 4096);
		// get title
		$title = get_html_tag('title', $buffer);
		if(strlen($title)>0) $infos['title'] = $title;
	}
	return $infos;
}

////////////////////////////////////////////
function OLD_____put_metadata_to_scrapeinfo($htmlfile, $document)
{
	if( file_exists($htmlfile) )
	{
		///// get the title from html file
		$handle = @fopen($htmlfile, "r");
		$buffer = fread($handle, 4096);
		$title = get_html_tag('title', $buffer);
		
		///// look for .scrapeinfo file to update
		$scrapeinfo = str_replace(array('.zip','.pdf','.png','.jpg'), '.scrapeinfo', $document);
		if( file_exists( $scrapeinfo ) ) 
		{
			//// edit the metadata into the scrapeinfo file
			$f = file_get_contents( $scrapeinfo );
			if( substr_count($f, 'meta_title = ') == 0 && strlen($title) > 0 ) 
			{
				$lineTitel = "\nmeta_title = $title";
				file_put_contents( $scrapeinfo, $lineTitel, FILE_APPEND);
			}
		}
		else echo "ScrapeInfoFile not found: $scrapeinfo";
	}
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
