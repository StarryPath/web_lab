# HTTP协议下每次用户密码传输格式变更

    target：js实践内容之一

---

流程：利用js脚本对用户传参进行混淆，避免劫持导致用户密码失去原有效果。生成当前时间戳作为key值，使用两个表单接收用户信息，一个表单在前端显示出来，接收用户的输入，另一个表单在前端隐藏，用于向服务器传参。第一个表单接收的用户输入通过js的字符串处理后与时间戳拼接，通过md5计算后传给第二个表单。第二个表单将用户名，key和混淆后的password传给服务器。服务器端根据用户名查询密码，与收到的key值通过相同的方法生成password，与收到的password进行比较，完成流程。

核心文件代码：

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