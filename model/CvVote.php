<?php

/**
 * Created by PhpStorm.
 * User: haier
 * Date: 2017-11-15
 * Time: 2:05
 */
class CvVote
{
    private $database;
    private $status = 200;
    private $retcode = 500500;

    /**
     * @return int
     */
    public function getRetcode()
    {
        return $this->retcode;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }


    /**
     * CvFav constructor.
     * @param $database
     * @param int $status
     */
    public function __construct($token, $club_id, $database)
    {
        $this->database = $database;
        $this->init($token, $club_id);
    }

    private function init($token, $club_id)
    {
        $user_id = $this->getUid($token);
        $this->cvVote($user_id, $club_id);
        //这个json直接在外用slim输出retcode
//        if (!is_null($json)) {
//            print_r(json_encode($json));
//        }
    }

    private function getUid($token)
    {
        try {
            $crypt = new \classphp\ThinkCrypt();
            $tokenObj =new TokenCheckPoint($token,$this->database);
            $tokenArr = $crypt->tokenDecrypt($token, $this->database);
            if(!is_array($tokenArr)|| sizeof($tokenArr) == 0){
                throw new Exception("token invaild 01",406);
            }
            return $tokenArr["user_id"];
        }catch (Exception $e){
            $this->status = $e->getCode();
            echo $e->getMessage();
        }

    }

//关注鉴权 时间鉴权 投票 返回不同数据

    private
    function cvVote($user_id, $club_id)
    {
        try {
//安全检查
            $safeObj = new Safe($user_id);
            if ($safeObj->getStatus() == 200) {
                $user_id = $safeObj->getStr();
            } else {
                $user_id = null;
//                    $this->status = $safeObj->getStatus();
                throw new Exception($safeObj->getMsg(), $safeObj->getStatus());
            }
//安全检查
            $safeObj = new Safe($club_id);
            if ($safeObj->getStatus() == 200) {
                $club_id = $safeObj->getStr();
            } else {
                $club_id = null;
//                    $this->status = $safeObj->getStatus();
                throw new Exception($safeObj->getMsg(), $safeObj->getStatus());
            }

            $fav = new \classphp\CvFav();
            //时间鉴权
            if ($fav->hasVoted($user_id, $club_id, $this->database)) {
                $this->retcode = 200403;
                //关注鉴权
            } elseif (!$fav->hasFollowed($user_id, $this->database)) {
                $this->retcode = 200401;
            } else {
                $club = new \classphp\CvClub();
                //刷新票数
                $club->voteUpdate($club_id, $this->database);
                //投票记录
                $fav->vote($club_id, $user_id, $this->database);
                $this->retcode = 200200;
            }
        } catch (Exception $e) {
            //失敗時的投票记录
            $fav->vote($club_id, $user_id, $this->database, $e->getMessage());
            $this->status = $e->getCode();
            echo $e->getMessage();
        }


    }
}