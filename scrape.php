<?php
/*
 * Downloader für das Ratsinfo System von Dresden (allris,sessionet)
 * 
 * $risdl->run_tests(); //start the build-in tests
 * 
 * Nutzbare Script-Parameter:
 * 
 * --trynext=2000  (optional)
 * 	 sucht in den nächsten 2000 ids nach neuen Dokumenten (2000 = Standardwert) und lädt sie herunter
 * 
 * --diff (optional)
 *   sucht nach Veränderungen auf dem Server und lädt nur die veränderte Dokumente herunter 
 *   vorhandene Dokumente werden versioniert
 * 
 */

$risdl = new RIS_Downloader;

// handle script parameters
$userparam = getopt('', array('trynext::', 'diff::'));
if( isset($userparam['diff']) )	{
		$risdl->diff_all( $userparam['diff'] );
	}
else
	{
		if( isset($userparam['trynext']) ) {
			echo 'The next '.$userparam['trynext'].' IDs will be scaned.';
			$trynext = $userparam['trynext'];
		}
		else 
		{
			$trynext = 2000; //if no value for --try is given, 2000 file will be tryed out
			echo 'No limit is set, the next '.$trynext.' IDs will be scaned.';
		}
	
	$risdl->search_new_files( $trynext );
	}


/*
 * Ratsinfomations downloader class
 *
 */
class RIS_Downloader
{
	
    private $downloadDir = 'downloads/'; //primary download folder
    private $versionsDir = 'versions/'; //contains older versions of a file
    private $infoFileSuffix = 'scrapeinfo'; //contains all data about a downloaded file
    private $newFilesCount = 0;
    private $unequal_files = array();

        
    
    /*
     * check differences between former downloaded files and 
     * the files are now online at the RIS
     * 
     * different files will be downloaded and former files 
     * will be versioned 
     * 
     */ 
    public function diff_all( $limit )
    {
		$this->add_log("**** start new diff process ****");
        $this->discover( 'allIds' );        			
		$this->find_unequal_files( $limit );
		$this->version_unequal_files();
		$this->download_unequal_files();
	}


	
    /*
	 *  
     */
    private function version_unequal_files()
    {
		if( count( $this->unequal_files) > 0 )
		{
			if( file_exists($this->versionsDir) === false ) mkdir( $this->versionsDir );
			foreach($this->unequal_files as $id => $name)
			{
				$name_scrapeinfo = str_replace('.pdf', '.scrapeinfo', $name);
				copy( $this->downloadDir.$name, $this->versionsDir.$name );
				copy( $this->downloadDir.$name_scrapeinfo, $this->versionsDir.$name_scrapeinfo );
				unlink( $this->downloadDir.$name);
				unlink( $this->downloadDir.$name_scrapeinfo);
			}
		}
	}
    


    /*
     * 
     */
    private function download_unequal_files()
    {
		$bulk = count($this->unequal_files);
		foreach($this->unequal_files as $id => $path)
		{
			$this->download( $id, $this->downloadDir );
		}
		echo "\nResults:";
        if( $this->newFilesCount > 0 ) $this->add_log('Searched for '.$bulk.' new files, and find and download '.$this->newFilesCount.' new files.', true );
        else $this->add_log( 'Searched for '.$bulk.' new files. Could not find new files.', true );		
	}        


	/*
	 *  compare former downloaded files with the files are now online 
	 */
	private function find_unequal_files($limit = 0)
	{
       $count = count($this->allKnownIds);
        if( $count > 0 )
			{
				$xi=0;
				$starttime = mktime();
				$unequal = array();
				ksort( $this->allKnownIds );				
				
				foreach($this->allKnownIds as $id => $PDFpath)
				{
					$xi++;
					
					if($limit > 0 AND $xi > $limit) break; // limitate the scan for test behaviour 
					
					echo "\ncheck id:$id ($xi/ $count, ". round($starttime / mktime())*($count-$xi) . ' seconds remaining)' ;
					$http_head = $this->download( $id, false, true ); //gets only the head of the file 
					$scrapeinfo = str_replace('.pdf', '.scrapeinfo', $PDFpath);
					$scrapeinfo = parse_ini_file( $this->downloadDir.$scrapeinfo, false, INI_SCANNER_RAW );
					
					if( isset($http_head['Content-Length']))
					{
						if( isset($scrapeinfo['size']) == false ) 
							$scrapeinfo['size'] = $http_head['Content-Length']; //fix empty values in scrapeinfo
						
						if($scrapeinfo['size'] != $http_head['Content-Length']) 
							$unequal[$id] = $PDFpath; //save unequal
					}
				}
				echo "\nfound ".count($unequal).' unequal files';
				$this->add_log("unequal files found: ".implode(',',$unequal), true);
				$this->unequal_files = $unequal;
			}
		else
			{
				echo $this->add_log('Exit, because no former files where found.', true );
			}
	
	}




    /*
     * identify the highes document-id are there and
     * search for new files by scaning for IDs above 
     * this highest known id  
     * 
     */ 
    public function search_new_files( $next )
    {
		if( ! $next > 0) return false;
        $startId = $this->discover( 'highestId' );
        echo "\nFind $startId as higest id";
        for( $id=$startId; $startId+$next > $id; $id++ )
        {
            $this->download( $id, $this->downloadDir );
        }
		echo "\nResults:";
        if( $this->newFilesCount > 0 ) $this->add_log('Searched for '.$next.' new files, and find and download '.$this->newFilesCount.' new files.', true );
        else $this->add_log( 'Searched for '.$next.' new files. Could not find new files.', true );
    }

    
    private function discover( $flag )
    /////////////////////////////
    // returns the higest last id found. If no previous, return zero for starting new 
    // $flag for different action later 
    {
        $higestId = 0;
        $this->allKnownIds = array();
        if( file_exists($this->downloadDir) === false) mkdir( $this->downloadDir );
        
        $dh = opendir( $this->downloadDir );
        while( false !== ($entry = readdir($dh)))
        {
			if( substr_count($entry, '.pdf') < 1 ) continue;
            
			$nameparts = preg_split("/[_.]/", $entry); //explodes string at underline and point 
			$id = (int) $nameparts[1];
			if($id > $higestId) $higestId = $id;

			if( $flag == 'allIds')
			{
				$this->allKnownIds[$id] = $entry;
			}
        }
        
        if( $higestId > 0) return $higestId;
        else return 0;
    }


    /////////////////////////////
    // download(id [,head(bool)] )
    // try to download given file or just the head
    // create file and *.info fileid or return false
    /* $http_response_header:
        // [0] => HTTP/1.1 200 OK
        // [1] => Date: Mon, 13 Apr 2015 17:22:52 GMT
        // [2] => Server: Apache/2.4.7 (Ubuntu)
        // [3] => Referer: http://ratsinfo.dresden.de
        // [4] => X-Powered-By: PHP/5.5.9-1ubuntu4.6
        // [5] => Expires: 0
        // [6] => Cache-Control: must-revalidate, post-check=0, pre-check=0
        // [7] => Pragma: public
        // [8] => Content-Length: 14450
        // [9] => Content-transfer-encoding: binary
        // [10] => Content-Disposition: attachment;filename="Einladung_-_OSR_SB.pdf"
        // [11] => X-Robots-Tag: noindex
        // [12] => Content-Type: application/pdf
        // [13] => Set-Cookie: PHPSESSID=l1hhnpp4tlm4k2qlr87ilqt453; path=/
        // [14] => Connection: close
     */     
    private function download( $id , $destination, $head = false)
    {
        $h = @fopen('http://ratsinfo.dresden.de/getfile.php?id='.$id.'&type=do','rb');
        if( $h === false) return false; //id not found, end of story
        
        if( $head === true ) //just download head
			{
				fclose($h);
				foreach($http_response_header as $str)
				{
					$field = substr($str, 0, strpos($str,':'));
					$value = substr($str, strpos($str,':')+1);
					$http_response_header_new[$field] = $value;
				}
				
				return $http_response_header_new;
				// über fread() werden immer nur die ersten bytes gehlt, dann abgebrochen, das kann zur analyse genutzt werden
			}
        else
			{
				$dl = stream_get_contents( $h );
				if( $dl === false ) $this->add_log( 'can not download id '.$id );
				else
				{
					echo "\n".'try to download id: '.$id;
					$size = trim( substr( $http_response_header[8], 15) );
					$OrgNname = trim( substr( $http_response_header[10], 42, -1) );
					$suffix = substr( $OrgNname, strrpos($OrgNname, '.')+1 );
					$now = mktime();
					$filename = 'fileid_'.$id.'_'.$now; //should: fileid_00001_1428947960.pdf [bez-id-timestamp.suffix]) 
					file_put_contents($destination.$filename.".$suffix", $dl);

					$infofile =
						'name = '.$OrgNname .PHP_EOL.
						'size = '.$size .PHP_EOL.
						'download_date = '.date('d-m-Y-H:i:s',$now)." ($now)";
					file_put_contents($destination.$filename.'.'.$this->infoFileSuffix, $infofile);
					$this->newFilesCount++;
				 }
			}
        fclose($h);
    }


    ////////////////////////////////
    private function add_log( $msg, $echo=false )
    {
        $msg = date("d.m.Y-h:i:s").': '.$msg.PHP_EOL;
        file_put_contents('scrape.log', $msg, FILE_APPEND);
        if($echo) echo "\n\r".$msg; //log also to console
    }


    ////////////////////////////////
    /// TESTIING SECTION        \\\\
    ////////////////////////////////

    ////////////////////////////////
    public function run_tests()
    {
        if(
            $this->unit_download()
            )
        return true;
        else return false;
    }
    
    ////////////////////////////////
    // downloads a known file an checks the download
    private function unit_download()
    {
        $dir = 'testing/';
        if(file_exists($dir) === false) mkdir($dir);
        else {
            array_map('unlink', glob( $dir.'*.*'));
        }

        $this->download(16490, $dir);
        $files = scandir($dir);
        if($files[0] && $files[1]) return true;
        else {
            echo "\n**** unit test fails, cannot download or write files ****";
            return false;
        }
    }
    
}

print "\n"; // new line and avoid ugly console output (|%|) after execution
?>
