<?php
header("Content-type: text/html;charset=utf-8");
error_reporting(E_ALL || ~E_NOTICE);
$link=mysql_connect("localhost","root","as971226") or die("link fail");
mysql_query("set names 'utf8'");//读库
mysql_select_db("fuyao",$link)or die("select_db fail");
$id=$_POST["id"]; 
$number=$_POST["number"];
$str="select count(*) from test1 where id='$id' and number='$number';";
echo $str;
$query=mysql_query($str,$link) or die("无法执行sql语句");
list($mycount)=mysql_fetch_row($query);
if($mycount==0)
{
	echo "序号或学号错误！！！！！！";
	echo "$name---$number";
	include"login.html";
	exit;
}
else
	header("location:http://localhost/aaa/a.php?id=$id&number=$number");
?>

