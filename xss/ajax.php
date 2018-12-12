<?php
session_start();
	$sno=$_REQUEST['sno'];
	if ($sno=="")
	{  
		echo "<script>alert('请输入学号！');</script>";  
	} 
	else
	{
		$mysqli = new mysqli("localhost", "root", "root", "Student_status");
		$mysqli->set_charset('utf8');
		$sql = "select * from  student_info where (sno=?)";
        
		$stmt = $mysqli->stmt_init();  
		
		$stmt ->prepare($sql);
		$stmt->bind_param('i',$sno);
		$stmt->execute();// 执行SQL语句
		$stmt->store_result();// 取回全部查询结果
		$stmt->bind_result($id,$sno2,$sclass,$sname,$sage,$ssex,$saddr);// 当查询结果绑定到变量中
		$stmt->fetch();
		echo $id,$sno2,$sclass,$sname,$sage,$ssex,$saddr;
		$stmt->close();
		$mysqli->close();
	}
}

   
?> 
