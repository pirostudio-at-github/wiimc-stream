<?php
require_once('../creds.php');
header("Content-Type: text/plain;charset=UTF-8;");

phpinfo(INFO_ENVIRONMENT | INFO_VARIABLES);
print("creds ".$creds);
