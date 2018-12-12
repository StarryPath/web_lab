<?php
header("Content-type: text/html;charset=utf-8");
error_reporting(E_ALL || ~E_NOTICE);
$link=mysql_connect("localhost","root","as971226") or die("link fail");

mysql_query("set names 'utf8'");//读库
mysql_select_db("fuyao",$link)or die("select_db fail");
	$id=$_GET['id'];
	$sql = "select * from test1 where id='$id';";
	$result = mysql_query($sql);
	while($row = mysql_fetch_array($result))
	{
		echo "用户ID：" . $row['id'] . "<br/>";
		echo "学院：" . $row['college'] . "<br/>";
		echo "学号 ：" . $row['number'] . "<br/>";
		echo "姓名 ：" . $row['name'] . "<br/>";
		echo "称号 ：" . $row['str1'] . "<br/>";
		echo "奖项 ：" . $row['str2'] . "<br/>";
	}
?>
