<?php
session_start(); 
include("setting.php");
header("Content-type: text/html;charset=utf-8");
error_reporting(E_ALL || ~E_NOTICE);


	
$username=$_POST["username"]; 
$password=$_POST["password"];
 
function set_token() { 
	
	
    $_SESSION['token'] = md5(microtime(true) ."check"); 
	
} 
function valid_token() { 
    
	$str=$_SESSION['token'];
	for($i = 0;$i < strlen($str);$i=$i+2){
 
    $resultString.=ord($str[$i]);//把字符转换成ascii码
	$resultString.=$str[$i];
	}
	$arr=str_split($resultString);
	$arr=array_count_values($arr);
	
	//var_dump(array_values($arr)); 
	for($j=0;$j<count($arr);$j++)
	{
		$num.=array_values($arr)[$j];
	}
	//echo $num;
	$finalStr=$num.$resultString;
	$return = $_REQUEST['token'] === $finalStr ? true : false;
    set_token();
	

    return $return; 

} 

 

//如果token为空则生成一个token 

if(!isset($_SESSION['token']) || $_SESSION['token']=='') { 
	
    set_token(); 
} 
if(isset($_POST['username'])){ 
    if(!valid_token()){ 
        echo "token error"; 
		exit;
    }
	}
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

//检查密码是否正确
$sql = "SELECT username,password FROM user WHERE (username = ?) and (password = ?)";
$stmt = $mysqli->stmt_init();
$stmt->prepare($sql);
$stmt->bind_param("ss",$username, $password);
$stmt->execute();
$stmt->bind_result($uname, $pword);
$state=1;
if(!$stmt->fetch())
{
	$state=0;
	echo '<script>alert("登录失败")</script>';
	
}

echo $uname, $pword;

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

<form method=post action=login.php>
<input type="hidden" name="token" value="" id="token">
user：<input type=text name=username  ><br>
password：<input type=password name=password > <br>
<input type=submit name=btn value=登录>
</form>	
<script>
var token='<?php echo $_SESSION['token']?>';
var temp="";
var res=[];
var count=0;
var sum=[];

for(var i=0;i<token.length;i=i+2)
{
	//document.write(token.charCodeAt(i)+" "+token[i]+"<br>");
	temp+=token.charCodeAt(i)+token[i];
	//document.write("temp:"+temp+"<br>");
}
var arr=temp.split("");
for(var i=0;i<arr.length;i++)
{
	if(res.indexOf(arr[i])==-1)
	{
		res.push(arr[i]);
	}
}
for(var i=0;i<res.length;i++)
{
	for(var j=0;j<arr.length;j++)
	{
		if(arr[j]==res[i])
		{
			count++;
		}
	}
	//document.write(res[i]+" "+count+"<br>");
	sum.push(count);
	
	count=0;
}
num=sum.join("");

var finalStr=num+temp;
document.getElementById('token').value= finalStr;
//document.write(finalStr);
</script>
</body>
</html>