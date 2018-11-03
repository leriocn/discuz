<?php
/*请做好校验，推荐使用url加密验证*/

$fxusername = $_GET['uname'];
require_once './source/class/class_core.php';  
$discuz = C::app();  
$discuz->init_mobile = false;
$discuz->init();  

// 自动登录
$minfo = C::t('common_member')->fetch_by_username($fxusername);
if(!empty($minfo)) {
    $uid = $minfo['uid'];
    $member = getuserbyuid($uid);
    if($member) {
        require_once libfile('function/member');
        setloginstatus($member, 2592000);
        DB::query("UPDATE ".DB::table('common_member_status')." SET lastip='".$_G['clientip']."', lastvisit='".TIMESTAMP."', lastactivity='".TIMESTAMP."' WHERE uid='$uid'");
        return true;
    }
}

//跳转到论坛首页
header('Location:./');
