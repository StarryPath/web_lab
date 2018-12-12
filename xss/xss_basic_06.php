<?php  
session_start();
if (isset($_SESSION['username']) && !empty($_SESSION['username']))
{
	setcookie ( "user_cookie", md5($_SESSION['username']),NULL,NULL,NULL,NULL,TRUE );
	header("Content-type: text/html;charset=utf-8");
	error_reporting(E_ALL || ~E_NOTICE);

}
else 
{
	header('Location:./index.php');
    exit();
}

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"> 
<title>xss_basic_06</title>
</head>
<body>
<div>
  <h1><?=$_SESSION['username']?>你好</h3>
  
</div>
<table width=700 border="1" align="left" cellpadding="5" cellspacing="1" bgcolor="#add3ef"> 
<tr bgcolor="#eff3ff"><td> <font size="6" color="black">留言板</font><br><br>
<form method="post" action="xss_basic_06.php">
 <font size="4" color="black">标题：</font><input style="height:25px; font-size:22px;" size=55 type=text name=sub> <br>
 <font size="4" color="black">内容：</font><textarea cols='' rows=4 name=content style='width: 600;'></textarea><br>
<input type=submit name=submit  style='font-size:18px' value="提交">
</form>
</td></tr>
</table>
<?php
session_start();
    function remove_xss($val) {
       // remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
       // this prevents some character re-spacing such as <java\0script>
       // note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs
       $val = preg_replace('/([\x00-\x08\x0b-\x0c\x0e-\x19])/', '', $val);
       // straight replacements, the user should never need these since they're normal characters
       // this prevents like <IMG SRC=@avascript:alert('XSS')>
       $search = 'abcdefghijklmnopqrstuvwxyz';
       $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
       $search .= '1234567890!@#$%^&*()';
       $search .= '~`";:?+/={}[]-_|\'\\';
       for ($i = 0; $i < strlen($search); $i++) {
          // ;? matches the ;, which is optional
          // 0{0,7} matches any padded zeros, which are optional and go up to 8 chars
          // @ @ search for the hex values
          $val = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val); // with a ;
          // @ @ 0{0,7} matches '0' zero to seven times
          $val = preg_replace('/(�{0,8}'.ord($search[$i]).';?)/', $search[$i], $val); // with a ;
       }
       // now the only remaining whitespace attacks are \t, \n, and \r
       $ra1 = array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style',
	   'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
       $ra2 = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy',
	   'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 
	   'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect',
	   'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 
	   'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 
	   'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup',
	   'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 
	   'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 
	   'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter',
	   'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart',
	   'onstart', 'onstop', 'onsubmit', 'onunload');
       $ra = array_merge($ra1, $ra2);
       $found = true; // keep replacing as long as the previous round replaced something
       while ($found == true) {
          $val_before = $val;
          for ($i = 0; $i < sizeof($ra); $i++) {
             $pattern = '/';
             for ($j = 0; $j < strlen($ra[$i]); $j++) {
                if ($j > 0) {
                   $pattern .= '(';
                   $pattern .= '(&#[xX]0{0,8}([9ab]);)';
                   $pattern .= '|';
                   $pattern .= '|(�{0,8}([9|10|13]);)';
                   $pattern .= ')*';
                }
                $pattern .= $ra[$i][$j];
             }
             $pattern .= '/i';
             $replacement = substr($ra[$i], 0, 2).'<x>'.substr($ra[$i], 2); // add in <> to nerf the tag
             $val = preg_replace($pattern, $replacement, $val); // filter out the hex tags
             if ($val_before == $val) {
                // no replacements were made, so exit the loop
                $found = false;
             }
          }
       }
       return $val;
    }
if(isset($_POST["submit"]) && $_POST["submit"] == "提交")
{
	
	$username=$_SESSION['username'];
	$sub=$_POST["sub"];
	$content=$_POST["content"];
	//转小写
	$sub=strtolower($sub);
	$content=strtolower($content);
	//替换
	$sub=str_replace("<",'&#60;',$sub);
	$content=str_replace("<",'&#60;',$content);
	$sub=str_replace(">",'&#62;',$sub);
	$content=str_replace(">",'&#62;',$content);
	$sub=str_replace('"','&#34;',$sub);
	$content=str_replace('"','&#34;',$content);
	$flag=0;
	while($flag==0)
	{
		if((preg_match('/<script>/',$sub)!=0)||(preg_match('/<script>/',$content)!=0))
		{
			$sub=str_replace("<script>",'',$sub);
			$content=str_replace("<script>",'',$content);
			$sub=str_replace("</script>",'',$sub);
			$content=str_replace("</script>",'',$content);
		}
		else
		{
			$flag=1;
		}
		
	}
	$sub=remove_xss($sub);
	$content=remove_xss($content);
	if ($sub==""||$content=="")
	{  
		echo "<script>alert('标题或内容不能为空！');</script>";  
	} 
	else
	{
		$mysqli = new mysqli("localhost", "admin", "123456", "xss");
		$mysqli->set_charset('utf8');
		$sql = "insert into xss6(username,sub,content) values(?,?,?)";
        
		$stmt = $mysqli->stmt_init();  
		
		$stmt ->prepare($sql);
		$stmt->bind_param('sss',$username,$sub,$content);
		$stmt->execute();
		$stmt->close();
		$mysqli->close();
	}
}
header("Content-type: text/html;charset=utf-8");
error_reporting(E_ALL || ~E_NOTICE);

$connect2=mysql_connect("localhost", "admin", "123456")or die("连接失败");
mysql_query("set names utf8");
$db2=mysql_select_db("xss",$connect2)or die("选择失败");
$sql2="select * from xss6 order by id desc limit 0,10";
$query2=mysql_query($sql2,$connect2)or die ("无法执行sql语句");
?>
<table width=500 border="0" align="right" cellpadding="5" cellspacing="1" bgcolor="#add3ef"> 
<?php 

while($list=mysql_fetch_array($query2))
{
	$str1=$list[username];
	$str2=$list[sub];
	$str3=$list[content]; 
?> 
<tr bgcolor="#eff3ff"> 
<td><b><big> 
用户：<?= $str1?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
标题：<?= $str2?></big></td> 
</tr> 
<tr bgColor="#ffffff"> 
<td>内容：<?= $str3?></td> 
</tr> 
<?php 
} 
?> 


  
</body>
</html>
