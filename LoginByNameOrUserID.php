<?php
define('NOROBOT', FALSE);  
define('ADMINSCRIPT', basename(__FILE__));  
define('CURSCRIPT', 'admin');  
define('HOOKTYPE', 'hookscript');  
define('APPTYPEID', 0);  
  
require_once './source/class/class_core.php';  
  
$discuz = C::app();  
$discuz->init();  
  
require_once libfile('function/member');  
require_once libfile('class/member');  
runhooks();  

// 自动登录
$fxusername = addslashes(trim($_GET['username'])); // 从url里获取的用户名
$minfo = C::t('common_member')->fetch_by_username($fxusername);
if(!empty($minfo)) {
    $uid = $minfo['uid'];
    $member = getuserbyuid($uid);
    if($member) {
        loadcache('usergroups');
        setloginstatus($member, 1296000);
        DB::query("UPDATE ".DB::table('common_member_status')." SET lastip='".$_G['clientip']."', lastvisit='".TIMESTAMP."', lastactivity='".TIMESTAMP."' WHERE uid='$uid'");
        loaducenter();
        uc_api_post('user', 'synlogin', array('uid'=>$uid));
    }
}

//跳转到论坛首页
header('Location:/DISCUZ');
