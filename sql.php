<?php

$id = $_GET['uid'];

echo "执行的sql语句为：" ;
echo "select * from user where id=" . $id . "<br/>";
echo "<hr>";

$mysqli = new mysqli('127.0.0.1','root','as971226','fuyao'); 

if(mysqli_connect_errno()){
    printf("连接失败:%s<br>",mysqli_connect_error());
    exit();
}

$result = $mysqli->query("select * from test1 where id=$id");

while(list($id,$college,$number,$name,$str1,$str2)=$result->fetch_row()){
    echo "用户ID：" . $id . "<br/>";
    echo "用户名：" . $college . "<br/>";
    echo "用户密码：" . $number . "<br/>";
}

$result->close();
$mysqli->close()

?>