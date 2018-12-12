<?php  
session_start();
if (isset($_SESSION['username']) && !empty($_SESSION['username']))
{
	header("Content-type: text/html;charset=utf-8");
	error_reporting(E_ALL || ~E_NOTICE);	
}
else 
{
	header('Location:./index.php');
    exit();
}
?>
<?php header("Content-Security-Policy: script-src data: ;default-src 'self';");?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"> 
<!--<meta http-equiv="Content-Security-Policy" content="script-src 'self' ;default-src 'self';">-->

<title>xss_csp</title>
</head>
<img src="http://www.xfcxc.top/Adgainai4/xss.png"   alt="xss" />
<img src="https://www.baidu.com/img/baidu_jgylogo3.gif" >
<body>
<div>
  <h1><?=$_SESSION['username']?>你好</h3>
  
</div>

<table width=700 border="1" align="left" cellpadding="5" cellspacing="1" bgcolor="#add3ef"> 
<tr bgcolor="#eff3ff"><td> <font size="6" color="black">留言板</font><br><br>
<form method="post" action="xss_csp.php">
 <font size="4" color="black">标题：</font><input style="height:25px; font-size:22px;" size=55 type=text name=sub> <br>
 <font size="4" color="black">内容：</font><textarea cols='' rows=4 name=content style='width: 600;'></textarea><br>
<input type=submit name=submit  style='font-size:18px' value="提交">
</form>
</td></tr>
</table>
<script nonce="random123">alert('nonce')</script>
<?php
session_start();
if(isset($_POST["submit"]) && $_POST["submit"] == "提交")
{
	
	$username=$_SESSION['username'];
	$sub=$_POST["sub"];
	$content=$_POST["content"];

	if ($sub==""||$content=="")
	{  
		echo "<script>alert('标题或内容不能为空！');</script>";  
	} 
	else
	{
		$mysqli = new mysqli("localhost", "admin", "123456", "xss");
		$mysqli->set_charset('utf8');
		$sql = "insert into xss_csp(username,sub,content) values(?,?,?)";
        
		$stmt = $mysqli->stmt_init();  
		
		$stmt ->prepare($sql);
		$stmt->bind_param('sss',$username,$sub,$content);
		$stmt->execute();
		$stmt->close();
		$mysqli->close();
	}
}
header("Content-type: text/html;charset=utf-8");
error_reporting(E_ALL || ~E_NOTICE);

$connect2=mysql_connect("localhost", "admin", "123456")or die("连接失败");
mysql_query("set names utf8");
$db2=mysql_select_db("xss",$connect2)or die("选择失败");


$sql2="select * from xss_csp order by id desc limit 0,10";
$query2=mysql_query($sql2,$connect2)or die ("无法执行sql语句");


	//print "id=$list[id]<br>用户：$str1<br>标题：$str2<br>内容：$str3<br>";

?>
<table width=500 border="0" align="right" cellpadding="5" cellspacing="1" bgcolor="#add3ef"> 
<?php 

while($list=mysql_fetch_array($query2))
{
	$str1=$list[username];
	$str2=$list[sub];
	$str3=$list[content]; 
?> 
<tr bgcolor="#eff3ff"> 
<td><b><big> 
用户：<?= $str1?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
标题：<?= $str2?></big></td> 
</tr> 
<tr bgColor="#ffffff"> 
<td>内容：<?= $str3?></td> 
</tr> 
<?php 
} 
?> 


  
</body>
</html>
