<html>
<body>
<form action='xss.php' method='post'>
<input  name='a' value=''>
<input name='c' type='submit'>
</form>
</body>
</html>

<?php
$b=$_POST['a'];
echo $b;
?>