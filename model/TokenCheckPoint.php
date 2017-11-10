<?php
/**对token进行解析
 * Created by PhpStorm.
 * User: haier
 * Date: 2017-11-7
 * Time: 22:58
 */



class TokenCheckPoint
{
    private $database;

    public function __invoke($request, $response, $next)
    {
        if (!$request->hasHeader('cookie')) {
            $response = $next($request, $response);
            $response->withStatus(412)->write('Precondition Failed');
        }else{
            $cookieArray = $request->getHeader('Cookie');
            $token = isset($cookieArray["token"])?$cookieArray["token"]:null;
            var_dump($cookieArray);
            $response = $next($request, $response);

        }

        return $response;
    }
    /**
     * TokenCheckPoint constructor.
     */
    public function __construct($token, $database)
    {
        $this->database = $database;

        //空检查
        if (is_null($token) || $token == "" || $token == "undefined") {
            throw new Exception('token null', 400);
        } else {
            //非法参数安全检查
            $safeObj = new Safe($token);
            if ($safeObj->getStatus() == 200) {
                $token = $safeObj->getStr();
            } else {
                $token = null;
                //$this->status = $safeObj->getStatus();
                throw new Exception($safeObj->getMsg(),$safeObj->getStatus());
            }
            //token检验
            $crypt = new classphp\ThinkCrypt();
            $userObj = new CvUser();
            //token解密成openid
            $tokenAr = $crypt->tokenDecrypt($token, $this->database);
            if (!is_array($tokenAr) || sizeof($tokenAr) == 0) {
                throw new Exception('token invalid 01', 406);
            }
            $openid = $tokenAr["openid"];
            $user_id = $tokenAr["user_id"];

            $deny = $userObj->isDenyOpenid($openid, $this->database);
            if ($deny) {
                throw new Exception('access deny', 403);
            }

            //openid 和userid 匹配
            $match = $userObj->isMatchUidOid($user_id, $openid, $this->database);
            if (!$match) {
                throw new Exception('token to invalid 02', 406);
            }

            return $token;

        }
    }
}