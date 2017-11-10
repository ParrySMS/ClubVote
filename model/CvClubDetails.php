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
            $cvBottom = new CvBottomPic();
            unset($clubBase);
            //base 包含 school club fav_num info photo
            //返回 字串关联数组clubBase
            $clubBase = $cvClub->getBaseDetails($id, $this->database);
//            var_dump($clubBase);
            $school = $clubBase["school"];
            $club = $clubBase["club"];
            $photo = $clubBase["photo"];
            $fav_num = $clubBase["fav_num"];
            $info = $clubBase["info"];

            $rankAll = $cvClub->getRankAll($id, $this->database);

            $rankSch = $cvClub->getRankSch($id, $school, $this->database);
            $bottom = $cvBottom->getBottomObjs($school, $this->database);
            //todo:  点赞头像(需要点赞记录表)
            $icons = array();

            $clubDerails = new \classphp\ClubDetails($id, $school, $club, $photo, $fav_num, $info, $rankSch, $rankAll, $icons, $bottom);
            $retdata = new \classphp\Json($clubDerails);
            return $retdata;

        } catch (Exception $e) {
            $this->status = $e->getCode();
            echo $e->getMessage();
        }


    }


}