<?php
require_once 'vendor/autoload.php';
use \GuzzleHttp\Client;
use \GuzzleHttp\Cookie\CookieJar;
use \GuzzleHttp\Psr7\Request;

class Connection
{
    public $cookies = "";

    public $opt = array(
        CURLOPT_AUTOREFERER => 0,
        CURLOPT_FOLLOWLOCATION => 1,
        CURLOPT_HEADER => 1,
        CURLOPT_POST => 1,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_UNRESTRICTED_AUTH => 1,
        CURLOPT_ENCODING, "gzip, deflate, br",
        CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:105.0) Gecko/20100101 Firefox/105.0',
        CURLOPT_ACCEPT_ENCODING, 'gzip, deflate, br',
        CURLOPT_REFERER, 'www.biblio-paysvoironnais.fr',
    );

    public $client;

    public $defaultHeader = [
        "Connection" => "keep-alive",
        "User-Agent" => "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/111.0",
        "Accept" => "*/*",
        "Accept-Encoding" => "gzip, deflate, br",
    ];
    public $headers = [
        "Referer" => "https://paysvoironnais.mediatheques.fr/?ln=connexion&ln2=%2F",
        "Host" => "paysvoironnais.mediatheques.fr",
        "User-Agent" => "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/111.0",
        "Accept" => "*/*",
        "Accept-Language" => "fr,fr-FR;q=0.8,en-US;q=0.5,en;q=0.3",
        "Accept-Encoding" => "gzip, deflate, br",
        "Content-Type" => "application/x-www-form-urlencoded; charset=UTF-8",
        "X-Requested-With" => "XMLHttpRequest",
        "Origin" => "https://paysvoironnais.mediatheques.fr",
        "Connection" => "keep-alive",
        "Sec-Fetch-Dest" => "empty",
        "Sec-Fetch-Mode" => "cors",
        "Sec-Fetch-Site" => "same-origin",
        "Pragma" => "no-cache",
        "Cache-Control" => "no-cache",
    ];
    
    
    private $biblioHeaders = [
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:105.0) Gecko/20100101 Firefox/105.0',
        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
        'Accept-Language' => 'fr,fr-FR;q=0.8,en-US;q=0.5,en;q=0.3',
        'Accept-Encoding' => 'gzip, deflate, br',
        'Content-Type' => 'application/x-www-form-urlencoded',
        'Origin' => 'https://www.biblio-paysvoironnais.fr',
        'Connection' => 'keep-alive',
        'Referer' => 'https://www.biblio-paysvoironnais.fr/opac/index/index/id_profil/1',
        'Upgrade-Insecure-Requests' => '1',
        'Sec-Fetch-Dest' => 'document',
        'Sec-Fetch-Mode' => 'navigate',
        'Sec-Fetch-Site' => 'same-origin',
        'Sec-Fetch-User' => '?1',
        'Pragma' => 'no-cache',
        'Cache-Control' => 'no-cache',
        'TE' => 'trailers',
    ];

    public $jar;

    public $options = [
        'allow_redirects' => false,
        'verify' => false,
        'form_params' => [
            'username' => '',
            'password' => '',
        ]];

    // constructor
    public function __construct($coo = null)
    {
        $this->cookies = $coo;
        $this->client = new Client();
        if ($coo != null) {
            $this->jar = $coo;
        } else {
            $this->jar = new CookieJar();
        }
    }

    public function clearCookies()
    {
        $this->cookies = "";
        $this->jar = new CookieJar();
    }

    private function initCookies(&$ch)
    {
        if (strlen($this->cookies) != 0) {
            curl_setopt($ch, CURLOPT_COOKIE, $this->cookies);
        }
    }

    private function saveCookies(&$output)
    {
        if (preg_match_all("/^Set-Cookie: (.*)$/mi", $output, $m)) {
            foreach ($m[1] as $coo) {
                if (strlen(strstr($coo, "PHPSESSID")) != 0) {
                    $this->cookies = $coo;
                }
            }
        }
    }

    private function buildOptions($data, $allow_redirect = true)
    {
        $retVal = [
            'allow_redirects' => $allow_redirect,
            'verify' => false,
            'cookies' => $this->jar];
        if ($data != null) {
            if ( gettype($data) == "string") {
                $retVal['body'] = $data;
            } else {
                $retVal['form_params'] = $data;                
            }
        }
        return $retVal;
    }

    public function post($url, $data, $follow_redirect = true)
    {
        $request = new Request('POST', $url, $this->headers);
        $res = $this->client->sendAsync($request, $this->buildOptions($data, $follow_redirect))->wait();
        $coom = "";
        $this->saveCookies($coom);
        $code = $res->getStatusCode();
        return $res->getBody()->getContents();
    }

    public function get($url, $data = null)
    {
        $request = new Request('GET', $url, $this->headers);
        $res = $this->client->sendAsync($request, $this->buildOptions($data))->wait();
        $coom = "";
        $this->saveCookies($coom);
        $code = $res->getStatusCode();
        return $res->getBody()->getContents();
    }

}
