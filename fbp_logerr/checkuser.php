<?php
session_start(); 
include("setting.php");
header("Content-type: text/html;charset=utf-8");
error_reporting(E_ALL || ~E_NOTICE);
$username=$_POST["username"]; 
$password=$_POST["password"];
$time=time();
$ss_id=md5($username+"check");
session_id($ss_id);
//锁定帐号的时间
$time_before=$time-1800;
require("functions.php");
//传参
$username=RepPostVar($username);
$password=RepPostVar($password);
//连接数据库
$mysqli = new mysqli($server, $db_user, $db_passwd, 'userdata');
if(!$mysqli)
die("connect error:".mysqli_connect_error());
//else
//echo "success connect mysql\n";
$mysqli->set_charset('utf8');
//一分钟内登录错误5次 以上，锁定30分钟
$limit="select logintime from user_login_info where state=0 and username=? and logintime BETWEEN ? and ? order by id desc limit 5 ;";
$stmt = $mysqli->stmt_init();
$stmt->prepare($limit);
$stmt->bind_param("sdd",$username,$time_before,$time);
$stmt->execute();
$stmt->bind_result($limit_time);
$stmt->fetch();
$time1=$limit_time;
$i=0;
while ($stmt->fetch()){$i++;}
$time2=$limit_time;
$time3=$time1-$time2;
if(($i==4)&&($time3!=0)&&($time3<60))
{
	echo '你刚刚输错很多次密码，为了保证账户安全，系统已经将您账号锁定';

    echo '<meta http-equiv="refresh" content="2;url=./login.html">';
    exit;
}
//一小时内登录错误10次 以上，锁定30分钟
$limit2="select logintime from user_login_info where state=0 and username=? and logintime BETWEEN ? and ? order by id desc limit 10 ;";
$stmt = $mysqli->stmt_init();
$stmt->prepare($limit2);
$stmt->bind_param("sdd",$username,$time_before,$time);
$stmt->execute();
$stmt->bind_result($limit_time);
$stmt->fetch();
$time1=$limit_time;
$i=0;
while ($stmt->fetch()){$i++;}
$time2=$limit_time;
$time3=$time1-$time2;
if(($i==9)&&($time3!=0)&&($time3<3600))
{
	echo '你刚刚输错很多次密码，为了保证账户安全，系统已经将您账号锁定';

    echo '<meta http-equiv="refresh" content="2;url=./login.html">';
    exit;
}

//检查密码是否正确
$sql = "SELECT username,password FROM user WHERE (username = ?) and (password = ?)";

$stmt->prepare($sql);
$stmt->bind_param("ss",$username, $password);
$stmt->execute();
$stmt->bind_result($uname, $pword);
$state=1;
if(!$stmt->fetch())
{
	$state=0;
	echo "用户名或密码错误";
	echo '<meta http-equiv="refresh" content="2;url=./login.html">';

}

echo $uname, $pword;
//记录日志

$info="insert into user_login_info(username,logintime,state) values(?,?,?)";
 
		
$stmt ->prepare($info);
$stmt->bind_param('sdd',$username,$time,$state);
$stmt->execute();
$stmt->close();
$mysqli->close();
if($state==1)
header("location:welcome.html");
	
?>