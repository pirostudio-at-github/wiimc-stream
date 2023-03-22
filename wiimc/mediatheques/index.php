<?php
require_once('../../creds.php');
require_once('../../parsers.php');
use Curl\Curl;

$cinema = "https://paysvoironnais.mediatheques.fr/cinema";

$curl = new Curl();
$parser = new parsers();

$qr = "&sort=class_nbavis";
if(isset($_GET['q']))
{
    //Search
    $qr = "&sort=pert&qr=".urlencode($_GET['q']);
}

$cinema_page = "https://paysvoironnais.mediatheques.fr/cinema?sm=catalogue&nb=cinema".$qr."&pag=";
$free = "https://paysvoironnais.mediatheques.fr/cinema?sm=avolonte".$qr."&pag=";

function parseSectionPages($conn, $parser, $section, $limit = 20) {
    $page = 1;
    do {
        $data = $conn->get($section.$page)->getResponse();
        //$data = $conn->get($section.$page);
        if ($data) {
            $parser->parseCatalogue($data); 
            $page += 1;  
        } else {
            break;
        }
        ob_flush();
        if ( $page > $limit ) {
            break;
        }
    } while($data);
}


if ( Creds::$creds == "" ) {
    header("Location: /config.php", TRUE, 302);
    die(0);
}

header("Content-Type: text/plain;charset=UTF-8;");
print("[Playlist]\n");
parseSectionPages($curl, $parser, $cinema_page);
parseSectionPages($curl, $parser, $free);
