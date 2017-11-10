<?php
//用code创建token

use classphp\Crypt;
use classphp\ThinkCrypt;

/**
 * Created by PhpStorm.
 * User: haier
 * Date: 2017-8-18
 * Time: 23:23
 */
class CvToken extends classphp\Token
{
    public $token;
    private $access_token;
    private $refresh_token;
    private $openid;
    private $database;
    private $status = 200;


    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }


    /**
     * CvToken constructor.
     */
    public function __construct($code = null, $database = null)
    {
        // parent::__construct($token);
        $this->init($code, $database);
    }

    /**
     * Token constructor.
     * @param $token
     */

    private function init($code, $database)
    {
        $this->database = $database;
        $this->token = $this->token($code, $database);


    }
//
//    //中间件
//    public function __invoke($request, $response, $next)
//    {
//
//        $response->getBody()->write('BEFORE');
//
//        $response = $next($request, $response);
//        $response->getBody()->write('AFTER');
//
//        return $response;
//    }


    /** 记录拿code请求的log
     * @param $code
     * @param $retcode
     * @param null $retmsg
     * @param $database
     */
    private function tokenApiLog($code, $retcode, $retmsg, $table = "cv_tokenlog")
    {
        $database = $this->database;
        $agent = $_SERVER["HTTP_USER_AGENT"];
        //var_dump($database);
        $ip = $this->getIP();
        $insert = $database->insert($table, [
            "code" => $code,
            "retcode" => $retcode,
            "retmsg" => $retmsg,
            "agent" => $agent,
            "ip" => $ip,
            "time" => date("Y-m-d H:i:s", time()),
            "visible" => 1
        ]);
        //var_dump($insert);
    }

    private function token($code, $database)
    {
        try {
            //空检查
            if (is_null($code) || $code == "" || $code == "undefined") {
                $this->tokenApiLog(null, 400, "code null");
                throw new Exception('code null', 400);
            } else {
                //非法参数安全检查
                $safeObj = new Safe($code);
                if ($safeObj->getStatus() == 200) {
                    $code = $safeObj->getStr();
                } else {
                    $code = null;
                    throw new Exception($safeObj->getMsg(), $safeObj->getStatus());
                }

                //获取openid

                if (APPID == null || APPSECRET == null) {
                    $openid = md5($code);//临时测试方法
                } else {
                    $openid = $this->getOpenidByCode($code);
                }

                //openid 与解析userid检查
                if ($openid == null) {
                    $this->tokenApiLog($code, 401, "openid unauthorized");
                    throw new Exception('openid unauthorized', 401);
                } else {
                    $user_id = $this->userCheck($openid, $database);
                    if (!is_null($user_id) || $user_id != "") {
                        //创建token
                        $token = $this->createToken($user_id, $openid);
//                        $retdata = new classphp\Token($token);
                        $this->tokenApiLog($code, 200, null);
                        return $token;

                    }
                }


            }

        } catch (Exception $e) {
            //报错
            $this->status = $e->getCode();
            echo $e->getMessage();
        }
    }

    /** 根据有效的openid返回 user_id
     * @param $openid
     * @param $database
     * @return int $user_id
     */
    private function userCheck($openid, $database)
    {
        $user = new CvUser();
        $user_id = $user->getUseridByOpenid($openid, $database);
        if (is_null($user_id)) {
            //新用户 数据库里没数据 拉取信息入库
            //返回一个信息包 关联数组
            $info =$this->getUserInfo($this->access_token, $openid);

            $user_id = $user->getUseridByCreatingUser($info,$database);
        }
        try {
            if (is_null($user_id)) {
                $this->tokenApiLog("openid:" . $openid, 500, "user_id null");
//                $this->status = 500;
                throw new Exception('user_id null', 500);
            } elseif ($user_id == -1) {
                $this->tokenApiLog("openid:" . $openid, 403, "access deny");
//                $this->status = 403;
                throw new Exception("access deny", 403);
            }
            return $user_id;

        } catch (Exception $e) {
            //报错
            $this->status = $e->getCode();
            echo $e->getMessage();
        }
    }

    /**网页授权1 用code换去access_token包 内含openid
     * @param $code
     * @param string $appid
     * @param string $appsecret
     * @return null
     */
    private function getOpenidByCode($code, $appid = APPID, $appsecret = APPSECRET)
    {
        //appid 和 appsecret在配置文件中
        //根据code获得Access Token 与 openid
        $access_token_url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appid&secret=$appsecret&code=$code&grant_type=authorization_code";
        $access_token_json = $this->https_request($access_token_url);
        $access_token_array = json_decode($access_token_json, true);
        //var_dump($access_token_array);
        if (isset($access_token_array['openid'])) {
            $this->openid = $access_token_array['openid'];
            $this->access_token = $access_token_array['access_token'];
            $this->refresh_token = $access_token_array['refresh_token'];
        }
        return isset($access_token_array['openid']) ? $access_token_array['openid'] : null;
    }

    /** 获取用户信息包
     * @param $access_token
     * @param $openid
     * @return $userInfoArray 用户信息的关联数组包
     */
    private function getUserInfo($access_token, $openid)
    {
        $access_token = $this->accessTokenCheck($access_token, $openid);
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token=$access_token&openid=$openid&lang=zh_CN";
        $userInfoArray = json_decode($this->https_request($url),true);
        //var_dump($userInfoArray);
        if (isset($userInfoArray["openid"])) {
            return $userInfoArray;
        }else{
            $this->tokenApiLog(null,500,"getInfo error");
            throw new Exception("getInfo error",500);
        }

    }

    /** 检查更新 返回有效的最新的$access_token
     * @param $access_token
     * @param $openid
     * @return mixed
     */
    private function accessTokenCheck($access_token, $openid)
    {
        $checkUrl = "https://api.weixin.qq.com/sns/auth?access_token=$access_token&openid=$openid";
        $check_json = $this->https_request($checkUrl);
        $check_array = json_decode($check_json, true);
        if ($check_array["errcode"] != 0) {
           $access_token =$this->accessTokenRefresh($this->refresh_token);
           return $access_token;
        } else {
            return $access_token;
        }
    }

    /** 对access_token进行更新
     * @param $refresh_token
     * @param string $appid
     * @return mixed
     * @throws Exception
     */
    private function accessTokenRefresh($refresh_token, $appid = APPID)
    {
        $refreshUrl = "https://api.weixin.qq.com/sns/oauth2/refresh_token?appid=$appid&grant_type=refresh_token&refresh_token=$refresh_token";
        $refresh_json = $this->https_request($refreshUrl);
        $refresh_array = json_decode($refresh_json, true);
        if (isset($refresh_array["access_token"])) {
            $this->access_token = $refresh_array["access_token"];
            $this->refresh_token = $refresh_array["refresh_token"];
            return $refresh_array["access_token"];
        } else {
            $this->tokenApiLog("refresh_token:" . $refresh_token, 500, "access_refresh error");
            throw new Exception("access_refresh error", 500);
        }
    }


    private function createToken($user_id, $openid)
    {
        $crypt = new ThinkCrypt();
        //$str = $user_id . "+" . md5($openid) . "+" . date("Y-m-H d:i:s");
        $str = $user_id . "+" . $openid . "+" . date("Y-s-H d:i:m");
        return $crypt->encrypt($str);
    }


    public function https_request($url, $data = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }

// Get the ip of client; 获取客户端的IP.
    public
    function getIP()
    {
        if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
            $ip = getenv("HTTP_CLIENT_IP");
        else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
            $ip = getenv("REMOTE_ADDR");
        else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
            $ip = $_SERVER['REMOTE_ADDR'];
        else
            $ip = "unknown";
        return ($ip);
    }

}