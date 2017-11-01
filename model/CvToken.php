<?php
namespace classphp;
use app\config;
use Think\Exception;

/**
 * Created by PhpStorm.
 * User: haier
 * Date: 2017-8-18
 * Time: 23:23
 */

class CvToken extends Token
{
    public $token;
    private $database;

    /**
     * CvToken constructor.
     */
    public function __construct($token,$database)
    {
        parent::__construct($token);
        $this->init($token,$database);
    }

    /**
     * Token constructor.
     * @param $token
     */

    private function init($token,$database)
    {
        $this->database = $database;
        $this->token = $token;
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
    private function tokenApiLog($code, $retcode, $retmsg,$agent,$table="cv_tokenlog")
    {
        $database=$this->database;
        $ip=$this->getIP();
        $insert = $database->insert($table, [
            "code" => $code,
            "retcode" => $retcode,
            "retmsg" => $retmsg,
            "agent"=>$agent,
            "ip"=>$ip,
            "time" => date("Y-m-d H:i:s", time()),
            "visible" => 1
        ]);
//    var_dump($insert);
    }

    private function token($code,$database){
        try {
            if (is_null($code) || $code == "" || $code == "undefined") {
                $this->tokenApiLog(null,400, "code null",$database);
                header('HTTP/1.1 400 Bad request');
                throw new Exception('code null');
            }else{
                if (APPID == null || APPSECRET == null) {
                    $openid = md5($code);//临时测试方法
                } else {
                    $openid = $this->getOpenidByCode($code);
                }


                if ($openid == null) {
                    $this->tokenApiLog($code, 500, "openid null",$database);
                    header('HTTP/1.1 500 Internal Server Error');
                    throw new Exception('openid null');
                } else{
                    $user_id =$this->userCheck($openid, $database);
                    if (!is_null($code) || $code != "") {
                        $token = createToken($user_id, $openid);
                        $retdata = new Token($token);
                        header('HTTP/1.1 200 OK');
                        JsonPrint(200, null, $retdata);
                        $this->tokenApiLog($code, 200, null, $database);
                    }
                }


                }

        }catch (Exception $e){
            //报错
            echo $e->getMessage();
        }
    }

    private function userCheck($openid, $database)
    {
        $user = new User();
        $user_id = getUseridByOpenid($openid, $database);
        if (is_null($user_id)) {
            $this->tokenApiLog("openid:" . $openid, 500, "user_id null", $database);
            header('HTTP/1.1 500 Internal Server Error');
            throw new Exception('openid null');
        } elseif ($user_id == -1) {
            $this->tokenApiLog("openid:" . $openid, 403, "access deny", $database);
            header('HTTP/1.1 403 Forbidden');
            throw new Exception("access deny");
        }
        return $user_id;
    }

    private function getOpenidByCode($code, $appid = APPID, $appsecret = APPSECRET)
    {
        //appid 和 appsecret在配置文件中
        //根据code获得Access Token 与 openid
        $access_token_url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appid&secret=$appsecret&code=$code&grant_type=authorization_code";
        $access_token_json = https_request($access_token_url);
        $access_token_array = json_decode($access_token_json, true);
        //var_dump($access_token_array);
        //$access_token = $access_token_array['access_token'];
        return isset($access_token_array['openid'])?$access_token_array['openid']:null;
    }

    public function https_request($url, $data = null){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }
    // Get the ip of client; 获取客户端的IP.
    public function getIP(){
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
        return($ip);
    }

}