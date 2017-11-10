<?php

/**
 * 搜索功能
 * Created by PhpStorm.
 * User: haier
 * Date: 2017-11-8
 * Time: 16:42
 */
class CvSearch
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


    public function __construct($content, $token, $database)
    {
        $this->database = $database;
        $this->init($content, $token);
    }

    private function init($content, $token)
    {
        $json = $this->search($content, $token);
        if (!is_null($json)) {
            print_r(json_encode($json));
        }
    }

    private function search($content, $token)
    {
        try {
            //执行token检查
            $token = new TokenCheckPoint($token, $this->database);
            //对club表进行搜索
            $cvClub = new CvClub();
            $clubObjs = $cvClub->search($content, $this->database);
            //封装
            $retdata = new classphp\Clubs($clubObjs);
            //var_dump($retdata);
            return new classphp\Json($retdata);
        } catch (Exception $e) {
            $this->status = $e->getCode();
            echo $e->getMessage();
        }


    }


}