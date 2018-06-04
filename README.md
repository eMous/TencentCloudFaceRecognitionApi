# TencentCloudFaceRecognitionApi

腾讯云人脸识别API的PHP版函数实例

使用方法：

0. 设置函数返回模式，定义FACETEST_DEBUG，函数将返回True or False，腾讯云的相应将直接被输出到标准输出。否则，结果将被json_encode成数组，作为返回值返回。

1. 导入你的 $appid, $secret_id, $secret_key, $web_server_ip （你可以把它放在同目录下的TencentCloudInfo.php中）

2. 调用函数相应即可
