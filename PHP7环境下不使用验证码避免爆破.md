# PHP7环境下不使用验证码避免爆破


> target：理解临时唯一凭证以及访问行为日志的作用及效果。

---

思路一：临时token+js随机混淆算法

--------------------
大致流程：
每次用户访问时服务器生成一个随机token，在返回包中发给用户，前端js代码将token进行混淆，当用户再次访问服务器时会带上混淆后的token，服务器检查token是否正确，若不正确则拒绝此次请求，由于每次用户访问服务器都会获得新的token，所以可以防止用户重复提交密码。

token生成流程：
1、如果token为空，则生成一个token；
2、当提交用户名时，检查token是否正确，检查之后再次生成一个新的token；
3、token储存在session里。

js混淆token流程：
1、`var token='<?php echo $_SESSION['token']?>';` 获取token值；
2、前端js和后端php通过一系列相同的字符处理函数和字符串处理函数对token值进行混淆；
3、检验`$_REQUEST['token']` 与 `$_SESSION['token']`是否相同。

    <?php
    session_start(); 
    include("setting.php");
    header("Content-type: text/html;charset=utf-8");
    error_reporting(E_ALL || ~E_NOTICE);
    
核心文件代码：
    
    
    	
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


思路二:记录用户一段时间内登录错误次数

--------------------
大致流程：通过登录日志表，记录用户的登录时间以及登录成功与否，对用户在一段时间内的登录失败次数进行计算。如：一分钟错误次数超过5次或一小时错误次数超过10次，就锁定当前用户30分钟，禁止登录。

核心文件代码：

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