<?php 
header("Content-type: text/html; charset=utf-8"); 
echo "<h3>xxe漏洞测试！</h3>";  
$xml = '<?xml version="1.0" encoding="utf-8"?> 
    <!DOCTYPE roottag PUBLIC "-//VSR//PENTEST//EN" "http://in">
    <roottag>not an entity attack!</roottag>';  
    try{   
    $doc = simplexml_load_string($xml);   
    }   
    catch(Exception $e){   
print "error";   
}  
?>  