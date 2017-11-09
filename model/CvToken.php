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
    public function __construct($token, $code = null, $database = null)
    {
        // parent::__construct($token);
        $this->init($token, $code, $database);
    }

    /**
     * Token constructor.
     * @param $token
     */

    private function init($token, $code, $database)
    {
        $this->database = $database;
        $this->token = $token;
        $json = $this->token($code, $database);
        if (!is_null($json)) {
            print_r(json_encode($json));
        }
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
                throw new Exception('code null',400);
            } else {
                //非法参数安全检查
                $safeObj = new Safe($code);
                if ($safeObj->getStatus() == 200) {
                    $code = $safeObj->getStr();
                } else {
                    $code = null;
                    throw new Exception($safeObj->getMsg(),$safeObj->getStatus());
                }

                //获取openid

                if (APPID == null || APPSECRET == null) {
                    $openid = md5($code);//临时测试方法
                } else {
                    $openid = $this->getOpenidByCode($code);
                }

                //openid 与解析userid检查
                if ($openid == null) {
                    $this->tokenApiLog($code, 401, "openid null");
                    throw new Exception('openid null',401);
                } else {
                    $user_id = $this->userCheck($openid, $database);
                    if (!is_null($user_id) || $user_id != "") {
                        //创建token
                        $token = $this->createToken($user_id, $openid);
                        $retdata = new classphp\Token($token);
                        $this->tokenApiLog($code, 200, null);
                        return new classphp\Json($retdata);
                    }
                }


            }

        } catch (Exception $e) {
            //报错
            $this->status = $e->getCode();
            echo $e->getMessage();
        }
    }

    private function userCheck($openid, $database)
    {
        $user = new CvUser();
        $user_id = $user->getUseridByOpenid($openid, $database);
        try {
            if (is_null($user_id)) {
                $this->tokenApiLog("openid:" . $openid, 500, "user_id null");
//                $this->status = 500;
                throw new Exception('user_id null',500);
            } elseif ($user_id == -1) {
                $this->tokenApiLog("openid:" . $openid, 403, "access deny");
//                $this->status = 403;
                throw new Exception("access deny",403);
            }
            return $user_id;
        } catch (Exception $e) {
            //报错
            $this->status = $e->getCode();
            echo $e->getMessage();
        }
    }

    private function getOpenidByCode($code, $appid = APPID, $appsecret = APPSECRET)
    {
        //appid 和 appsecret在配置文件中
        //根据code获得Access Token 与 openid
        $access_token_url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appid&secret=$appsecret&code=$code&grant_type=authorization_code";
        $access_token_json = $this->https_request($access_token_url);
        $access_token_array = json_decode($access_token_json, true);
        //var_dump($access_token_array);
        //$access_token = $access_token_array['access_token'];
        return isset($access_token_array['openid']) ? $access_token_array['openid'] : null;
    }

    private function createToken($user_id, $openid)
    {
        $crypt = new ThinkCrypt();
        //$str = $user_id . "+" . md5($openid) . "+" . date("Y-m-H d:i:s");
        $str = $user_id . "+" . $openid . "+" . date("Y-m-H d:i:s");
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