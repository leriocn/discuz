<?php

/**
 * [$corpid 企业ID]
 * @var string
 */
$corpid = "wxbe5bc8702c1******";

/**
 * [$appSecret 应用密钥]
 * @var string
 */
$appSecret = "1qRZny9lzJlCchvpswwvZRtQOr************";

/**
 * [getJson Get方式获取数据]
 * @param  [type] $url [URL地址]
 * @return [type]      [数组]
 */
function getJson($url){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch);
    curl_close($ch);
    return json_decode($output, true);
}

/**
 * [curl_post_https Post方式获取https请求]
 * @param  [type] $url  [URL地址]
 * @param  [type] $data [POST的数据，数组]
 * @return [type]       [返回数组]
 */
function curl_post_https($url,$data){ // 模拟提交数据函数
    $curl = curl_init(); // 启动一个CURL会话
    curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE); // 对认证证书来源的检查
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE); // 从证书中检查SSL加密算法是否存在
    //curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
    curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
    curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data,JSON_UNESCAPED_UNICODE)); // Post提交的数据包
    curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
    curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
    $tmpInfo = curl_exec($curl); // 执行操作
    if (curl_errno($curl)) {
        return false;
    }
    curl_close($curl); // 关闭CURL会话
    return json_decode($tmpInfo, true); // 返回数据，json格式
}

/**
 * [getAccessToken 获取企业微信Token]
 * @return [type] [字符串]
 */
function getAccessToken() {
    $tokenFile = "./access_token.txt";//缓存文件名
    $data = json_decode(file_get_contents($tokenFile));
    if ($data->expire_time < time() or !$data->expire_time) {
        $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=$corpid&corpsecret=$appSecret";
        $res = getJson($url);
        $access_token = $res['access_token'];
        if($access_token) {
            $data->expire_time = time() + 7000;
            $data->access_token = $access_token;
            $fp = fopen($tokenFile, "w");
            fwrite($fp, json_encode($data));
            fclose($fp);
        }
    } else {
      $access_token = $data->access_token;
    }
    return $access_token;
}

/**
 * [checkLoginValide 校验当前合法性]
 * @param  [type] $code [企业微信登录的code返回]
 * @return [type]       [用户名]
 */
function checkLoginValide($code){
    if($code){
       $access_token = getAccessToken();
       $url = "https://qyapi.weixin.qq.com/cgi-bin/user/getuserinfo?access_token=$access_token&code=$code";
       $ret = getJson($url);
       @$user_ticket = $ret['user_ticket'];
       if($user_ticket){
            $tcUrl = "https://qyapi.weixin.qq.com/cgi-bin/user/getuserdetail?access_token=$access_token";
            $param = array('user_ticket' => $user_ticket);
            $retUInfo = curl_post_https($tcUrl,$param);
            @$userName = $retUInfo['name'];
            if($userName){
                return $userName;
            }
       }
    }
    
    return false;
}

/**
 * [initLoginState 登录状态校验]
 * @param  [type] $code [企业微信登录COde]
 * @return [type]       [布尔]
 */
function initLoginState($code){
    //校验合法性
    $fxusername = checkLoginValide($code);
    if($fxusername){
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
    }

    return false;
}

//处理登录状态
@$code = $_POST['code'];
if($code){
    $ret = initLoginState($code);
    echo json_encode(array('ret' => $ret),JSON_UNESCAPED_UNICODE);
    die();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Discuz论坛企业微信自动登录</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <style type="text/css">
        * {margin: 0,auto; padding: 0;}
        .information {font-size: 0.1em; color:#aaa;position: absolute;bottom:10px;right:10px;}
    </style>
</head>
<body>
    <div style="width:100%;text-align: center;"><h3 style="color:#366cb3"></h3></div>
    <div class="information"></div>
</body>
<script src="http://code.jquery.com/jquery-1.12.4.min.js" integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ=" crossorigin="anonymous"></script>
<script>
  $(function(){
        function getQueryString(name){
          var query = window.location.search.substring(1);
           var vars = query.split("&");
           for (var i=0;i<vars.length;i++) {
               var pair = vars[i].split("=");
               if(pair[0] == name){return pair[1];}
           }
           return(false);
        }
        
        var loadInfo = '正在跳转，请稍后';
        var count = 0;
        function loadingInfo(){
            count++;
            if(count>6){
                count = 0;
            }
            var dot = "";
            var tmp = count;
            while(tmp>0){
                dot += '.';
                tmp--;
            }
            $('h3').text(loadInfo + dot);
    
            setTimeout(loadingInfo,250);
        }

        var code = getQueryString('code');
        if(code){
            $.ajax({
                type: "POST",
                url: "login.php",
                data: {code:code},
                dataType: "json",
                beforeSend: function(){
                    loadingInfo();
                },
                success: function(data){
                    $('.information').text(JSON.stringify(data));
                    if(data.ret)
                    {
                        loadInfo = ":-) 自动登陆成功，正在跳转";
                        $('h3').css('color','green');
                    }
                    window.location.href = "./";
                },
                error: function(err){
                     $('.information').text(err.responseText);
                    window.location.href = "./";
                }
            });
        }
        else{
            window.location.href = "./";
        }
    });
</script>
</html>
