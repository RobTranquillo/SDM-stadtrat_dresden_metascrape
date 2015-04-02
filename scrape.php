<?php

$risdl = new RIS_Downloader;
$risdl->download_new_files();


/*
 * Scraper Class
 *
 * uses black and white lists
 */
class RIS_Downloader
{

    //////////////////////
    // constructor gets the black and white list and save them in a member var
    function __construct()
    {
        $this->get_blacklist();
        $this->get_whitelist();
    }
    

    /////////////////////////////
    function download_new_files()
    {
        //$dl = file("http://ratsinfo.dresden.de/getfile.php?id=196947&type=do");
        //file_put_contents("dl.pdf", $dl);

        for($id=196940;$id<196950;$id++)
        {
            $dl = @ file('http://ratsinfo.dresden.de/getfile.php?id='.$id.'&type=do');
            if( $dl === false ) $this->add_to_blacklist( $id );
            else file_put_contents("downloads/fileid_$id.pdf", $dl);
        }


        $this->save_blacklist();
    }


    //////////////////////////
    private $blacklist;
    private $whitelist;
    private function get_blacklist() { return $this->get_list('blacklist'); }
    private function get_whitelist() { return $this->get_list('whitelist'); }
    private function get_list( $list )
    {
        $file = $list.".csv";        
        $lines = file( $file ); //read in the file
        $this->$list = $lines; //save as member var, with the name from the function argument
    }


    //////////////////////////
    private function add_to_blacklist( $id )
    {
        if( array_search( $id, $this->blacklist ) === false )
            array_push( $this->blacklist, $id );
    }


    //////////////////////////
    private function save_blacklist()
    {
        file_put_contents('blacklist.csv', implode(PHP_EOL, $this->blacklist) );
    }
}
?>