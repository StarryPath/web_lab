<?php
header("Content-type: text/html;charset=utf-8");
error_reporting(E_ALL || ~E_NOTICE);
$id = $_GET['id'];
$number=$_GET['number'];

echo "执行的sql语句为：" ;
echo "select * from test1 where id='" . $id . "'and number='".$number."';"."<br/>";
echo "<hr>";
$mysqli = new mysqli('127.0.0.1','root','as971226','fuyao'); 
if(mysqli_connect_errno()){
    printf("连接失败:%s<br>",mysqli_connect_error());
    exit();
}
$result = $mysqli->query("select * from test1 where id='$id' and number='$number';");


while(list($id,$college,$number,$name,$str1,$str2)=$result->fetch_row()){
    echo "用户ID：" . $id . "<br/>";
    
    echo "用户密码：" . $number . "<br/>";
}
$result->close();
$mysqli->close();


include($_GET['page']);
?>