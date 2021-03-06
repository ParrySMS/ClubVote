<?php

//创建jssdk的签名包
class CvJSSDK
{
    private $appId = APPID;
    private $appSecret = APPSECRET;
    private $status = 200;


    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function __construct($url = null)
    {
        $this->init($url);
    }

    private function init($url)
    {
        $json = $this->sign($url);
        if (!is_null($json)) {
            print_r(json_encode($json));
        }

    }

    private function sign($url)
    {
        try {
            if (is_null($url)) {
                throw new Exception("url null", 400);
            } else {
                //安全檢查过滤 不需要 因为不经过数据库
//                $safeObj = new Safe($url);
//                if ($safeObj->getStatus() == 200) {
//                    $url = $safeObj->getStr();
//                } else {
//                    $url = null;
////                    $this->status = $safeObj->getStatus();
//                    throw new Exception($safeObj->getMsg(), $safeObj->getStatus());
//                }
//

                //获取签名包
                $signPackage = $this->getSignPackage($url);
                $timestamp = $signPackage["timestamp"];
                $nonceStr = $signPackage["nonceStr"];
                $signatrue = $signPackage["signature"];
                $retdata = new classphp\JsRetdata($timestamp, $nonceStr, $signatrue);
                return new classphp\Json($retdata);


            }
        } catch (Exception $e) {
            $this->setStatus($e->getCode());
            echo $e->getMessage();
        }

    }

    private function getSignPackage($url)
    {
        $jsapiTicket = $this->getJsApiTicket();

        // 注意 URL 一定要动态获取，不能 hardcode.
        // $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        //$url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        $timestamp = time();
        $nonceStr = $this->createNonceStr();

        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

        $signature = sha1($string);

        $signPackage = array(
            "appId" => $this->appId,
            "nonceStr" => $nonceStr,
            "timestamp" => $timestamp,
            "url" => $url,
            "signature" => $signature,
            "rawString" => $string
        );
        return $signPackage;
    }

    private function createNonceStr($length = 16)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    private function getJsApiTicket()
    {
        // jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
        $data = json_decode($this->get_php_file("./jssdk/jsapi_ticket.php"));
        if ($data->expire_time < time()) {
		//if (1) {
            $accessToken = $this->getAccessToken();
            // 如果是企业号用以下 URL 获取 ticket
            // $url = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=$accessToken";
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
            $res = json_decode($this->httpGet($url));
            //var_dump($res);
			 if (!isset($res->ticket)) {
                //var_dump($res);
                throw new Exception("JSSDK-TICKET error:$res->errmsg", 500);
            }
            $ticket = $res->ticket;
            
                $data->expire_time = time() + 7000;
                $data->jsapi_ticket = $ticket;
                $this->set_php_file("./jssdk/jsapi_ticket.php", json_encode($data));
            
        } else {
            $ticket = $data->jsapi_ticket;
        }

        return $ticket;
    }

    private function getAccessToken()
    {
        // access_token 应该全局存储与更新，以下代码以写入到文件中做示例
        $data = json_decode($this->get_php_file("./jssdk/access_token.php"));
        if ($data->expire_time < time()) {
		//不使用缓存的测试
		//if (1) {
            // 如果是企业号用以下URL获取access_token
            // $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=$this->appId&corpsecret=$this->appSecret";
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->appId&secret=$this->appSecret";
			//$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=APPID&secret=APPSECRET";

            $res = json_decode($this->httpGet($url));
            if (!isset($res->access_token)) {
                //var_dump($url);
				//var_dump($this->httpGet($url));
				//var_dump($res);
                throw new Exception("JSSDK-ACTOKEN error:$res->errmsg", 500);
            }
			//var_dump($res);
            $access_token = $res->access_token;

            $data->expire_time = time() + 7000; //留200秒做平滑过度
            $data->access_token = $access_token;
            $this->set_php_file("./jssdk/access_token.php", json_encode($data));
        } else {
            $access_token = $data->access_token;

        }
        return $access_token;
    }

    private function httpGet($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        // 为保证第三方服务器与微信服务器之间数据传输的安全性，所有微信接口采用https方式调用，必须使用下面2行代码打开ssl安全校验。
        // 如果在部署过程中代码在此处验证失败，请到 http://curl.haxx.se/ca/cacert.pem 下载新的证书判别文件。
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_CAINFO, PEM_FILE); //证书判别文件路径常量 PEM_FILE 见config
        curl_setopt($curl, CURLOPT_URL, $url);

        $res = curl_exec($curl);
        curl_close($curl);

        return $res;
    }

    private function get_php_file($filename)
    {
        return trim(substr(file_get_contents($filename), 15));
    }

    private function set_php_file($filename, $content)
    {
        $fp = fopen($filename, "w");
        fwrite($fp, "<?php exit();?>" . $content);
        fclose($fp);
    }
}

