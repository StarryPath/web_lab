<?php  
session_start();
if (isset($_SESSION['username']) && !empty($_SESSION['username']))
{
	setcookie ( "user_cookie", md5($_SESSION['username']),NULL,NULL,NULL,NULL,TRUE );
	;
	error_reporting(E_ALL || ~E_NOTICE);
	
}
else 
{
	header('Location:./index.php');
    exit();
}

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; "> 
<title>xss_utf7</title>
</head>
<body>
<div>

  <h1><?=$_SESSION['username']?> Welcome</h3>
  
</div>
<table width=700 border="1" align="left" cellpadding="5" cellspacing="1" bgcolor="#add3ef"> 
<tr bgcolor="#eff3ff"><td> <font size="6" color="black">Message Board</font><br><br>
<form method="post" action="xss_utf7.php">
 <font size="4" color="black">Title:</font><input style="height:25px; font-size:22px;" size=55 type=text name=sub> <br>
 <font size="4" color="black">Content:</font><textarea cols='' rows=4 name=content style='width: 600;'></textarea><br>
<input type=submit name=submit  style='font-size:18px' value="submit">
</form>
</td></tr>
</table>
<?php
session_start();
if(isset($_POST["submit"]) && $_POST["submit"] == "submit")
{
	
	$username=$_SESSION['username'];
	$sub=$_POST["sub"];
	$content=$_POST["content"];
	
	//替换
	$sub=str_replace("<",'&#60;',$sub);
	$content=str_replace("<",'&#60;',$content);
	$sub=str_replace(">",'&#62;',$sub);
	$content=str_replace(">",'&#62;',$content);
	$sub=str_replace('"','&#34;',$sub);
	$content=str_replace('"','&#34;',$content);
		
	$flag=0;
	while($flag==0)
	{
		if((preg_match('/<script>/',$sub)!=0)||(preg_match('/<script>/',$content)!=0))
		{
			$sub=str_replace("<script>",'',$sub);
			$content=str_replace("<script>",'',$content);
			$sub=str_replace("</script>",'',$sub);
			$content=str_replace("</script>",'',$content);
		}
		else
		{
			$flag=1;
		}
		
	}
	
	if ($sub==""||$content=="")
	{  
		echo "<script>alert('标题或内容不能为空！');</script>";  
	} 
	else
	{
		$mysqli = new mysqli("localhost", "admin", "123456", "xss");
		$mysqli->set_charset('utf8');
		$sql = "insert into xss5(username,sub,content) values(?,?,?)";
        
		$stmt = $mysqli->stmt_init();  
		
		$stmt ->prepare($sql);
		$stmt->bind_param('sss',$username,$sub,$content);
		$stmt->execute();
		$stmt->close();
		$mysqli->close();
	}
}

error_reporting(E_ALL || ~E_NOTICE);

$connect2=mysql_connect("localhost", "admin", "123456")or die("连接失败");
mysql_query("set names utf8");
$db2=mysql_select_db("xss",$connect2)or die("选择失败");
$sql2="select * from xss5 order by id desc limit 0,10";
$query2=mysql_query($sql2,$connect2)or die ("无法执行sql语句");
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
User:<?= $str1?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
Title:<?= $str2?></big></td> 
</tr> 
<tr bgColor="#ffffff"> 
<td>Content:<?= $str3?></td> 
</tr> 
<?php 
} 
?> 


  
</body>
</html>
