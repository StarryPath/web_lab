<?php
header("Content-type: text/html;charset=utf-8");
error_reporting(E_ALL || ~E_NOTICE);
$mysqli = new mysqli("localhost", "root", "as971226", "fuyao");
 
$username = "somename";
$password = "someword";
 
$query = "SELECT * FROM test1 WHERE (id = ?) and (number = ?)";
 
$stmt = $mysqli->stmt_init();
 
if ($stmt->prepare($query)) {
    $stmt->bind_param("ii", $id, $number);
    $stmt->execute();
 
    $stmt->bind_result($id, $number);
    while ($stmt->fetch()) {
        printf ("%d : %d\n", $id, $number);
    }
    $stmt->close();
}
 
$mysqli->close();
?>