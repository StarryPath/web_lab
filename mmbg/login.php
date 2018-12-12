<?php
session_start(); 
include("setting.php");
header("Content-type: text/html;charset=utf-8");
error_reporting(E_ALL || ~E_NOTICE);
$username=$_POST["username"]; 
$password=$_POST["password"];
$key=$_POST["key"];
require("functions.php");
//传参
if($_POST['username'])
{
$username=RepPostVar($username);
$password=RepPostVar($password);

//连接数据库
$mysqli = new mysqli($server, $db_user, $db_passwd, 'userdata');
if(!$mysqli)
die("connect error:".mysqli_connect_error());
//else
//echo "success connect mysql\n";
$mysqli->set_charset('utf8');

//查询密码
$sql = "SELECT password FROM user WHERE (username = ?) ";
$stmt = $mysqli->stmt_init();
$stmt->prepare($sql);
$stmt->bind_param("s",$username);
$stmt->execute();
$stmt->bind_result($pword);
$state=1;
$stmt->fetch();



for($i = 0;$i < strlen($pword);$i++){

$resultString.=ord($pword[$i]);//把字符转换成ascii码
$resultString.=$pword[$i];
}
$resultString=$resultString.$key;
$finalPasswd=md5($resultString);
$return = $password === $finalPasswd ? true : false;
if(!$return)
{
	$state=0;
	echo '<script>alert("登录失败")</script>';
}
$stmt->close();
$mysqli->close();
if($state==1)
header("location:welcome.html");
}
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>login</title>
</head>
<body>

<form >
user：<input type=text name=user  value="" id="user"><br>
password：<input type=password name=passwd value="" id="passwd">
</form>	
<form method=post action=login.php>
<input type="hidden" name="key" value="" id="key">
<input type="hidden" name=username  value="" id="username">
<input type="hidden" name=password  value="" id="password">
<input type=submit name=btn value=登录>
</form>
<script src="md5.js" type="text/javascript"></script>
<script>
var key=new Date().getTime();
//document.write(key);
var passwd = document.getElementById("passwd");
var pv="";
var temp="";
passwd.oninput = function(event) {
    pv= passwd.value; 
	//document.write(typeof passwd.value);
	for(var i=0;i<pv.length;i++)
	{
		
		temp+=pv.charCodeAt(i)+pv[i];
	
	}
	temp=temp+key;
	finalKey=hex_md5(temp);
	
	document.getElementById("key").value=key;
	document.getElementById("username").value=document.getElementById("user").value;
	document.getElementById("password").value=finalKey;
}

</script>

</body>
</html>