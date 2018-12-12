# token健壮性分析之伪随机数研究


---

> 真随机数：产生的数不可预计，也不可能重复产生两个相同的真随机数序列。真随机数只能通过某些随机的物理过程来产生，如放射性衰变、电子设备的热噪声等。

> 伪随机数：通过某种数学公式或者算法产生的数值序列。虽然在数学意义上伪随机数是不随机的，但是如果能够通过统计检验，可以当成真随机数使用。

在计算机上可以用物理方法来产生随机数，但价格昂贵，不能重复，使用不便。另一种方法是用数学递推公式产生，这样产生的序列与真正的随机数序列不同，所以称为伪随机数或伪随机序列，只要方法和参数选择合适，所产生的伪随机数就能满足均匀性和独立性，与真正的随机数具有相近的性质。

1、平方取中法
-------
平方取中法(midsquare method)是产生[0，1]均匀分布随机数的方法之一，亦称冯·诺伊曼取中法，最早由冯·诺伊曼(John von Neumann，1903-1957)提出的一种产生均匀伪随机数的方法。此法将一个2s位十进制随机数平方后得到的一个4s位数，去头截尾取中间2s位数作为一个新的随机数，重复上述过程可得到一个伪随机数列。即：

x(i+1)=(10^(-m/2)*x(i)*x(i))mod(10^m)

平方取中法计算较快，但在实际应用时会发现该方法容易产生周期性明显的数列，而且在某些情况下计算到一定步骤后始终产生相同的数甚至是零，或者产生的数字位数越来越小直至始终产生零。所以用平方取中法产生伪随机数数列时不能单纯使用公式，应该在计算过程中认为加入更多变化因素，比如根据前一个数的奇偶性进行不同的运算，如果产生的数字位数减少时通过另一种运算使其恢复成m位。
代码为：


    long intlen(long in)        //整数in的长度
    {
        long count=0;
        while(in!=0)
        {
            in/=10;
            count++;
        }
        return count;
    }
    long power_10(long in)      //10的in次幂
    {
        long i,out=1;
        for(i=0;i<in;i++)
            out*=10;
        return out;
    }
    long rand_pfqz(void)      //平方取中
    {
        long len;
        while(seed<10000)			//保持数位一致
            seed=seed*13+seed/10+second/3;
        len=intlen(seed);
        long temp=seed;
        temp=((seed*seed/power_10(len/2))%power_10(len));
        if(temp%2==0)   temp+=second/3+7854;		//增加改变因素，
        else    temp+=second*second/2317;			//以延长计算周期
        seed=temp;
        return (unsigned long)(seed%10000*LENGTH)/10000;
    }

2、线性同余法（LCG）
-------

这是最常用的一种生成伪随机数的方法，容易理解，容易实现，而且速度快。给定首项，通过定义好的递推公式得到伪随机序列。

递推公式：X[i+1]=(A*X[i]+C) mod M



> 经前人研究表明，在M=2^q的条件下，参数A，C，X[0]按如下选取，周期较大，概率统计特性好:
> 
> A=2^b+1=2^(log2(M)/2)+1=2^log2(sqrt(M))+1=sqrt(M)+1；b取q/2 附近的数
> 
> C=(1/2+sqrt(3))*M
> 
> X[0]为任意非负数

以下是一个使用了线性同余的递推公式： 
Xt = (X0 * 17 + 29) mod 500

线性同余中的线性，是指“线性”表示方程中 x 的次数是一次，mod 取余运算符则体现了“同余”这一数学概念。

式中，17 、29和500分别称做乘数、增量和模数。使用线性同余生成随机数的方法速度快，但对乘数、增量和模数的选取有一定的要求：

多次使用线性同余公式产生的序列应该看起来是随机的，不循环的；
乘数/增量与模数互质；
这个函数能够产生一个完整周期内的所有随机数。这一要求由模数控制。

    <?php 
      function returnXianXinTongYu($count){
        $a = 9;
        $b = 7;
        $m = 1 << 31;
       
        $value = [];
        $value[0] =time();
        //$value[0] =100;
    
    
        for ($i = 1; $i < $count; $i++) {
            $value[$i] = (($a * $value[$i - 1] + $b) % $m);
        }
        
        foreach($value as $t=>$v){
            $value[$t] = sprintf('%.6f',$v / $m);
        }
    
    
        sort($value);
        return $value;
    }
    print_r(returnXianXinTongYu(10));
    
可以通过取系统时间让每次生成的序列不同，通过取很大的m值和精心选择ab值来让序列趋向于真随机。

3、梅森旋转（Mersenne Twister/MT）
---------------------------
简介：
> 梅森旋转算法可以产生高质量的伪随机数，并且效率高效，弥补了传统伪随机数生成器的不足。梅森旋转算法的最长周期取自一个梅森素数，由此命名为梅森旋转算法。

应用：
> 梅森旋转算法是R、Python、Ruby、IDL、Free Pascal、PHP、Maple、Matlab、GNU多重精度运算库和GSL的默认伪随机数产生器。从C++11开始，C++也可以使用这种算法。在Boost C++,Glib和NAG数值库中，作为插件提供。 
> 
> 在SPSS中，梅森旋转算法是两个PRNG中的一个：另一个是产生器仅仅为保证旧程序的兼容性，
> 梅森旋转被描述为“更加可靠”。梅森旋转在SAS中同样是PRNG中的一个，另一个产生器是旧时的且已经被弃用。

优点：

> 许可免费，而且对所有它的变体专利免费（除CryptMT外）
> 几乎无处不在：它被包含在大多数编程语言和库中
> 通过了包括Diehard测试在内的大多数统计随机性测试（除TestU01测试外）
> 在应用最广泛的MT19937变体中，周期长达2^19937-1 
> 在MT19937-32的情况下对1 ≤ k ≤ 623，满足k-分布
> 比其他大多数随机数发生算法要快

k-分布:
一个周期为P的w位整数的随机序列xi，当满足如下条件时被称为满足v位的k-分布：

> 假设truncv(x)表示x的前v位形成的数字，并且长度为P的kv位序列： ![此处输入图片的描述][1]
> 其中每个可能出现的2^kv组合在一个周期内出现相同的次数（除全0序列出现次数次数比其他序列少1次）

算法详细：
本算法基于标准（线性）旋转反馈移位寄存器（twisted generalised feedback shift register/TGFSR）产生随机数
算法中用到的变量如下所示：

> w：长度（以bit为单位）
> n：递归长度
> m：周期参数，用作第三阶段的偏移量
> r：低位掩码/低位要提取的位数
> a：旋转矩阵的参数
> f：初始化梅森旋转链所需参数
> b,c：TGFSR的掩码
> s,t：TGFSR的位移量
> u,d,l：额外梅森旋转所需的掩码和位移量
> 
> 
> MT19937-32的参数列表如下：
> (w, n, m, r) = (32, 624, 397, 31)
> a = 9908B0DF（16）
> f = 1812433253
> (u, d) = (11, FFFFFFFF16)
> (s, b) = (7, 9D2C568016)
> (t, c) = (15, EFC6000016)
> l = 18
> 
> MT19937-64的参数列表如下：
> (w, n, m, r) = (64, 312, 156, 31)
> a = B5026F5AA96619E9（16）
> f = 6364136223846793005
> (u, d) = (29, 555555555555555516)
> (s, b) = (17, 71D67FFFEDA6000016)
> (t, c) = (37, FFF7EEE00000000016)
> l = 43

![此处输入图片的描述][2]

整个算法分为三个阶段（如图所示）：

> 第一阶段：初始化，获得基础的梅森旋转链；
第二阶段：对于旋转链进行旋转算法； 
第三阶段：对于旋转算法所得的结果进行处理；

初始化:

> 首先将传入的seed赋给MT[0]作为初值，然后根据递推式：MT[i] = f × (MT[i-1] ⊕ (MT[i-1] >>
> (w-2))) + i递推求出梅森旋转链。伪代码如下：

     // 由一个seed初始化随机数产生器
     function seed_mt(int seed) {
         index := n
         MT[0] := seed
         for i from 1 to (n - 1) {
             MT[i] := lowest w bits of (f * (MT[i-1] xor (MT[i-1] >> (w-2))) + i)
         }
     }

对旋转链执行旋转算法:

> 遍历旋转链，对每个MT[i]，根据递推式：MT[i] = MT[i+m]⊕((upper_mask(MT[i]) ||
> lower_mask(MT[i+1]))A）进行旋转链处理。
> 
> 其中，“||”代表连接的意思，即组合MT[i]的高 w-r 位和MT[i+1]的低 r 位，设组合后的数字为x，在 MT 中，A被定义为

![此处输入图片的描述][3]

> 则xA的运算规则为（x0是最低位）：

 
![此处输入图片的描述][4]

伪代码为:

    lower_mask = (1 << r) - 1
    upper_mask = ！lower_mask
     // 旋转算法处理旋转链 
     function twist() {
         for i from 0 to (n-1) {
             int x := (MT[i] & upper_mask)+ (MT[(i+1) mod n] & lower_mask)
             int xA := x >> 1
             if (x mod 2) != 0 { 
             // 最低位是1
                 xA := xA xor a
             }
             MT[i] := MT[(i + m) mod n] xor xA
         }
         index := 0
    }

对旋转算法所得结果进行处理:

> 设x是当前序列的下一个值，y是一个临时中间变量，z是算法的返回值。则处理过程如下： 
y := x ⊕ ((x >> u) & d) 
y := y ⊕ ((y << s) & b) 
y := y ⊕ ((y << t) & c) 
z := y ⊕ (y >> l) 伪代码如下：

    // 从MT[index]中提取出一个经过处理的值
    // 每输出n个数字要执行一次旋转算法，以保证随机性
     function extract_number() {
         if index >= n {
             if index > n {
               error "发生器尚未初始化"
             }
             twist()
         }
    
         int x := MT[index]
         y := x xor ((x >> u) and d)
         y := y xor ((y << s) and b)
         y := y xor ((y << t) and c)
         z := y xor (y >> l)
    
         index := index + 1
         return lowest w bits of (z)
     }
     
MT-19937-32实现代码（C语言版）:

    #include <stdint.h>
    #include <stdio.h>
    #include <stdlib.h>
    // 定义MT19937-32的常数
    enum
    {
        // 假定 W = 32 (此项省略)
        N = 624,
        M = 397,
        R = 31,
        A = 0x9908B0DF,
    
        F = 1812433253,
    
        U = 11,
        // 假定 D = 0xFFFFFFFF (此项省略)
    
        S = 7,
        B = 0x9D2C5680,
    
        T = 15,
        C = 0xEFC60000,
    
        L = 18,
    
        MASK_LOWER = (1ull << R) - 1,
        MASK_UPPER = (1ull << R)
    };
    
    static uint32_t  mt[N];
    static uint16_t  index;
    
    // 根据给定的seed初始化旋转链
    void Initialize(const uint32_t  seed)
    {
        uint32_t  i;
        mt[0] = seed;
        for ( i = 1; i < N; i++ )
        {
            mt[i] = (F * (mt[i - 1] ^ (mt[i - 1] >> 30)) + i);
        }
        index = N;
    }
    
    static void Twist()
    {
        uint32_t  i, x, xA;
        for ( i = 0; i < N; i++ )
        {
            x = (mt[i] & MASK_UPPER) + (mt[(i + 1) % N] & MASK_LOWER);
            xA = x >> 1;
            if ( x & 0x1 )
            {
                xA ^= A;
            }
            mt[i] = mt[(i + M) % N] ^ xA;
        }
    
        index = 0;
    }
    
    // 产生一个32位随机数
    void ExtractU32()
    {
        uint32_t  y;
        int       i = index;
        if ( index >= N )
        {
            Twist();
            i = index;
        }
        y = mt[i];
        index = i + 1;
        y ^= (y >> U);
        y ^= (y << S) & B;
        y ^= (y << T) & C;
        y ^= (y >> L);
        printf("%u\n",y);
        //return y;
    }
    int main()
    {
        Initialize(F);
        Twist();
        int i=0;
        for(i=0;i<20;i++)
        ExtractU32();
    
        return 0;
    }

![此处输入图片的描述][5]


  [1]: https://raw.githubusercontent.com/StarryPath/mt-png/master/20171128182836316.png
  [2]: https://raw.githubusercontent.com/StarryPath/mt-png/master/20171128183440350.png
  [3]: https://raw.githubusercontent.com/StarryPath/mt-png/master/QQ%E6%88%AA%E5%9B%BE20180902194539.png
  [4]: https://raw.githubusercontent.com/StarryPath/mt-png/master/QQ%E6%88%AA%E5%9B%BE20180902194711.png
  [5]: https://raw.githubusercontent.com/StarryPath/mt-png/master/QQ%E6%88%AA%E5%9B%BE20180902201837.png




