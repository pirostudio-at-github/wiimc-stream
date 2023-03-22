<?php

class Crypto {

    private static $password = "123654AZER";

    public static function encrypt($data) {
       // $comp = gzdeflate(gzdeflate($data,9),9);
       $crypted = openssl_encrypt($data,"AES-128-ECB",Crypto::$password);
       return base64_encode($crypted);
    }

    public static function decrypt($data) {
        $decoded = base64_decode($data);
        $comp = openssl_decrypt($decoded,"AES-128-ECB",Crypto::$password);
        //return gzinflate(gzinflate($comp));
        return $comp;

    }
}
/*
$string_to_encrypt="Test";
$password="password";
$encrypted_string=openssl_encrypt($string_to_encrypt,"AES-128-ECB",$password);
$decrypted_string=openssl_decrypt($encrypted_string,"AES-128-ECB",$password);*/