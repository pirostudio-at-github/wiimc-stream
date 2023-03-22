<html>
   <body>
    <h1>WiiStream Credential Configuration</h1>
    Please give you connexion information for <br/>
    paysvoironnais.mediatheques.fr
    <form action="config.php" method="GET">
        <p> username <input type="text" id="username" name="username" ></input></p>
        <p> password <input type="password" id="pass" name="pass" ></input></p>
        <input type="submit" value="Generate"></input>
    </form> 
  
<?php
require_once("crypto.php");

if (isset($_GET["username"])) {
    $code = Crypto::encrypt($_GET["username"]."#".$_GET["pass"]);
    print("<p>http://localhost/_/".urlencode($code)."/wiimc");
    print("<p>".Crypto::decrypt($code));
} else {
    print("No code !");
}
?>
 </body>
</html>