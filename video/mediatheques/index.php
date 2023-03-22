<?php
require_once '../../vendor/autoload.php';
use Curl\Curl;
require_once('../../creds.php');
require_once('../../connection.php');
require_once('../../mpeg_dash.php');

$cinema = "https://paysvoironnais.mediatheques.fr/cinema";
$page = 1;
$cinema_page = "https://paysvoironnais.mediatheques.fr/cinema?sm=catalogue&nb=cinema&sort=class_nbavis&pag=".$page;

if( !isset($_GET['q']) ) {
    header('HTTP/1.1 404 Not Found');
    die(0);
}
if(isset($_GET['info'])){
	phpinfo(INFO_VARIABLES);
	die(0);
}

$docid = $_GET['q'];
Creds::$creds;
$user = Creds::getMediaUser();
$password = Creds::getMediaPassword();

///////////////////////////////////////////////////////
//header("Content-Type: text/plain;charset=UTF-8;");

$conn = new Connection();
$saveHeaders = $conn->headers;
$conn->headers = $conn->defaultHeader;
$conn->clearCookies();

$login_url = 'https://paysvoironnais.mediatheques.fr';
$login_params = '/album/'.$docid.'?from=cinema&sm=catalogue&nb=cinema&sort=title&pag=1';
$redirect = '{"ln2": "/album/'.$docid.'?from=cinema&sm=catalogue&nb=cinema&sort=title&pag=1"}';

$conn->get($login_url.$login_params);
//print("Jar\n");
//var_dump($conn->jar);
//ob_flush();

$login_data = [
	"xhr"=> 1,
	"ln"=> "connexion",
	"f"=> 1,
	"fp"=> "",
	"user"=> $user,
	"pass"=> $password];

$conn->headers = $saveHeaders;
$login_res = $conn->post($login_url, $login_data);

//print("login_res Jar\n");
//var_dump($conn->jar);
//ob_flush();

$video_url = "https://paysvoironnais.mediatheques.fr/";
$video_data = [
	"ln"=> "wstoken",
	"docid"=> $docid,
	"upload"=> 0,
	"streaming"=> 0,
	"finish"=> 0,
	"confirmed"=> 0,
	"confirmPlay"=> 0,
	"hideMessageForfait"=> 0,
	"xhr"=> 1];

//$video_resp = $curl->post($video_url, $video_data)->getResponse();
$video_resp = $conn->post($video_url, $video_data);
$video_resp_data = json_decode($video_resp);

if ( !isset($video_resp_data->success) ||  $video_resp_data->success == 0)
{
    die(0);
}

$lic_headers = [
"Host" => "analytics-ingress-global.bitmovin.com",
"User-Agent" => "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/111.0",
"Accept" => "*/*",
"Accept-Language" => "fr,fr-FR;q=0.8,en-US;q=0.5,en;q=0.3",
"Accept-Encoding" => "gzip, deflate, br",
"Content-Type" => "text/plain",
"Origin" => "https://paysvoironnais.mediatheques.fr",
"Connection" => "keep-alive",
"Referer: https://paysvoironnais.mediatheques.fr/",
"Sec-Fetch-Dest" => "empty",
"Sec-Fetch-Mode" => "cors",
"Sec-Fetch-Site" => "cross-site",
"Pragma" => "no-cache",
"Cache-Control" => "no-cache",
];

$dash_headers = [
"Host" => "cdn-downloads.ig1-cdn.com",
"User-Agent" => "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/111.0",
"Accept" => "*/*",
"Accept-Language" => "fr,fr-FR;q=0.8,en-US;q=0.5,en;q=0.3",
"Accept-Encoding" => "gzip, deflate, br",
"Origin" => "https://paysvoironnais.mediatheques.fr",
"Connection" => "keep-alive",
"Referer" => "https://paysvoironnais.mediatheques.fr/",
"Sec-Fetch-Dest" => "empty",
"Sec-Fetch-Mode" => "cors",
"Sec-Fetch-Site" => "cross-site",
"Pragma" => "no-cache",
"Cache-Control" => "no-cache",
];

file_put_contents("video_log.json",$video_resp);

$licensing_key = $video_resp_data->bmvsetup->key;
$licensing_analytic_key = $video_resp_data->bmvsetup->analytics->key;
$dash = $video_resp_data->bmvsources->dash;
$hls = $video_resp_data->bmvsources->hls;

$licensing_analyt = "https://analytics-ingress-global.bitmovin.com/licensing";
$licensing_analyt_data = ["analyticsVersion"=>"v2.29.1","domain"=>"paysvoironnais.mediatheques.fr","key"=>$licensing_analytic_key];

$licensing = "https://licensing.bitmovin.com/licensing";
$licens_data = ["domain"=>"paysvoironnais.mediatheques.fr","key"=>$licensing_key,"version"=>"8.109.0"];

//var_dump($licensing_analyt_data);
//var_dump($licens_data);
//var_dump($dash);

$bitmovin = new Connection();
$bitmovin->headers = $lic_headers;
try {
$lica_resp = $bitmovin->post($licensing_analyt,$licensing_analyt_data);
//var_dump($lica_resp);
//ob_flush();
} catch (Exception $error) {

}

try {
$lic_resp = $bitmovin->post($licensing,$licens_data);
//var_dump($lic_resp);
//ob_flush();
} catch (Exception $error) {

}
$bitmovin->headers = $dash_headers;
if ( $_GET['t'] == 'mpd') {
	$dash_resp = $bitmovin->get($dash);
}
if ( $_GET['t'] == 'm3u') {
	$dash_resp = $bitmovin->get($hls);
}
		//var_dump($dash_resp);

header("Content-Type: application/dash+xml;charset=UTF-8;");
// Il sera nommé downloaded.pdf
header('Content-Disposition: attachment; filename="stream-'.$docid.'.'.$_GET['t']);

print($dash_resp);

/*header("Content-Type: text/plain;charset=UTF-8;");
$mpd_parser = new MpegDash($dash_resp);
$dash_files = $mpd_parser->getFileUrls();
$dash_url_array = explode("/",$dash);
array_pop($dash_url_array);
$dash_base_url = join("/",$dash_url_array);

foreach($dash_files as $dashfile)
{
    $dashfile_resp = $bitmovin->get($dash_base_url.'/'.$dashfile);
    file_put_contents($dashfile,$dashfile_resp);
}*/
