[https://raw.githubusercontent.com/StarryPath/xxe-png/master/%E5%94%AF%E4%B8%80%E5%85%A5%E5%8F%A3%EF%BC%9Aindex.php.png](https://raw.githubusercontent.com/StarryPath/xxe-png/master/%25E5%2594%25AF%25E4%25B8%2580%25E5%2585%25A5%25E5%258F%25A3%25EF%25BC%259Aindex.php.png)Poscms过滤函数



---

poscms在传参时会直接调用CI框架中input类的post，get等方法，这些方法已经封装好了，在方法中会调用security中的xss过滤方法：xss_clean()

    protected $_never_allowed_str =	array(
    		'document.cookie' => '[removed]',
    		'document.write'  => '[removed]',
    		'.parentNode'     => '[removed]',
    		'.innerHTML'      => '[removed]',
    		'-moz-binding'    => '[removed]',
    		'<!--'            => '&lt;!--',
    		'-->'             => '--&gt;',
    		'<![CDATA['       => '&lt;![CDATA[',
    		'<comment>'	  => '&lt;comment&gt;',
    		'<%'              => '&lt;&#37;'
    	);
    
    	/**
    	 * List of never allowed regex replacements
    	 *
    	 * @var	array
    	 */
    	protected $_never_allowed_regex = array(
    		'javascript\s*:',
    		'(document|(document\.)?window)\.(location|on\w*)',
    		'expression\s*(\(|&\#40;)', // CSS and IE
    		'vbscript\s*:', // IE, surprise!
    		'wscript\s*:', // IE
    		'jscript\s*:', // IE
    		'vbs\s*:', // IE
    		'Redirect\s+30\d',
    		"([\"'])?data\s*:[^\\1]*?base64[^\\1]*?,[^\\1]*?\\1?"
    	);

        public function xss_clean($str, $is_image = FALSE)  
        {  
            if (is_array($str)) {  
                while (list($key) = each($str)) {  
                    $str[$key] = $this->xss_clean($str[$key]);  
                }  
                return $str;  
            }  
            //删除不可见字符  
            $str = remove_invisible_characters($str);  
            //对url字符串进行编码  
            do {  
                $str = rawurldecode($str);  
            } while (preg_match('/%[0-9a-f]{2,}/i', $str));  
            //转换为ASCII字符实体  
            $str = preg_replace_callback("/[^a-z0-9>]+[a-z0-9]+=([\'\"]).*?\\1/si", array($this, '_convert_attribute'), $str);  
            $str = preg_replace_callback('/<\w+.*/si', array($this, '_decode_entity'), $str);  
            //再次删除不可见字符！  
            $str = remove_invisible_characters($str);  
            //将所有标签转换为空格  
            $str = str_replace("\t", ' ', $str);  
            //捕获转换后的字符串  
            $converted_string = $str;  
            //删除不允许的字符串  
            $str = $this->_do_never_allowed($str);  
      
              
            if ($is_image === TRUE) {  
                  
                $str = preg_replace('/<\?(php)/i', '<?\\1', $str);  
            } else {  
                $str = str_replace(array('<?', '?' . '>'), array('<?', '?>'), $str);  
            }  
            $words = array(  
                'javascript', 'expression', 'vbscript', 'jscript', 'wscript',  
                'vbs', 'script', 'base64', 'applet', 'alert', 'document',  
                'write', 'cookie', 'window', 'confirm', 'prompt', 'eval'  
            );  
            foreach ($words as $word) {  
                $word = implode('\s*', str_split($word)) . '\s*';  
                $str = preg_replace_callback('#(' . substr($word, 0, -3) . ')(\W)#is', array($this, '_compact_exploded_words'), $str);  
            }  
            do {  
                $original = $str;  
                if (preg_match('/<a/i', $str)) {  
                    $str = preg_replace_callback('#<a[^a-z0-9>]+([^>]*?)(?:>|$)#si', array($this, '_js_link_removal'), $str);  
                }  
                if (preg_match('/<img/i', $str)) {  
                    $str = preg_replace_callback('#<img[^a-z0-9]+([^>]*?)(?:\s?/?>|$)#si', array($this, '_js_img_removal'), $str);  
                }  
                if (preg_match('/script|xss/i', $str)) {  
                    $str = preg_replace('#</*(?:script|xss).*?>#si', '[removed]', $str);  
                }  
            } while ($original !== $str);  
            unset($original);  
      
            $pattern = '#'  
                . '<((?<slash>/*\s*)(?<tagName>[a-z0-9]+)(?=[^a-z0-9]|$)' // tag start and name, followed by a non-tag character  
                . '[^\s\042\047a-z0-9>/=]*' // a valid attribute character immediately after the tag would count as a separator  
                // optional attributes  
                . '(?<attributes>(?:[\s\042\047/=]*' // non-attribute characters, excluding > (tag close) for obvious reasons  
                . '[^\s\042\047>/=]+' // attribute characters  
                // optional attribute-value  
                . '(?:\s*=' // attribute-value separator  
                . '(?:[^\s\042\047=><`]+|\s*\042[^\042]*\042|\s*\047[^\047]*\047|\s*(?U:[^\s\042\047=><`]*))' // single, double or non-quoted value  
                . ')?' // end optional attribute-value group  
                . ')*)' // end optional attributes group  
                . '[^>]*)(?<closeTag>\>)?#isS';  
            do {  
                $old_str = $str;  
                $str = preg_replace_callback($pattern, array($this, '_sanitize_naughty_html'), $str);  
            } while ($old_str !== $str);  
            unset($old_str);  
      
            $str = preg_replace(  
                '#(alert|prompt|confirm|cmd|passthru|eval|exec|expression|system|fopen|fsockopen|file|file_get_contents|readfile|unlink)(\s*)(.∗?)
    #si',  
                '\\1\\2(\\3)',  
                $str  
            );  
            //这增加了一点额外的预防措施的情况下，通过上述过滤器  
            $str = $this->_do_never_allowed($str);  
            if ($is_image === TRUE) {  
                return ($str === $converted_string);  
            }  
            return $str;  
        } 




