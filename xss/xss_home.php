<?php  
session_start();
if (isset($_SESSION['username']) && !empty($_SESSION['username']))
{
	header("Content-type: text/html;charset=utf-8");
	error_reporting(E_ALL || ~E_NOTICE);
	echo $_SESSION['username']."你好";
}
else 
{
	header('Location:./index.php');
    exit();
}
?>
<html>
<head>
<title>xss_home</title>  
<meta http-equiv="content-type" content="text/html;charset=utf-8">
</head>  
<body>
</br>
<a href="./xss_basic_01.php">xss_basic_01--基础环境</a></br>
<a href="./xss_basic_02.php">xss_basic_02--关键词过滤&lt;script&gt;</a> </br>
<a href="./xss_basic_03.php">xss_basic_03--关键词过滤&lt;script&gt;+大小写转换+循环过滤</a> </br>
<a href="./xss_basic_04.php">xss_basic_04--开启httponly</a> </br>
<a href="./xss_basic_05.php">xss_basic_05--实体化编码处理</a> </br>
<a href="./xss_basic_06.php">xss_basic_06--添加XSS filter</a> </br>
<a href="./xss_csp.php">xss_csp</a> </br>
</body>
</html>
