<?php

/**
 * Created by PhpStorm.
 * User: haier
 * Date: 2017-11-15
 * Time: 22:36
 */
class CvClubList
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

    //参数控制 第一页 和其他页 获取club对象然后输出

    /**
     * CvClubList constructor.
     */
    public function __construct($ids,$token, $database)
    {
        //var_dump($ids);
       // $ids=array(2,3,1);
        $this->database = $database;
        if (is_array($ids) && sizeof($ids) == 0) {
            $json = $this->pageOne($token);
        } else {
            $json = $this->pageOther($ids,$token);
        }
        if (!is_null($json)) {
            print_r(json_encode($json));
        }
    }


    private function pageOne($token)
    {
        //乱序取全部 截取十个获取对象 剩下的放进ids 返回
        try {
            $token = new TokenCheckPoint($token, $this->database);
            $cvClub = new \classphp\CvClub();
            $ids = $cvClub->getAllCid($this->database);
            //乱序
            shuffle($ids);

            //分割
            $firstids = array_slice($ids, 0,PAGE_SIZE);//PAGE_SIZE每页的数量
            $otherids = array_slice($ids,PAGE_SIZE);
            //获取
            $clubs = $cvClub->getClubsByIds($firstids, $this->database);
            $listone = new \classphp\ClubList($otherids, $clubs);
            $retdata = new \classphp\Json($listone);
            return $retdata;
        } catch (Exception $e) {
            $this->status = $e->getCode();
            echo $e->getMessage();
        }


    }

    private function pageOther($ids,$token)
    {
        //用ids获取对象  返回
        try {
            $token = new TokenCheckPoint($token, $this->database);
            //安全检查
            $safeObj = new Safe($ids);
            if ($safeObj->getStatus() == 200) {
                $ids = $safeObj->getAr();
            } else {
                $ids = null;
//                    $this->status = $safeObj->getStatus();
                throw new Exception($safeObj->getMsg(), $safeObj->getStatus());
            }

          //  var_dump($ids);
            if (!is_array($ids)) {
                throw new Exception("ids type error", 400);
            }
            $cvClub = new \classphp\CvClub();

            $clubs = $cvClub->getClubsByIds($ids, $this->database);
            $retdata = new \classphp\Json($clubs);
            return $retdata;
        } catch (Exception $e) {
            $this->status = $e->getCode();
            echo $e->getMessage();
        }
    }

}