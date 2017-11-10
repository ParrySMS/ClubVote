<?php

/**
 * Created by PhpStorm.
 * User: haier
 * Date: 2017-11-10
 * Time: 15:13
 */
class CvClubDetails
{
    private $database;
    private $status = 200;

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }


    public function __construct($id, $token, $database)
    {
        $this->database = $database;
        $this->init($id, $token);
    }

    private function init($id, $token)
    {
        $json = $this->clubDetails($id, $token);
        if (!is_null($json)) {
            print_r(json_encode($json));
        }
    }

    private function clubDetails($id, $token)
    {
        try {
            $token = new TokenCheckPoint($token, $this->database);
            $cvClub = new CvClub();
            unset($clubBase);
            //base 包含 id school club fav_num info photo
            //返回 字串关联数组
            $clubBase = $cvClub->getBaseDetails($id, $this->database);
            $rankAll = $cvClub->getRankAll($id, $this->database);
            //todo: 求rankSch 还有 点赞头像 底部图
            return null;

        } catch (Exception $e) {
            $this->status = $e->getCode();
            echo $e->getMessage();
        }


    }


}