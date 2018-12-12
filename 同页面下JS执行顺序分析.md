# 同页面下JS执行顺序分析



---

> 如果说，JavaScript引擎的工作机制比较深奥是因为它属于底层行为，那么JavaScript代码执行顺序就比较形象了，因为我们可以直观感觉到这种执行顺序，当然JavaScript代码的执行顺序是比较复杂的，所以在深入JavaScript语言之前也有必要对其进行剖析。

1、基本HTML文档
==========

HTML文档在浏览器中的解析过程是这样的：浏览器是按着文档流从上到下逐步解析页面结构和信息。JavaScript代码作为嵌入的脚本应该也算做HTML文档的组成部分，所以JavaScript代码在装载时的执行顺序也是根据脚本标签`<script>`的出现顺序来确定的。

    <script>
    
    alert("顶部脚本");
    
    </script>
    
    <html><head>
    
    <script>
    
    alert("头部脚本");
    
    </script>
    
    <title></title>
    
    </head>
    
    <body>
    
    <script>
    
    alert("页面脚本");
    
    </script>
    
    </body></html>
    
    <script>
    
    alert("底部脚本");
    
    </script>

如果通过脚本标签`<script>`的src属性导入外部JavaScript文件脚本，那么它也将按照其语句出现的顺序来执行，而且执行过程是文档装载的一部分。不会因为是外部JavaScript文件而延期执行。

    <script>
    
    alert("顶部脚本");
    
    </script>
    
    <html>
    
    <head>
    
    <script src="head.js"></script>
    
    <title></title>
    
    </head>
    
    <body>
    
    <script src="body.js"></script>
    
    </body>
    
    </html>
    
    <script>
    
    alert("底部脚本");
    
    </script>

2、预编译
=====

关于function：
String Array 都是系统内置对象（已经定义好，可以直接使用）当然，这货也是一样，我们之前定义的函数，其实就是一个这货的实例。

    function Hello() {
    
    alert("Hello");
    
    }
    
    Hello();
    
    var Hello = function() {
    
    alert("Hello");
    
    }
    
    Hello();
所以上面的两个写法作用相同。
当我们对其中的函数进行修改时，会发现很奇怪的问题。

    <script type="text/javascript">
    
            function Hello() {
    
                alert("Hello");
    
            }
    
            Hello();
    
            function Hello() {
    
                alert("Hello World");
    
            }
    
            Hello();
    
        </script>
        
上面的代码会连续输出两次Hello World。这是因为Javascript并非完全的按顺序解释执行，而是在解释之前会对Javascript进行一次“预编译”，在预编译的过程中，会把定义式的函数优先执行，也会把所有var变量创建，默认值为undefined，以提高程序的执行效率。也就是说上面的一段代码其实被JS引擎预编译为这样的形式：

    <script type="text/javascript">
    
            var Hello = function() {
    
                alert("Hello");
    
            }
    
            Hello = function() {
    
                alert("Hello World");
    
            }
    
            Hello();
    
            Hello();
    
        </script>
改变了代码的顺序。

3、按块执行
======

所谓代码块就是使用`<script>`标签分隔的代码段。例如，下面两个<script>标签分别代表两个JavaScript代码块。
 

    <script>
    
    // JavaScript代码块1
    
    var a =1;
    
    </script>
    
    <script>
    
    // JavaScript代码块2
    
    function f(){
    
        alert(1);
    
    }
    
    </script>
JavaScript解释器在执行脚本时，是按块来执行的。通俗地说，就是浏览器在解析HTML文档流时，如果遇到一个<script>标签，则JavaScript解释器会等到这个代码块都加载完后，先对代码块进行预编译，然后再执行。执行完毕后，浏览器会继续解析下面的HTML文档流，同时JavaScript解释器也准备好处理下一个代码块。

由于JavaScript是按块执行的，所以如果在一个JavaScript块中调用后面块中声明的变量或函数就会提示语法错误。虽然说，JavaScript是按块执行的，但是不同块都属于同一个全局作用域，也就是说，块之间的变量和函数是可以共享的。

4、事件机制
======

当JavaScript解释器执行下面代码时就会提示语法错误，显示变量a未定义，对象f找不到。

    <script>
    
    // JavaScript代码块1
    
    alert(a);
    
    f();
    
    </script>
    
    <script>
    
    // JavaScript代码块2
    
    var a =1;
    
    function f(){
    
        alert(1);
    
    }
    
    </script>
由于JavaScript是按块处理代码，同时又遵循HTML文档流的解析顺序，所以在上面示例中会看到这样的语法错误。但是当文档流加载完毕，如果再次访问就不会出现这样的错误。例如，把代码中的变量和函数的代码放在页面初始化事件函数中，就不会出现语法错误了。

    <script>
    
    // JavaScript代码块1
    
    window.onload = function(){        // 页面初始化事件处理函数
    
        alert(a);
    
        f();
    
    }
    
    </script>
    
    <script>
    
    // JavaScript代码块2
    
    var a =1;
    
    function f(){
    
        alert(1);
    
    }
    
    </script>
为了安全起见，我们一般在页面初始化完毕之后才允许JavaScript代码执行，这样可以避免网速对JavaScript执行的影响，同时也避开了HTML文档流对于JavaScript执行的限制。除了页面初始化事件外，我们还可以通过各种交互事件来改变JavaScript代码的执行顺序，如鼠标事件、键盘事件及时钟触发器等方法。

5、异步加载
======

通常来说，浏览器对于Javascript的运行有两大特性：1）载入后马上执行，2）执行时会阻塞页面后续的内容（包括页面的渲染、其它资源的下载）。于是，如果有多个js文件被引入，那么对于浏览器来说，这些js文件被被串行地载入，并依次执行。所以head里的 `<script>`标签会阻塞后续资源的载入以及整个页面的生成。这也就是为什么有很多网站把javascript放在网页的最后面了，要么就是动用了window.onload或是docmuemt ready之类的事件。然而我们希望页面加载和js载入可以同时进行，那么我们需要异步加载js：

5.1、document.write
-----------------

你可能以为document.write()这种方式能够解决不阻塞的方式，document.write了的`<script>`标签后就可以执行后面的东西去了，这没错。***对于在同一个script标签里的Javascript的代码来说，是这样的，但是对于整个页面来说，这个还是会阻塞。***

5.2、script的defer和async属性
------------------------

IE自从IE6就支持defer标签，对于IE来说，这个标签会让IE并行下载js文件，并且把其执行hold到了整个DOM装载完毕（DOMContentLoaded），多个defer的`<script>` 在执行时也会按照其出现的顺序来运行。最重要的是`<script>`被加上defer后，其不会阻塞后续DOM的的渲染。但是因为这个defer只是IE专用，所以一般用得比较少。
标准的的HTML5也加入了一个异步载入javascript的属性：async，无论你对它赋什么样的值，只要它出现，它就开始异步加载js文件。但是， async的异步加载会有一个比较严重的问题，那就是它忠实地践行着“载入后马上执行”这条军规，所以，虽然它并不阻塞页面的渲染，但是你也无法控制他执行的次序和时机，而且并不是所有的浏览器都支持。

5.3、动态创建DOM方式
-------------
这个方式几乎成了标准的异步载入js文件的方式，用 js 创建一个 script 元素并插入到 document 中。这样就做到了非阻塞的下载 js 代码。

    <script language="javascript" type="text/javascript">
        function loadjs(script_filename) {
            var script = document.createElement('script');
            script.setAttribute('type', 'text/javascript');
            script.setAttribute('src', script_filename);
            script.setAttribute('id', 'coolshell_script_id');
    
            script_id = document.getElementById('coolshell_script_id');
            if(script_id){
                document.getElementsByTagName('head')[0].removeChild(script_id);
            }
            document.getElementsByTagName('head')[0].appendChild(script);
        }
    
        var script = './alert.js';
        loadjs(script);
    </script>
    

5.4、按需异步载入js
------------
上面那个DOM方式的例子解决了异步载入Javascript的问题，但是没有解决我们想让他按我们指定的时机运行的问题。所以，我们只需要把上面那个DOM方式绑到某个事件上来就可以了。

比如：

绑在window.load事件上

    <script language="javascript" type="text/javascript">
        function loadjs(script_filename) {
            var script = document.createElement('script');
            script.setAttribute('type', 'text/javascript');
            script.setAttribute('src', script_filename);
            script.setAttribute('id', 'coolshell_script_id');
    
            script_id = document.getElementById('coolshell_script_id');
            if(script_id){
                document.getElementsByTagName('head')[0].removeChild(script_id);
            }
            document.getElementsByTagName('head')[0].appendChild(script);
        }
    
        function onPageLoad(){
            var script = './alert.js';
            loadjs(script);
        }
        window.onload = onPageLoad;
    
    </script>
    

6、识别正常JS
========

面对运营商劫持插入JS时，如何识别出哪些JS是原本网页中有的，哪些JS是运营商添加的，对后续采取相应措施极为必要。