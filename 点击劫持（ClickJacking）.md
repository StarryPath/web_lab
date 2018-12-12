# 点击劫持（ClickJacking）

标签（空格分隔）： Web安全

---

一、什么是点击劫持
---------

点击劫持是一种视觉上的欺骗手段。攻击者使用一种透明的、不可见的iframe，覆盖在一个网页上，然后诱使用户在该网页上进行操作，此时用户将在不知情的情况下点击透明的iframe页面。通过调整iframe页面的位置，可以诱使用户恰好点击在iframe页面的一些功能性按钮上，攻击者常常配合社工手段完成攻击。

看下面这个例子：
在页面中插入了一个指向目标网站的iframe，出于演示的目的，我们让这个iframe变成半透明：

    <html>
    <head>
    <title>Click Jack!</title>
    <style>
    iframe{
    width:350px;
    height:380px;
    
    position:absolute;
    top:-330px;
    left:-180px;
    z-index:2;
    
    -moz-opacity:0.5;
    opacity:0.5;
    filter:alpha(opacity=0.5);
    }
    
    button {
    
    position:absolute;
    top:10px;
    left:10px;
    z-index:1;
    width:120px;
    }
    </style>
    </head>
    <body>
    <iframe src="https://book.qidian.com/info/1010468795" scrolling="no"></iframe>
    <button>Click here!</button>
    </body>
    </html>


在页面中有一个button，如果iframe全透明时，用户看到的是：
![此处输入图片的描述][1]


当iframe半透明时，可以看到，在button上其实覆盖了另一个网页:
![此处输入图片的描述][2]

覆盖的网页是一个免费试读按钮：
![此处输入图片的描述][3]

当用户点击按钮时也就增加了小说的热度和点击量。

分析代码，起到关键作用的是这几行：

    iframe{
    width:350px;
    height:380px;
    
    position:absolute;
    top:-330px;
    left:-180px;
    z-index:2;
    
    -moz-opacity:0.5;
    opacity:0.5;
    filter:alpha(opacity=0.5);
    }

通过控制iframe的长和宽，以及调整top left的位置，可以把iframe页面内的任意部分覆盖到任何地方。同时设置iframe的position为absolute（绝对定位），并将z-index（z-index 属性设置元素的堆叠顺序。拥有更高堆叠顺序的元素总是会处于堆叠顺序较低的元素的前面。）的值调到最大，以达到让iframe位于页面的最上层。最后通过设置opacity来控制页面的透明度，值为0是完全不可见。

二、flash点击劫持
-----------

下面来看一个更为严重的Clickjacking攻击案例。攻击者通过Flash构造出了点击劫持，在完成一系列复杂的动作后，最终控制了用户电脑的摄像头。 目前Adobe公司已经在Flash中修补了此漏洞。攻击过程如下：首先，攻击者制作了一个Flash游戏，并诱使用户来玩这个游戏。这个游戏就是让用户去点击“CLICK”按钮，每次点击后这个按钮的位置都会发生变化。

在其上隐藏了一个看不见的iframe：
![此处输入图片的描述][4]
游戏中的某些点击是有意义的，某些点击是无效的。攻击通过诱导用户鼠标点击的位置，能够完成一些较为复杂的流程。
![此处输入图片的描述][5]
![此处输入图片的描述][6]
最终通过这一步步的操作，打开了用户的摄像头。
![此处输入图片的描述][7]

三、拖拽劫持与数据窃取
-----------


目前很多浏览器都开始支持Drag & Drop 的API。对于用户来说，拖拽使他们的操作更加简单。浏览器中的拖拽对象可以是一个链接，也可以是一段文字，还可以从一个窗口拖拽到另外一个窗口，因此拖拽是不受同源策略限制的。
"拖拽劫持"的思路是诱使用户从隐藏的不可见iframe中"拖拽"出攻击者希望得到的数据，然后放到攻击者能控制的另外一个页面中，从而窃取数据。
在JavaScript或者Java API的支持下，这个攻击过程会变得非常隐蔽。因为它突破了传统ClickJacking一些先天的局限，所以这种新型的"拖拽劫持"能够造成更大的破坏。

国内的安全研究者 xisigr 曾经构造了一个针对 Gmail 的 POC，其过程大致如下：
首先，制作一个网页小游戏，要把小球拖拽到小海豹的头顶上。
![此处输入图片的描述][8]
实际上，在小球和小海豹的头顶上都有隐藏的 iframe。
![此处输入图片的描述][9]
在这个例子中，xisigr 使用 event.dataTransfer.getData('Text') 来获取“drag”到的数据（此方法接受指定类型的拖放（以DOMString的形式）数据。如果拖放行为没有操作任何数据，会返回一个空字符串。）。当
用户拖拽小球时，实际上是选中了隐藏的 iframe 里的数据；在放下小球时，把数据也放在了隐
藏的 textarea 中，从而完成一次数据窃取的过程。


四、XSS劫持
-------

XSS劫持（XSSJacking）是由Dylan Ayrey所提出的一种新型XSS攻击，可窃取受害者的敏感信息。XSS劫持需要其他三种技术配合使用，分别是点击劫持，粘贴劫持以及Self-XSS，甚至还需要一些社会工程学的帮助。
要构成一个XSS劫持攻击有以下几种必要条件：

    1.目标网站必须有点击劫持漏洞

    2.Self-XSS

    3.粘贴劫持

Self-XSS(自跨站脚本攻击)是一种由受害者自己输入XSS payload触发才能成功的XSS攻击行为，就是我们常说的自插。再加上复制粘贴，我们现在就可以想一想漏洞利用的思路了，先来看一个例子：
这是一个有self-xss和点击劫持漏洞的网站：
![此处输入图片的描述][10]
当用户输入xsscode可以执行，但是xss自己打自己没有用处，攻击者就想到了使用点击劫持，但是怎样让用户输入payload呢？攻击者建了这样的网站：
![此处输入图片的描述][11]
当用户注册时会让用户输入邮箱，输入完之后会让用户重新输入。一般来说邮箱的长度在10位以上，很多人就会复制上面输入过的，再粘贴到下面。如果用户真的粘贴了，那么攻击者也就达到目的了：
![此处输入图片的描述][12]

可以看到在粘贴之后已经完成xss攻击。我们来看一下网站源码：

    <html>
        <head>
        </head>
        <body>
            Enter your email below to register:
            </br>
            <textarea autofocus style="width:220px; height:35px;"></textarea>
            </br>
            Repeat your email:
            </br>
            <iframe style="width:230px; height:50px;" frameBorder="0" src="index.html"></iframe>
            </br>
            <input type="submit"></input>
            <script>
                document.addEventListener('copy', function(e){
                    console.log(e);
                    e.clipboardData.setData('text/plain', '\x3cscript\x3ealert(1)\x3c/script\x3e');
                    e.preventDefault(); // We want our data, not data from any selection, to be written to the clipboard
                });
            </script>
        </body>
    </html>

可以看到`<iframe style="width:230px; height:50px;" frameBorder="0" src="index.html"></iframe>`将存在点击劫持和self-xss漏洞的网站插到了攻击者的网站中。

    <script>
                document.addEventListener('copy', function(e){
                    console.log(e);
                    e.clipboardData.setData('text/plain', '\x3cscript\x3ealert(1)\x3c/script\x3e');
                    e.preventDefault(); // We want our data, not data from any selection, to be written to the clipboard
                });
    </script>
这段代码的作用是当用户存在复制操作时，就将xsscode存入到用户的剪贴板中，这样当用户粘贴时，就可以将payload粘贴到存在self-xss的3网站中，完成攻击。

五、鼠标劫持
------
这个通常和点击劫持差不多，但重心在于鼠标的光标上。

鼠标劫持最经典的一个案例就是通过自定义光标图像欺骗用户，显示的光标改变了鼠标实际的位置。举个例子：
![此处输入图片的描述][13]

攻击者将光标图片替换成自定义图片，此外，事件监听器附加在页面监听mousemove事件。当用户鼠标移动，事件触发器产生一个假的光标进行相应的移动。单击Yes按钮，结果点击到eval按钮。

六、JS防御绕过
----

如if (top.location != self.location) {top.location=self.location;} 

    <iframe src="xxx" security="restricted" scrolling="no" sandbox=""> 

security="restricted"  为IE的禁止JS

sandbox=""  为HTML5的禁止JS

这样就达到了bypass的效果

七、防御方法
------
由于使用js防护可能被各种方法绕过，所以X-FRAME-OPTIONS是目前最可靠的方法。

X-FRAME-OPTIONS是微软提出的一个http头，专门用来防御利用iframe嵌套的点击劫持攻击。

并且在IE8、Firefox3.6、Chrome4以上的版本均能很好的支持。

这个头有三个值：

    DENY // 拒绝任何域加载
    SAMEORIGIN // 允许同源域下加载
    ALLOW-FROM // 可以定义允许frame加载的页面地址

php中设置：

    header("X-FRAME-OPTIONS:DENY");
Apache配置：

    Header always append X-Frame-Options SAMEORIGIN


  [1]: https://raw.githubusercontent.com/StarryPath/iframe-png/master/QQ%E6%88%AA%E5%9B%BE20171231145113.png
  [2]: https://raw.githubusercontent.com/StarryPath/iframe-png/master/QQ%E6%88%AA%E5%9B%BE20171231145448.png
  [3]: https://raw.githubusercontent.com/StarryPath/iframe-png/master/QQ%E6%88%AA%E5%9B%BE20171231145708.png
  [4]: https://raw.githubusercontent.com/StarryPath/iframe-png/master/201608261472195714792392.png
  [5]: https://raw.githubusercontent.com/StarryPath/iframe-png/master/201608261472195765627779.png
  [6]: https://raw.githubusercontent.com/StarryPath/iframe-png/master/201608261472195746923033.png
  [7]: https://raw.githubusercontent.com/StarryPath/iframe-png/master/201608261472195789919141.png
  [8]: https://raw.githubusercontent.com/StarryPath/iframe-png/master/QQ%E6%88%AA%E5%9B%BE20171231214444.png
  [9]: https://raw.githubusercontent.com/StarryPath/iframe-png/master/QQ%E6%88%AA%E5%9B%BE20171231214551.png
  [10]: https://raw.githubusercontent.com/StarryPath/iframe-png/master/QQ%E6%88%AA%E5%9B%BE20171231164950.png
  [11]: https://raw.githubusercontent.com/StarryPath/iframe-png/master/QQ%E6%88%AA%E5%9B%BE20171231165449.png
  [12]: https://raw.githubusercontent.com/StarryPath/iframe-png/master/QQ%E6%88%AA%E5%9B%BE20171231165917.png
  [13]: https://raw.githubusercontent.com/StarryPath/iframe-png/master/QQ%E6%88%AA%E5%9B%BE20171231173027.png