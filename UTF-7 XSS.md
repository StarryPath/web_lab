# UTF-7 XSS

标签（空格分隔）： Web安全

---

一、utf-7 简介
----------

由于在过去SMTP的传输仅能接受7比特的字符，而当时Unicode并无法直接满足既有的SMTP传输限制，在这样地背景下UTF-7被提出。严格来说UTF-7不能算是Unicode所定义的字符集之一，较精确的来说，UTF-7是提供了一种将Unicode转换为7比特US-ASCII字符的转换方式。
有些字符本身可以直接以单一的ASCII字符来呈现。第一个组群被称作“direct characters”，其中包含了62个数字与英文字母，以及包含了九个符号字符：**' ( ) , - . / : ?**。这些“direct characters”被认为可以很安全的直接在文件里呈现。
另一个主要的组群称作“optional direct characters”，其中包含了所有可被打印的字符，这些字符在U+0020～U+007E之间，除了~ \ +和空白字符以外。这些“optional direct characters”（例如< > = ）的使用虽可减少空间的使用也可增加人的可阅读性，但却会因为一些不良设计的邮件网关而会产生一些错误，导致必须使用额外的转义字符。

    举个例子：< 编码为+ADw- 
              >编码为+AD4-
              "编码为+ACI-

可以看到尖括号和双引号等通过utf-7编码后无敏感字符，所以可以想到通过utf-7编码进行xss攻击。百度和google都曾爆出过此类漏洞。

二、各浏览器对utf-7的支持
---------------

Chrome和Firefox早已不支持UTF-7，目前只有IE支持UTF-7编码格式。

三、利用条件
------

1、ie浏览器
2、在Content-Type中没有设置charset

四、在ie6/7中的利用
------------

早期在ie中的utf-7 xss攻击极为简单，ie浏览器会自动判断字符格式，没有指定charset，出现utf-7编码（如+ADw，+AC8-,等等）时，即解析。payload为：

    +ADw-script+AD4-alert(document.cookie)+ADw-/script+AD4-

此漏洞在ie8中被修复，但是ie浏览器还是支持utf-7的，所以基于utf-7 的xss攻击仍然存在。

五、ie8以后的利用
----------

测试环境为ie11。
先来看看页面和源码：
![此处输入图片的描述][1]


 这是一个简单的留言板系统，用户输入留言，写入数据库，再从数据库读出留言，显示在页面右端。再来看看部分源码：
 ![此处输入图片的描述][2]
 


  可以看到`<meta>`标签中并没有设置charset，就可以通过字符集抢占的方式使浏览器以UTF-7解析。这就给了使用utf-7 进行xss的机会。
  再来看看网页的防御：
  ![此处输入图片的描述][3]


 可以看到网站对尖括号和双引号进行了实体化编码。接下来就试验一下xss攻击能否成功。
 首先要解决一个问题，就是如何让浏览器对网页内容进行utf-7编码。上面已经提到了需要通过字符集的抢占，方法就是通过设置BOM头修改编码，并且BOM头的优先级是最高的，只要能控制目标网页的开头是UTF-7的BOM头，即可完成攻击。utf-7 bom 目前知道的有4个，如下：

    +/v8 
    +/v9 
    +/v+ 
    +/v/
    
使用的payload为：bom头+空格+xsscode
这里注意一定要有个空格，而且xsscode需要使用utf7编码。
这里我构造了如下payload：

    +/v8 +ADw-script+AD4-alert(123)+ADw-/script+AD4-
    
编码前为`<script>alert(123)</script>`
![此处输入图片的描述][4]


可以看到并没有弹框，原因很可能是utf7的bom头并没有在网页的开头，为了证明猜想的原因，在网页源代码的开头加上bom头，
![此处输入图片的描述][5]
成功弹窗。
![此处输入图片的描述][6]
所以我们只要在一个网页的头部插入此payload即可实现xss攻击。



  从实际场景出发能控制网页开头就是Json callback。首先要介绍一下Jsonp：
Jsonp(JSON with Padding) 是 json 的一种"使用模式"，可以让网页从别的域名（网站）那获取资料，即跨域读取数据，不受同源策略的限制，在网站开发过程中很常用。

这是一个本地的客户端网页：
![此处输入图片的描述][7]


 接下来是网站的源代码：
 ![此处输入图片的描述][8]


 可以看到完成了跨域请求。请求的网页为服务器端的jsonp.php,也就是产生漏洞的页面。可以看一下此客户端的源码：
 

    <?php
        //获取回调函数名
    
        $jsoncallback = htmlspecialchars($_REQUEST ['jsoncallback']);
        //json数据
        $json_data = '["Dont worry","Be happy"]';
    //输出jsonp格式的数据
    echo $jsoncallback . "(" . $json_data . ")";
        ?>
    
    
使用Jsonp类型的话，会创建一个查询字符串参数 callback=? ，这个参数会加在请求的URL后面。**服务器端应当在JSON数据前加上回调函数名，以便完成一个有效的JSONP请求。**
举个例子：
如客户想访问 : http://www.example.com/jsonp/jsonp.php?jsonp=callbackFunction。

假设客户期望返回JSON数据：["name1","name2"]。

真正返回到客户端的数据显示为: callbackFunction(["name1","name2"])。
可以看到客户端直接采用字符串拼接，来完成Jsonp请求，虽然在一开始使用htmlspecialchars函数进行实体化编码，但是使用utf7可以绕过。由于www.xfcxc.top全站使用utf-8编码，所以这里在本地进行测试。
在ie浏览器输入此payload：

    http://localhost/jsonp/jsonp.php?jsoncallback=%2b/v8 %2bADw-script%2bAD4-alert(123)%2bADw-/script%2bAD4-

![此处输入图片的描述][9]


  [1]: https://raw.githubusercontent.com/StarryPath/xss-utf7-png/master/QQ%E6%88%AA%E5%9B%BE20171225184459.png
  [2]: https://raw.githubusercontent.com/StarryPath/xss-utf7-png/master/QQ%E6%88%AA%E5%9B%BE20171225184943.png
  [3]: https://raw.githubusercontent.com/StarryPath/xss-utf7-png/master/QQ%E6%88%AA%E5%9B%BE20171225185235.png
  [4]: https://raw.githubusercontent.com/StarryPath/xss-utf7-png/master/QQ%E6%88%AA%E5%9B%BE20171225195222.png
  [5]: https://raw.githubusercontent.com/StarryPath/xss-utf7-png/master/QQ%E6%88%AA%E5%9B%BE20171225195706.png
  [6]: https://raw.githubusercontent.com/StarryPath/xss-utf7-png/master/QQ%E6%88%AA%E5%9B%BE20171225195735.png
  [7]: https://raw.githubusercontent.com/StarryPath/xss-utf7-png/master/QQ%E6%88%AA%E5%9B%BE20171227145146.png
  [8]: https://raw.githubusercontent.com/StarryPath/xss-utf7-png/master/QQ%E6%88%AA%E5%9B%BE20171227145215.png
  [9]: https://raw.githubusercontent.com/StarryPath/xss-utf7-png/master/QQ%E6%88%AA%E5%9B%BE20171227162050.png
 

 