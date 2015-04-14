<?php

$risdl = new RIS_Downloader;

#if ( $risdl->run_tests() == true ) 
{
   $risdl->find_new_files( 203000 );
}


// file end
// avoid ugly console output (|%|)
print "\n";






/*
 * Scraper Class
 *
 */
class RIS_Downloader
{
    private $downloadDir = 'downloads/';
    private $infoFileSuffix = 'scrapeinfo';
    private $newFilesCount = 0;
    
    //////////////////////
    function __construct()
    {
    }


    /////////////////////////////
    // search new files by a given count  
    public function find_new_files( $next )
    {
        $lastId = $this->discover( 'highestId' );
        echo "\nFind $lastId as higest id";
        for( $id=$lastId+1; $id <= $lastId+$next; $id++ )
        {
            $this->download( $id, $this->downloadDir );
        }

        if( $this->newFilesCount > 0 ) $this->add_log('Search for $next new files. Find and download '.$this->newFilesCount.' new files.', true );
        else $this->add_log( 'Search for '.$next.' new files. Could not find new files.', true );
    }

    
    /////////////////////////////
    // returns the higest last id found. If no previous, return zero for starting new 
    private function discover( $flag )
    {
        $higestId = 0;
        $dh = opendir( $this->downloadDir );
        readdir($dh); readdir($dh); //jump over '.' and '..'
        while( false !== ($entry = readdir($dh)))
        {
            $nameparts = preg_split("/[_.]/", $entry); //explodes string at underline and point 
            $id = (int) $nameparts[1];
            if($id > $higestId) $higestId = $id;
        }
        if( $higestId > 0) return $higestId;
        else return 0;
    }


    /////////////////////////////
    // download(id [,head(bool)] )
    // try to download given file
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
        $dl = stream_get_contents( $h );
        //über fread() werden immer nur die ersten bytes gehlt, dann abgebrochen, das kann zur analyse genutzt werden

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
                'name: '.$OrgNname .PHP_EOL.
                'size: '.$size .PHP_EOL.
                'download date:'.date('d-m-Y-H:i:s',$now)." ($now)";
            file_put_contents($destination.$filename.'.'.$this->infoFileSuffix, $infofile);
            $this->newFilesCount++;
         }

        if( $head === true ) //just download head
        {
            //print_r( $http_response_header );
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
?>