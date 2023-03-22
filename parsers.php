<?php
require_once 'vendor/autoload.php';
use Masterminds\HTML5;

require_once('creds.php');

class parsers
{
    private $index;
    private static $video_url="/video/mediatheques/";

    public function __construct() {
        $this->index = 0;
    }
    public function parseCatalogue($data)
    {
        $retVal = [];
        $data = strstr($data, "<div");

        $logInHTML = new HTML5();
        if ($logInDom = $logInHTML->loadHtml($data)) {
            $divs = $logInDom->getElementsByTagName("div");
            foreach ($divs as $div) {
                $docid = $div->getAttribute("data-docid");
                if ($docid) {
                    $author = "";
                    $title = $div->getAttribute("data-title");
                    $stitle = $div->getAttribute("data-stitle");
                    if( $stitle != "" )
                        $title .= " - ".$stitle;
                    $subdivs = $div->getElementsByTagName("div");
                    $duration = 60;
                    foreach ($subdivs as $subdiv) {
                        $tarif = $subdiv->getAttribute("data-tarif");
                        if ($tarif) {
                            $subp = $subdiv->getElementsByTagName("p");
                            if($subp->count() > 1) {
                                $iauthor = $subp->item(0);
                                if($iauthor->hasChildNodes()) {
                                    $author = $iauthor->textContent;
                                }
                                $idur = $subp->item(1);
                                if($idur->hasChildNodes()) {
                                    $sduration = $idur->textContent;
                                    $aduration = [];
                                    preg_match("/([0-9]+)min([0-9]+)/", $sduration, $aduration );
                                    $duration = intval($aduration[1])*60 + intval($aduration[2]);        
                                }
                            }
                        }
                    }

                    $host = "http://".$_SERVER['SERVER_NAME'];
                    print("File".$this->index."=".$host.Creds::$creds_uri.parsers::$video_url."?q=".$docid."\n");
                    print("Title".$this->index."=".$title."\n");
                    if($author != "") {
                        print("Author".$this->index."=".$author."\n");
                    }
                    print("Length".$this->index."=".$duration."\n");
                    $this->index += 1;
                }
            }
        }
        return $retVal;
    }
}
