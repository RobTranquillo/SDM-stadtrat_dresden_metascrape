<?php
/*
 * write all known data from all .scrapeinfo files in one csv file
 * 
 * if no output file name is argumented to the script a defaultname will be taken
 * and overwrite an even existing file 
 *  
 */

if(isset($argv[1])) $outputfile = $argv[1];
	else $outputfile = 'allfilesmeta.csv';
$downloadpath = 'downloads';
$csvhead = '"nr","fileid","orginal_filename","size","download date","meta_title","upload_date","subject"'; //must be the same sort order as they are in .scrapeinfo

// write all scrapeinfo in just one file and sort same tags in same colums
echo 'Start searching for .scrapeinfo files in '.$downloadpath;
$files = get_si_files( $downloadpath );
$n = count($files);
echo "\nfound $n .scrapeinfo files in $downloadpath";
if( $n > 0 ) {
    $outstr = '';
    $i = 0;
    foreach( $files AS $file ) 
    {
		$outstr .= $i++ . ','; //line number
		$outstr .= str_replace(array($downloadpath.'/fileid_','.scrapeinfo'), '', $file) . ','; //extract fileID from path
        $outstr .= get_file_contents_as_csv($file) . "\n";
    }
    
    // write to output
    $by = file_put_contents( $outputfile, $csvhead ."\n". $outstr );
    if( $by != false ) echo "\n".round($by/1024)." kbytes written to $outputfile";
}





////////////////////////////
// collects all .scrapeinfo files 
function get_file_contents_as_csv($f)
{
	$lines = file($f);
	$i=0;
	$out=array();
	foreach( $lines AS $line ) 
	{
		$field = trim(substr( $line, strpos($line,' = ')+3));
		$field = str_replace('"',"'", $field);
		$out[] = '"'.$field.'"';
	}
	return implode(',', $out);
}

////////////////////////////
function get_si_files($dir)
{
    $farr = array();
	if ($handle = opendir( $dir )) {
        while (false !== ($entry = readdir($handle))) {
			if(substr_count($entry,'.scrapeinfo')>0)
				array_push( $farr, $dir.'/'.$entry );
        }
    }
    return $farr;
}
    
    
echo "\n";
?>
