<?php

$risdl = new RIS_Downloader;
$risdl->download_new_files();


/*
 * Scraper Class
 *
 */
class RIS_Downloader
{

    //////////////////////
    function __construct()
    {
    }
    

    /////////////////////////////
    function download_new_files()
    {
        //$dl = file("http://ratsinfo.dresden.de/getfile.php?id=196947&type=do");
        //file_put_contents("dl.pdf", $dl);

        for($id=0; $id<250000; $id++)
        {
            $dl = file('http://ratsinfo.dresden.de/getfile.php?id='.$id.'&type=do');
            if( $dl === false ) $this->add_log( "id $id not found" );
            else file_put_contents("downloads/fileid_$id.pdf", $dl);
        }


    ////////////////////////////////
    private function add_log( $msg )
    {
        $msg = date("Y:m:d h:i:s") . ':' . $msg;
        file_put_contents('scrape.log', $msg, FILE_APPEND);
    }
}
?>