<?php
require_once("Crypto.php");

class Creds {
    public static $creds = "";
    public static $code = "";
    public static $creds_uri = "";

    public static function getMediaUser() {
        if( isset(Creds::$creds[0]))
            return Creds::$creds[0];
        else
            return "";
    }
    public static function getMediaPassword() {
        if( isset(Creds::$creds[1]))
            return Creds::$creds[1];
        else
            return "";
    }

    public static function updateWithUrl($url) {
        Creds::$code = extractCreds($url);
        Creds::$creds_uri = "/_/".Creds::$code;
        Creds::$creds = explode("#",Crypto::decrypt(urldecode(Creds::$code)));    
    }
    public static function updateWithCode($code) {
        Creds::$code = $code;
        Creds::$creds_uri = "/_/".Creds::$code;
        Creds::$creds = explode("#",Crypto::decrypt(urldecode(Creds::$code)));    
    }
}
function extractCreds($bla) {
    if(isset($bla)) {
        $fields = explode("/",$bla);
        if ( $fields[1] == "_" )
            return $fields[2];
    }
    return "";
}

if(isset($_GET['c'])) {
    Creds::updateWithCode($_GET['c']);
}

else if(isset($_SERVER['REQUEST_URI'])) {
    Creds::updateWithUrl($_SERVER['REQUEST_URI']);
}

else if(isset($_SERVER['REDIRECT_REDIRECT_SCRIPT_URL'])) {
    Creds::updateWithUrl($_SERVER['REDIRECT_REDIRECT_SCRIPT_URL']);
}