<?php
/*
 * 
 * Konvertiert PDF in html.
 * Bekannte html tags landen in der dazugehörigen .scrapeinfo Datei.
 * 
 *  
 */


include('htmlgrabber.php'); //supports some html grabbing functions
$downloadpath = 'downloads';
$temppath = 'pdftooutput'; //Attention, all files in there will be delete!


// handle script parameter
$param = getopt('', array('sincedate::'));
if($param['sincedate'] > 0 ) {
	echo 'Metadate since '.date('d.m.Y H:i:s',$param['sincedate']).' will be extracted.';
	$sincedate = $param['sincedate'];
}
else $sincedate = 0; //if no value for --sincedate is given, startdate 1.1.1970 will be set



$files = get_pdf_files($downloadpath);
if($sincedate > 0) $files = filter_files($files, $sincedate); //chop all old and just konverted files from array
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



/*
 * filters the array by files timestamp, to keep only new files
 *
 * function for use together with cron.service 
 * for permanent update of the data  
 * 
 */
function filter_files($arr, $threshold)
{
	$threshold = (int) $threshold;
	$pot = array();
	foreach( $arr AS $testee)
	{
		$f_ts = (int) substr($testee, strrpos($testee, '_')+1);
		if($f_ts >= $threshold) array_push($pot, $testee);
	}
	return $pot;
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
		// open file
		$handle = @fopen($htmlfile, "r");
		$buffer = fread($handle, 4096);

		///// get the title
		$title = get_html_tag('title', $buffer);
		if(strlen($title)>0 AND $title != 'pdftooutput/new') {
			$infos['meta_title'] = trim(html_entity_decode($title));
		}
		else $infos['meta_title'] = '<Titel nicht ermittelbar>';
		
		///// get the upload-time
		$datetime = get_meta_tag_content('date', $buffer); //<meta name="date" content="2013-07-31T23:14:47+00:00"/>
		if(strlen($datetime)>0) {
			$dd = explode("-", substr($datetime,0,10));  //-> date_date
			$dt = explode(":", substr($datetime,10,8));  //-> date-time
			$uxts = mktime((int)$dt[0], (int)$dt[1], (int)$dt[2], (int)$dd[1], (int)$dd[2], (int)$dd[0]);
			$infos['upload_date'] = $datetime." ($uxts)";
		}
		
		
		//get subject
		$sub = get_meta_tag_content('subject', $buffer);  //<meta name="subject" content="Dokument zu einer Sitzung aus dem Sitzungsdienst."/>
		if(strlen($sub)>0) {
			 $sub = html_entity_decode($sub);
			 $sub = str_replace(array("\r", "\n", "   "), " ", $sub); //sometimes, <br>'s are in there 
			 $infos['subject'] = trim($sub);
		 }
		
		
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
	shell_exec("pdftohtml -i -nodrm $file $outfile");
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
