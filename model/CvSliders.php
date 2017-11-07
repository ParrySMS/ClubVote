<?php

/**
 * Created by PhpStorm.
 * User: haier
 * Date: 2017-11-7
 * Time: 21:58
 */
class CvSliders
{
    private $database;
    private $status = 200;

    /**
     * CvSliders constructor.
     * @param $database
     */
    public function __construct($token, $database)
    {

        $this->init($token, $database);

    }

    private function init($token, $database)
    {
        $this->database = $database;
        $json = $this->sliders($token);
        if (!is_null($json)) {
            print_r(json_encode($json));
        }

    }

    private function sliders($token)
    {
        try {
            //空检查
            if (is_null($token) || $token == "" || $token == "undefined") {
                $this->status = 400;
                throw new Exception('token null');
            } else {
                //非法参数安全检查
                $safeObj = new Safe($token);
                if ($safeObj->getStatus() == 200) {
                    $token = $safeObj->getStr();
                } else {
                    $token = null;
                    $this->status = $safeObj->getStatus();
                    throw new Exception($safeObj->getMsg());
                }
                //token检验
                $crypt = new classphp\ThinkCrypt();
                $userObj = new CvUser();
                //token解密成openid
                $openid = $crypt->tokenDecrypt($token, $this->database);
                if ($openid === null) {
                    $this->status = 400;
                    throw new Exception('token invalid 01');
                }

                $deny =$userObj->isDenyOpenid($openid,$this->database);
                if($deny){
                    $this->status = 403;
                    throw new Exception('access deny');
                }

                $vaild =$userObj->isVaildOpenid($openid,$this->database);
                if(!$vaild){
                    $this->status = 400;
                    throw new Exception('token to invalid 02');
                }
                //Todo: 获取图片对象


            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }


}