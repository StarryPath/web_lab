# 内容安全策略--CSP（Content Security Policy）

标签（空格分隔）： Web安全

---

一、什么是CSP
========

这要从2007年说起，当时XSS攻击已经在OWASP TOP10攻击中排名第一位，CSP的最初的设想就在这一年被Mozilla项目组的Gervase Markham和WEB安全界大牛Robert Hansen ‘rsnake’两人共同提出的。
2011年 Firefox和Chrome相继推出自己的CSP标准。后来W3C起草语法完全不同的CSP1.0草案，Firefox和Chrome不久宣布全面支持W3C的CSP1.0标准。

CSP 的实质就是白名单制度，开发者明确告诉客户端，哪些外部资源可以加载和执行，等同于提供白名单。它的实现和执行全部由浏览器完成，开发者只需提供配置。
CSP 大大增强了网页的安全性。攻击者即使发现了漏洞，也没法注入脚本(这里指的是脚本不会执行)，除非还控制了一台列入了白名单的可信主机。


二、CSP的启用方式
==========

CSP的启用方式有两种：
第一种是通过 HTTP 头信息的Content-Security-Policy的字段：

    <?php header("Content-Security-Policy: script-src 'self' ;default-src 'self';");?>


    
第二种是通过网页的meta标签：

    <meta http-equiv="Content-Security-Policy" content="script-src 'self' ;default-src 'self';">

接下来看一下效果：
![此处输入图片的描述][1]
    

可以看到页面引用了两个外域的图片，抓一下这个网站发回的包：

![此处输入图片的描述][2]

发现并没有CSP的头部信息，说明网站未启用CSP。接下来我们启用CSP，就是用上面的代码

![此处输入图片的描述][3]

再抓一下返回包，可以看到已经有了CSP的头部信息。现在再来看一下网页，
![此处输入图片的描述][4]

引用的两个外域图片已经不显示了。上面的CSP语句的作用就是，所有的资源都只加载当前域的。

三、CSP的基础用法
==========

CSP的基础语法就是简单的键值对。我们首先来了解键的部分：

1 、资源加载限制
---------

    script-src：外部脚本
    style-src：样式表
    img-src：图像
    media-src：媒体文件（音频和视频）
    font-src：字体文件
    object-src：插件（比如 Flash）
    child-src：框架
    frame-ancestors：嵌入的外部资源（比如<frame>、<iframe>、<embed>和<applet>）
    connect-src：HTTP 连接（通过 XHR、WebSockets、EventSource等）
    worker-src：worker脚本
    manifest-src：manifest 文件

**default-src：用来设置上面各个选项的默认值。**

其中script-src和object-src必须设置（或使用default-src补全），若不设置，则对xss的防御将完全失效。

2、 URL 限制
---------

有时，网页会跟其他 URL 发生联系，这时也可以加以限制。

    frame-ancestors：限制嵌入框架的网页
    base-uri：限制<base#href>
    form-action：限制<form#action>

其中frame-ancestors可以防止点击劫持。
3、其他限制
------

    block-all-mixed-content：HTTPS 网页不得加载 HTTP 资源（浏览器已经默认开启）
    upgrade-insecure-requests：自动将网页上所有加载外部资源的 HTTP 链接换成 HTTPS 协议
    plugin-types：限制可以使用的插件格式
    sandbox：浏览器行为的限制，比如不能有弹出窗口等。

4、report-uri
------------

    report-uri：告诉浏览器，应该把注入行为报告给哪个网址。
    
    Content-Security-Policy: default-src 'self'; report-uri 'http://reportcollector.example.com/collector.cgi'

接下来说键值对中值的部分：

5、选项值
---

    主机名：example.org，https://example.com:443
    路径名：example.org/resources/js/
    通配符：*.example.org，*://*.example.com:*（表示任意协议、任意子域名、任意端口）
    协议名：https:、data:
    关键字'self'：当前域名，需要加引号
    关键字'none'：禁止加载任何外部资源，需要加引号
    
script-src的源列表不能允许data：URI，如：

    Content-Security-Policy: script-src data: ;default-src 'self';
否则会导致伪协议xss使防护完全失效。
payload：`<script src="data:text/javascript,alert(1)"></script>`

6、script-src 的特殊值
-----------------
除了常规值，script-src还可以设置一些特殊值。注意，下面这些值都必须放在单引号里面。

    unsafe-inline：允许执行页面内嵌的<script>标签和事件监听函数
    unsafe-eval：允许将字符串当作代码执行，比如使用eval、setTimeout、setInterval和Function等函数。
    nonce值：每次HTTP回应给出一个授权token，页面内嵌脚本必须有这个token，才会执行
    hash值：列出允许执行的脚本代码的Hash值，页面内嵌脚本的哈希值只有吻合的情况下，才能执行。
    
unsafe-inline和unsafe-eval的设置一定要慎重，很多网站不能避免的要使用页面内嵌的`<script>`标签或者eval等函数，此时一定要设置nonce值。在nonce中，应用程序定义并生成了单一的，不可猜测的令牌（nonce），这个令牌会同时传递给CSP策略和作为一个合法HTML属性传递给script。 用户代理仅允许执行那些nonce值能够匹配策略中指定的值的脚本。虽然攻击者可以将标记注入易受攻击的页面，但是由于不知道nonce的临时值，因此他并不能执行恶意脚本。具体使用方式如下：

    Content-Security-Policy: script-src 'nonce-random123' ;default-src 'self';
    
    <script nonce="random123">alert('nonce')</script>
实际使用中为了确保安全，nonce 的值都会经过 base64 编码，并且编码前不少于 128 位，每次请求序号都会改变。总之一个原则：不能让攻击者猜到它的内容。

7、总结
----

这些键值对就构成了白名单，多个值可以并列，用空格分隔，但是如果有多个相同的键，则只有第一个生效，如果不设置某个限制选项，就是默认允许任何值。

> 通过这些限制能力我们可以看出，目前的CSP提供了对三种类型漏洞的保护功能：
> 
> XSS：XSS攻击能在一个脆弱的应用程序中注入并执行不受信任的脚本（用script-src和object-src指令来进行保护）
> 
> Clickjacking：Clickjacking通过在攻击者控制的页面上覆盖隐藏的框架来迫使用户在受影响的应用程序中执行不想要的操作。（通过限制框架嵌入和
> frame-ancestors指令来保护）
> 
> Mixedcontent：Mixedcontent意味着在通过用HTTPS传递的页面上使用不安全协议加载资源（使用upgrade-insecure-requests和blockall-mixed-content关键字进行保护，限制将脚本和敏感资源加载到https网页中)

四、CSP的绕过
========

在上面我们已经提到了三种设置不当导致防御完全失效的情况：

1、 策略必须同时定义script-src和object-src（或者使用default-src来补全）
----------------------------------------------------
包括script标签和object标签的xss。

2、script-src的源列表不能包含unsafe-inline关键词（除非使用nonce）或者允许data: URI
------------------------------------------------------------
包括`<img src="x" onerror="alert(1)">`和伪协议等xss。

3、script-src和object-src源列表不能包含含有攻击者可控制response的安全相关部分的源地址，或包含不安全的库。
-------------------------------------------------------------------

> 由于CSP的基本假设之一就是在策略白名单中的域名只会提供安全的内容，因此从理论上来说攻击者不应该能够将有效的JavaScript注入到白名单里来源的响应中。然而在实践中，我们发现现代web应用程序往往会因为几种模式违反这个假设。

1、JSONP函数名注入
当网站开发者暴露了JSONP接口的域名时可以利用这种方法。
未做严格的过滤，可能注入任意函数名，例如利用UTF7编码就可以完成xss攻击。

2、AngularJS等库
当网站开发者在白名单中加入托管了AngularJS的域名后，就可以利用这种方法。
默认情况下AngularJS会通过eval来执行代码，在特定的不允许eval的CSP场景下，AngularJS也支持使用“CSP compatibility mode” (ng-csp)来执行模板的代码。攻击者只需要从白名单中的域名里引入AngularJS，就可以在页面上通过注入ng-app标签来编写能够执行任意JavaScript代码的模板。

3、Unexpected JavaScript-parseable responses（意想不到的JavaScript解析的响应）
由于兼容性的原因，Web浏览器通常很容易检查响应的MIME类型是否与使用响应的页面上下文匹配。任何可以解析为js而没有出现语法错误，并且在第一个运行时错误出现的攻击者控制的数据响应可以导致脚本执行。因此，CSP可以被下面的方式绕过：

    部分可以被攻击者控制的内容的逗号分隔（csv）数据：
    
    1
    2
    Name,Value
    alert(1),234
    回显请求参数的错误消息
    
    1
    Error: alert(1)// not found.

因此，如果白名单的源列表中具有此类属性的任何页面，攻击者可以伪造脚本响应并执行js。类似的问题也适用于object-src白名单：如果攻击者可以将一个被解释为flash对象的资源上传到针对object-src的白名单域中，则可以执行脚本。

4、 Path restrictions as a security mechanism（作为安全机制的路径限制）
CSP2引入了可以将白名单限制到域的特定路径的能力（类似于example.org/foo/bar），开发者可以选择在可信域上指定特定的目录来加载脚本和其他资源。
不幸的是，由于处理跨源重定向的问题，这种限制被放宽了。如果白名单源列表目录中包含重定向器，则这个重定向器可以在不被允许的目录加载资源。最常见于OAuth中，或被用于防止referer丢失。

5、策略优先级绕过
在浏览器的保护策略中，有很多是重复的。比如A策略可以抵御C攻击，B策略也可以抵御C攻击。此处的抵御可以是阻断也可以是放行。于是当AB同时作用于C攻击上时，Bypass就可能发生。
（1）Iframe sandbox 和 CSP sandbox
当iframe sandbox允许执行JS，而CSP不允许执行JS，问题就发生了，CSP就被bypass了。

（2）XSS Auditor和CSP
关于XSS Auditor和CSP，这里我想进行一次更开放式的讨论。以Chrome中测试为例，当XSS Auditor和CSP同时作用到一段JS代码上，会有怎样一个效果呢。比如XSS Auditor设置的是阻断，CSP里设置unsafe-inline放行，结果还是被阻断。这是由于浏览器解析JS脚本的时候先使用了XSS auditor这层安全防御策略，所以CSP中的unsafe-inline这个指令并没有起作用，从广义的角度来看，CSP中的策略被Bypass了。浏览器的策略中，类似与这样的情况还有很多。比如下面介绍的这个。

（3） X-Frame-Options和CSP frame
X-Frame-Options HTTP 响应头是用来给浏览器指示允许一个页面可否在 `<frame>`, `<iframe>` 或者 `<object>` 中展现的标记。网站可以使用此功能，来确保自己网站的内容没有被嵌到别人的网站中去，也从而避免了点击劫持 (clickjacking) 的攻击。
当a.com设置X-Frame-Options:deny，b.com设置CSP frame-src a.com，那么b.com是否可以iframe a.com呢。测试中发现a.com还是不能被b.com包含的。你可以认为浏览器解析中，X-Frame-Options优先级大于CSP frame。

五、CSP总结
=======

> 充分了解CSP安全策略的语法和指令，并最大程度的合理的去利用和部署这些策略，努力把安全策略发挥到极致，使其最终把危害降低到最低。
> CSP并不能消除内容注入攻击，但可以有效的检测并缓解跨站攻击和内容注入攻击带来的危害。
> CSP不是做为防御内容注入(如XSS)的第一道防线而设计，而最适合部署在纵深防御体系中。
> 关于为什么CSP的使用率如此之低。究其原因，CSP虽然提供了强大的安全保护，但是他也造成了如下问题：Eval及相关函数被禁用、内嵌的JavaScript代码将不会执行、只能通过白名单来加载远程脚本。这些问题阻碍CSP的普及，如果要使用CSP技术保护自己的网站，开发者就不得不花费大量时间分离内联的JavaScript代码和做一些调整。
> 没有被绕过的策略不是好的策略，而从辩证角度来讲，多加载一种安全策略，就多了一种Bypass的维度。在安全领域“Bypass”始终是一个曼妙而鬼魅的名字。
> 应该把CSP安全策略视为是一把可以直插心脏的锋利的尖刀，而不是一根电线杆子杵在那。








 


  [1]: https://raw.githubusercontent.com/StarryPath/xss-csp-png/master/csp2.png
  [2]: https://raw.githubusercontent.com/StarryPath/xss-csp-png/master/csp3.png
  [3]: https://raw.githubusercontent.com/StarryPath/xss-csp-png/master/csp4.png
  [4]: https://raw.githubusercontent.com/StarryPath/xss-csp-png/master/csp5.png
