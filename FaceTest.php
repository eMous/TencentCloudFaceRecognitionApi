<?php

// Set your $appid, $secret_id, $secret_key, $web_server_ip
require_once __DIR__."/TencentCloudInfo.php";

// Base Functions
/* PHP CURL HTTPS POST */
function curl_post_http($url, $header, $data)
{ // 模拟提交数据函数
    $curl = curl_init(); // 启动一个CURL会话
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
    curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data)); // Post提交的数据包
    curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLINFO_HEADER_OUT, true);
    $tmpInfo = curl_exec($curl); // 执行操作
    var_dump($tmpInfo);

    if (curl_errno($curl)) {
        echo 'Errno' . curl_error($curl);//捕抓异常
    }
    curl_close($curl); // 关闭CURL会话
    return $tmpInfo; // 返回数据，json格式
}
//Get Tencent Cloud Authorization
function getAuthorization($appid, $secret_id, $secret_key)
{
    $bucket = "tencentyun";
    $expired = time() + 2592000;
    $onceExpired = 0;
    $current = time();
    $rdm = rand();
    $userid = "0";
    $fileid = "fileID";

    $srcStr = 'a=' . $appid . '&b=' . $bucket . '&k=' . $secret_id . '&e=' . $expired . '&t=' . $current . '&r=' . $rdm . '&u='
        . $userid . '&f=';

    $srcWithFile = 'a=' . $appid . '&b=' . $bucket . '&k=' . $secret_id . '&e=' . $expired . '&t=' . $current . '&r=' . $rdm . '&u='
        . $userid . '&f=' . $fileid;

    $srcStrOnce = 'a=' . $appid . '&b=' . $bucket . '&k=' . $secret_id . '&e=' . $onceExpired . '&t=' . $current . '&r=' . $rdm
        . '&u=' . $userid . '&f=' . $fileid;

    $signStr = base64_encode(hash_hmac('SHA1', $srcStr, $secret_key, true) . $srcStr);

    $srcWithFile = base64_encode(hash_hmac('SHA1', $srcWithFile, $secret_key, true) . $srcWithFile);

    $signStrOnce = base64_encode(hash_hmac('SHA1', $srcStrOnce, $secret_key, true) . $srcStrOnce);

    return $signStr;
}

// API functions
// Check the pic that it is alive person or not.
function tencentCloudFaceAlive($url, $appid, $authorization)
{
    $header = array(
        'Content-Type: application/json',
        "Host: recognition.image.myqcloud.com",
        "Authorization: " . $authorization
    );
    $data = array("appid" => $appid, "url" => $url);
    $ret = curl_post_http("http://recognition.image.myqcloud.com/face/livedetectpicture", $header, $data);
    return $ret;
}
// Register a new person in cloud.
function tencentCloudAddPerson($appid, $authorization, $group_ids, $person_id, $person_image_url, $person_name = "", $tag = "")
{

    $header = array(
        'Content-Type: application/json',
        "Host: recognition.image.myqcloud.com",
        "Authorization: " . $authorization
    );

    $data = array("appid" => $appid, "group_ids" => $group_ids, "person_id" => $person_id, "url" => $person_image_url);
    echo json_encode($data) . "\n";
    if ($person_name)
        $data["person_name"] = $person_name;
    if ($tag)
        $data["tag"] = $tag;

    $ret = curl_post_http("http://recognition.image.myqcloud.com/face/newperson", $header, $data);

    return $ret;
}
// Add a face picture to a person's db in cloud.
function tencentCloudAddFace($appid, $authorization,$person_id,$urls,$tag = ""){

    $header = array(
        'Content-Type: application/json',
        "Host: recognition.image.myqcloud.com",
        "Authorization: " . $authorization
    );

    $data = array("appid" => $appid,  "person_id" => $person_id, "urls" => $urls);
    if ($tag)
        $data["tag"] = $tag;

    $ret = curl_post_http("https://recognition.image.myqcloud.com/face/addface", $header, $data);

    return $ret;
}
// Verify a person with the uploaded pic.
function tencentCloudFaceVerify($appid, $authorization,$person_id,$url){
    $header = array(
        'Content-Type: application/json',
        "Host: recognition.image.myqcloud.com",
        "Authorization: " . $authorization
    );

    $data = array("appid" => $appid,  "person_id" => $person_id, "url" => $url);
    $ret = curl_post_http("https://recognition.image.myqcloud.com/face/verify", $header, $data);
    return $ret;
}

// User Functions
function test($url)
{
    global $appid;
    global $secret_key;
    global $secret_id;
    $auth = getAuthorization($appid, $secret_id, $secret_key);
    //$ret = tencentCloudFaceAlive($url, $appid, $auth);
//    tencentCloudAddPerson($appid, $auth, array("test"), "anon", "http://193.112.191.202/photo/me.png");
//    tencentCloudAddFace($appid,$auth,"anon",array("http://193.112.191.202/photo/me1.png"));
    tencentCloudFaceVerify($appid,$auth,"anon","http://showyoumycode.com/photo/me2.png");
}

//$auth = getAuthorization($appid, $secret_id, $secret_key);
//tencentCloudAddPerson($appid,$auth,"test","anon","http://193.112.191.202/photo/me.png");
test($web_server_ip."/photo/timg.jpg");