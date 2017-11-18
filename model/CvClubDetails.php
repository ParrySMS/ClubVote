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
    private $avator_num = AVATORS_NUM;

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }


    public function __construct($id, $token,$avator_num ,$database)
    {
        $this->database = $database;
        $this->avator_num = $avator_num;
        $this->init($id,$token);
    }

    private function init($id,$token)
    {
        $json = $this->clubDetails($id,$token);
        if (!is_null($json)) {
            print_r(json_encode($json));
        }
    }

    private function clubDetails($id,$token)
    {
        try {
            //安全检查
            $safeObj = new Safe($id);
            if ($safeObj->getStatus() == 200) {
                $content = $safeObj->getStr();
            } else {
                $id = null;
//                    $this->status = $safeObj->getStatus();
                throw new Exception($safeObj->getMsg(), $safeObj->getStatus());
            }

            if(is_null($id)){
                throw new Exception("club_id null or not number",400);
            }
            $token = new TokenCheckPoint($token, $this->database);
            $cvClub = new classphp\CvClub();
            $cvFav = new classphp\CvFav();
            $cvBottom = new classphp\CvBottomPic();
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

            $totalRank = $cvClub->getRankAll($id, $this->database);

            $schRank = $cvClub->getRankSch($id, $school, $this->database);
            unset($avatars,$sponsors);
            $sponsors = $cvBottom->getBottomObjs($school, $this->database);
            //点赞头像(需要点赞记录表)

            $avatars =   $cvFav->getAvators($id,$this->database,$this->avator_num);
//            $sponsors =array($sponsorGZH,$sponsorOne);

            //$id, $school, $club, $photo,$fav_num,$schRank, $totalRank, $desc, array $avatars, $sponsors
            $clubDerails = new \classphp\ClubDetails($id, $school, $club, $photo, $fav_num, $schRank, $totalRank,$info, $avatars, $sponsors);
            $retdata = new \classphp\Json($clubDerails);
            return $retdata;

        } catch (Exception $e) {
            $this->status = $e->getCode();
            echo $e->getMessage();
        }


    }


}