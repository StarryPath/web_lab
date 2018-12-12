<?php
function printerror($error=""){
	
	
	
	$gotourl_js="history.go(-1)";
			
		
	echo"<script>alert('".$error."');".$gotourl_js."</script>";
		
	exit();
	
}
function RepPostVar($val)
{
	if($val!=addslashes($val))
	{
		exit();
	}
	
	$val=str_replace("%","",$val);
	$val=str_replace(" ","",$val);
	$val=str_replace("\t","",$val);
	$val=str_replace("%20","",$val);
	$val=str_replace("%27","",$val);
	$val=str_replace("*","",$val);
	$val=str_replace("'","",$val);
	$val=str_replace("\"","",$val);
	$val=str_replace("/","",$val);
	$val=str_replace(";","",$val);
	$val=str_replace("#","",$val);
	$val=str_replace("--","",$val);
	
	$val=addslashes($val);
	
	return $val;
}
?>