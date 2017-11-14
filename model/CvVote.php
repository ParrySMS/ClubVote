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
    private $retcode = 200;

    /**
     * CvFav constructor.
     * @param $database
     * @param int $status
     */
    public function __construct($user_id, $club_id, $database, $status)
    {
        $this->database = $database;
        $this->init($user_id, $club_id);
    }

    private function init($user_id, $club_id)
    {
        $json = $this->cvVote($user_id, $club_id);
        if (!is_null($json)) {
            print_r(json_encode($json));
        }
    }

    //todo:关注鉴权 时间鉴权 投票 返回不同数据

    private function cvVote($user_id, $club_id)
    {
        $fav = new \classphp\CvFav();
        //时间鉴权
        if ($fav->hasVoted($user_id, $club_id, $this->database)) {
            $retcode = 200403;
            //关注鉴权
        } elseif (!$fav->hasFollowed($user_id, $this->database)) {
            $retcode = 200401;
        } else {
            $retcode = 200200;
        }
        //todo:对票数进行处理


    }
}