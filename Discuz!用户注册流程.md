# Discuz!用户注册流程

标签： Web安全

---
http://localhost/discuz/upload/member.php?mod=register
dz的用户注册功能通过 on_register()函数实现，下面对代码进行逐行分析：

	global $_G;
    //Array ( [username] => pI65tC [password] => SIR9ER [password2] => QMMY1m [email] => DATa8u )
	$_GET['username'] = trim($_GET[''.$this->setting['reginput']['username']]);
	$_GET['password'] = $_GET[''.$this->setting['reginput']['password']];
	$_GET['password2'] = $_GET[''.$this->setting['reginput']['password2']];
	$_GET['email'] = $_GET[''.$this->setting['reginput']['email']];

dz的所有配置都存在$_G['setting']数组里。当需要使用配置时，进行`global $_G;`操作即可加载所有配置项。
下面那行注释是我加进去的，抓包即可发现post表单中的name值并不是普通的username，password等，反而都很怪异，其实是和配置项中定义的name是相同的。也是一种防范流量劫持的思路。

    if($_G['uid']) {
		$ucsynlogin = $this->setting['allowsynlogin'] ? uc_user_synlogin($_G['uid']) : '';
		$url_forward = dreferer();
		if(strpos($url_forward, $this->setting['regname']) !== false) {
			$url_forward = 'forum.php';
		}
		showmessage('login_succeed', $url_forward ? $url_forward : './', array('username' => $_G['member']['username'], 'usergroup' => $_G['group']['grouptitle'], 'uid' => $_G['uid']), array('extrajs' => $ucsynlogin));}





