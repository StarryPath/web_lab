<?php  
header("Content-type: text/html;charset=utf-8");
error_reporting(E_ALL || ~E_NOTICE);

 
        if(isset($_POST["submit"]) && $_POST["submit"] == "登陆")  
        {  
            $user = $_POST["username"];  
            $psw = $_POST["password"]; 
			
						
            if($user == "" || $psw == "")  
            {  
                echo "<script>alert('请输入用户名或密码！'); history.go(-1);</script>";  
            }  
            else  
            {  
                $mysqli = new mysqli("localhost", "admin", "123456", "xss");
                $query = "SELECT username,password from user where (username = ?) and (password = ?)";
                $stmt = $mysqli->stmt_init();   
				if ($stmt->prepare($query)) 
				{
					$stmt->bind_param("ss", $user, $psw);
					$stmt->execute();
				}
				$result = $stmt->get_result();
				$data = $result->fetch_all(MYSQLI_ASSOC);
				$stmt->close();
				$mysqli->close();
                if($data)  
                {  
                    session_start();
					$_SESSION['username']=$user;
					setcookie ( "user_cookie", md5($user) ); 	
					header("Location: ./xss_home.php");
                }  
                else  
                {  
                    echo "<script>alert('用户名或密码不正确！');history.go(-1);</script>";  
                }  
            }  
        }  
        else  
        {  
            echo "<script>alert('提交未成功！'); history.go(-1);</script>";  
        }  
      
?>  