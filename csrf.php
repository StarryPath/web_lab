<html>
<head><meta http-equiv="Content-Type" content="textml; charset=utf-8"> </head>
<body>
<table width="58%" border="0" >
<tr><td> 留言板<br><br>
<form method="post" action="csrf.php">
用户：<input type=text name =anonym> <br>
标题：<input type=text name=sub> <br>
内容：<textarea cols='' rows=2 name=content style='width: 300;'></textarea><br>
<input type=submit name=mysubmit>
</form>
</td></tr>
</table>
<?php
header("Content-type: textml;charset=utf-8");
error_reporting(E_ALL || ~E_NOTICE);
include"setup.php";
$anonym=$_POST["anonym"];
$sub=$_POST["sub"];
$content=$_POST["content"];
if($anonym!='')
{
	$sql1="insert into $NEWSTABLE values('id','$anonym','$sub','$content');";
	$connect1=mysql_connect($DB_SERVER,$DB_USER,$DB_PASS)or die("连接失败");
	mysql_query("set names utf8");
	$db1=mysql_select_db($DB_NAME,$connect1)or die("选择失败");
	$query1=mysql_query($sql1,$connect1)or die("not connect newstable");
}
$connect2=mysql_connect($DB_SERVER,$DB_USER,$DB_PASS)or die("连接失败");
mysql_query("set names utf8");
$db2=mysql_select_db($DB_NAME,$connect2)or die("选择失败");
$sql2="select * from $NEWSTABLE order by id desc";
$query2=mysql_query($sql2,$connect2)or die ("无法执行sql语句");
while($list=mysql_fetch_array($query2))
{
	$str1=$list[anonym];
	$str2=$list[sub];
	$str3=$list[content];
	
	print "id=$list[id]<br>用户：$str1<br>标题：$str2<br>内容：$str3<br>";

	
}
?>

</body>
</html>
