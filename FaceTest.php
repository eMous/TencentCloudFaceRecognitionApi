<?php

define("FACETEST_DEBUG",true);

// Set your $appid, $secret_id, $secret_key, $web_server_ip
require_once __DIR__ . "/TencentCloudInfo.php";

/*
 * Basic Functions
 */
// PHP CURL HTTPS POST
function curl_post_http($url, $header, $data)
{ // 模拟提交数据函数
    $curl = curl_init(); // 启动一个CURL会话
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
    curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data)); // Post提交的数据包
    curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
    curl_setopt($curl, CURLINFO_HEADER_OUT, true);
    if (!defined("FACETEST_DEBUG"))
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    $tmpInfo = curl_exec($curl); // 执行操作
    $tmpInfo = json_decode($tmpInfo, true);

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

/*
 * API functions
 */
/* Alive Check */
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

/* Multiple Identity */
// Identity multiple faces in a pic.
function tencentCloudMultIdentify($appid, $authorization, $url, $group_id_s, $session_id = "")
{
    $header = array(
        'Content-Type: application/json',
        "Host: recognition.image.myqcloud.com",
        "Authorization: " . $authorization
    );
    $group_key = "group_id";
    if (is_array($group_id_s))
        $group_key = "group_ids";
    $data = array("appid" => $appid, $group_key => $group_id_s, "url" => $url);
    if ($session_id)
        $data["session_id"] = $session_id;
    $ret = curl_post_http("https://recognition.image.myqcloud.com/face/multidentify", $header, $data);
    return $ret;
}

/* Face analyse */
// Face detect and analyse from an image.
function tencentCloudFaceDetect($appid, $authorization, $url, $mode = 1)
{
    $header = array(
        'Content-Type: application/json',
        "Host: recognition.image.myqcloud.com",
        "Authorization: " . $authorization
    );
    $data = array("appid" => $appid, "url" => $url, "mode" => $mode);
    $ret = curl_post_http("http://recognition.image.myqcloud.com/face/detect", $header, $data);
    return $ret;
}

/* Face Shape Location */
// Locate organs of faces.
function tencentCloudFaceShape($appid, $authorization, $url, $mode = 1)
{
    $header = array(
        'Content-Type: application/json',
        "Host: recognition.image.myqcloud.com",
        "Authorization: " . $authorization
    );
    $data = array("appid" => $appid, "url" => $url, "mode" => $mode);
    $ret = curl_post_http("http://recognition.image.myqcloud.com/face/shape", $header, $data);
    return $ret;
}

/* Personal information manager*/
// Register a new person in cloud.
function tencentCloudAddPerson($appid, $authorization, $group_ids, $person_id, $person_image_url, $person_name = "", $tag = "")
{

    $header = array(
        'Content-Type: application/json',
        "Host: recognition.image.myqcloud.com",
        "Authorization: " . $authorization
    );

    $data = array("appid" => $appid, "group_ids" => $group_ids, "person_id" => $person_id, "url" => $person_image_url);
    if ($person_name)
        $data["person_name"] = $person_name;
    if ($tag)
        $data["tag"] = $tag;

    $ret = curl_post_http("http://recognition.image.myqcloud.com/face/newperson", $header, $data);

    return $ret;
}

// Delete a person.
function tencentCloudDeletePerson($appid, $authorization, $person_id)
{
    $header = array(
        'Content-Type: application/json',
        "Host: recognition.image.myqcloud.com",
        "Authorization: " . $authorization
    );
    $data = array("appid" => $appid, "person_id" => $person_id);
    $ret = curl_post_http("http://recognition.image.myqcloud.com/face/delperson", $header, $data);

    return $ret;
}

// Add a face picture to a person's db in cloud.
function tencentCloudAddFace($appid, $authorization, $person_id, $urls, $tag = "")
{

    $header = array(
        'Content-Type: application/json',
        "Host: recognition.image.myqcloud.com",
        "Authorization: " . $authorization
    );

    $data = array("appid" => $appid, "person_id" => $person_id, "urls" => $urls);
    if ($tag)
        $data["tag"] = $tag;

    $ret = curl_post_http("https://recognition.image.myqcloud.com/face/addface", $header, $data);

    return $ret;
}

// Delete a face belonging to some person..
function tencentCloudDelFace($appid, $authorization, $person_id, $face_ids)
{
    $header = array(
        'Content-Type: application/json',
        "Host: recognition.image.myqcloud.com",
        "Authorization: " . $authorization
    );

    $data = array("appid" => $appid, "person_id" => $person_id, "face_ids" => $face_ids);
    $ret = curl_post_http("https://recognition.image.myqcloud.com/face/delface", $header, $data);

    return $ret;
}

// Set person's name and tag.
function tencentCloudSetPersonInfo($appid, $authorization, $person_id, $person_name = "", $person_tag = "")
{
    $header = array(
        'Content-Type: application/json',
        "Host: recognition.image.myqcloud.com",
        "Authorization: " . $authorization
    );
    $data = array("appid" => $appid, "person_id" => $person_id);
    if ($person_name)
        $data["person_name"] = $person_name;
    if ($person_tag)
        $data["tag"] = $person_tag;

    $ret = curl_post_http("https://recognition.image.myqcloud.com/face/setinfo", $header, $data);

    return $ret;
}

// Get person's info(name tag face_ids).
function tencentCloudGetPersonInfo($appid, $authorization, $person_id)
{
    $header = array(
        'Content-Type: application/json',
        "Host: recognition.image.myqcloud.com",
        "Authorization: " . $authorization
    );
    $data = array("appid" => $appid, "person_id" => $person_id);
    $ret = curl_post_http("https://recognition.image.myqcloud.com/face/getinfo", $header, $data);

    return $ret;
}

// Get group list.
function tencentCloudGetGroupIds($appid, $authorization)
{
    $header = array(
        'Content-Type: application/json',
        "Host: recognition.image.myqcloud.com",
        "Authorization: " . $authorization
    );

    $data = array("appid" => $appid);
    $ret = curl_post_http("https://recognition.image.myqcloud.com/face/getgroupids", $header, $data);
    return $ret;
}

// Get person ids in some group.
function tencentCloudGetPersonIds($appid, $authorization, $group_id)
{
    $header = array(
        'Content-Type: application/json',
        "Host: recognition.image.myqcloud.com",
        "Authorization: " . $authorization
    );

    $data = array("appid" => $appid, "group_id" => $group_id);
    $ret = curl_post_http("https://recognition.image.myqcloud.com/face/getpersonids", $header, $data);
    return $ret;
}

// Get face ids from a person.
function tencentCloudGetFaceIds($appid, $authorization, $person_id)
{
    $header = array(
        'Content-Type: application/json',
        "Host: recognition.image.myqcloud.com",
        "Authorization: " . $authorization
    );

    $data = array("appid" => $appid, "person_id" => $person_id);
    $ret = curl_post_http("https://recognition.image.myqcloud.com/face/getfaceids", $header, $data);
    return $ret;
}

// Get face info.
function tencentCloudGetFaceInfo($appid, $authorization, $face_id)
{
    $header = array(
        'Content-Type: application/json',
        "Host: recognition.image.myqcloud.com",
        "Authorization: " . $authorization
    );

    $data = array("appid" => $appid, "face_id" => $face_id);
    $ret = curl_post_http("https://recognition.image.myqcloud.com/face/getfaceinfo", $header, $data);
    return $ret;
}

// Add groups for a person.
function tencentCloudAddGroupsForPerson($appid, $authorization, $person_id, $group_ids)
{
    $header = array(
        'Content-Type: application/json',
        "Host: recognition.image.myqcloud.com",
        "Authorization: " . $authorization
    );
    $data = array("appid" => $appid, "group_ids" => $group_ids, "person_id" => $person_id);

    $ret = curl_post_http("https://recognition.image.myqcloud.com/face/addgroupids", $header, $data);
    return $ret;
}

// Delete groups a person belonging.
function tencentCloudDeleteGroupsForPerson($appid, $authorization, $person_id, $group_ids)
{
    $header = array(
        'Content-Type: application/json',
        "Host: recognition.image.myqcloud.com",
        "Authorization: " . $authorization
    );
    $data = array("appid" => $appid, "group_ids" => $group_ids, "person_id" => $person_id);

    $ret = curl_post_http("https://recognition.image.myqcloud.com/face/delgroupids", $header, $data);
    return $ret;
}

/* Verify Face(image ==> target person ? true or false) */
// Verify a person with the uploaded pic.
function tencentCloudFaceVerify($appid, $authorization, $person_id, $url)
{
    $header = array(
        'Content-Type: application/json',
        "Host: recognition.image.myqcloud.com",
        "Authorization: " . $authorization
    );

    $data = array("appid" => $appid, "person_id" => $person_id, "url" => $url);
    $ret = curl_post_http("https://recognition.image.myqcloud.com/face/verify", $header, $data);
    return $ret;
}

/* Identify Face(image ==> search people possibly matched */
// Identify a person in cloud person db.
function tencentCloudFaceIdentify($appid, $authorization, $group_id_s, $url)
{
    $header = array(
        'Content-Type: application/json',
        "Host: recognition.image.myqcloud.com",
        "Authorization: " . $authorization
    );

    $group_key = "group_id";
    if (is_array($group_id_s))
        $group_id_s = "group_ids";

    $data = array("appid" => $appid, $group_key => $group_id_s, "url" => $url);
    $ret = curl_post_http("https://recognition.image.myqcloud.com/face/identify", $header, $data);
    return $ret;
}

/* Compare two faces */
// Compare two faces with two images.
function tencentCloudFaceCompare($appid, $authorization, $urlA, $urlB)
{
    $header = array(
        'Content-Type: application/json',
        "Host: recognition.image.myqcloud.com",
        "Authorization: " . $authorization
    );
    $data = array("appid" => $appid, "urlA" => $urlA, "urlB" => $urlB);
    $ret = curl_post_http("https://recognition.image.myqcloud.com/face/compare", $header, $data);
    return $ret;
}

/*
 * User Functions
 */
// Example usage
function test()
{
    global $appid, $secret_key, $secret_id, $web_server_ip;
    $auth = getAuthorization($appid, $secret_id, $secret_key);
//    tencentCloudFaceAlive($url, $appid, $auth);
//    tencentCloudAddPerson($appid, $auth, array("test"), "anon", $web_server."/photo/me1.png");
//    tencentCloudAddFace($appid,$auth,"anon",array($web_server."/photo/me1.png"));
//    tencentCloudFaceVerify($appid, $auth, "anon", $url);
//    tencentCloudFaceIdentify($appid, $auth, "test", $url);
//    tencentCloudDeletePerson($appid,$auth,"anon");
//    tencentCloudDelFace($appid,$auth,"anon",array("2608353382257177155"));
//    tencentCloudSetPersonInfo($appid,$auth,"anon","young me");
//    tencentCloudGetPersonInfo($appid,$auth,"anon");
//    tencentCloudGetGroupIds($appid, $auth);
//    tencentCloudGetPersonIds($appid,$auth,"test");
//    tencentCloudGetFaceIds($appid,$auth,"anon1");
//    tencentCloudGetFaceInfo($appid,$auth,"2605913984284553244");
//    tencentCloudAddGroupsForPerson($appid,$auth,"anon",array("test1","test2"));
//    tencentCloudDeleteGroupsForPerson($appid,$auth,"anon",array("test1","test2"));
//    tencentCloudFaceCompare($appid,$auth,$web_server."/photo/me1.png",$web_server."/photo/me.png");
//    tencentCloudMultIdentify($appid, $auth, $web_server_ip."/photo/me3.png", "test");
//    tencentCloudFaceDetect($appid, $auth, $web_server_ip . "/photo/me.png");
    tencentCloudFaceShape($appid, $auth, $web_server_ip . "/photo/timg.jpg");
}


test();
