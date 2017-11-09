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


    public function __construct($content, $token, $database)
    {
        $this->database = $database;
        $this->init($content,$token);
    }

    private function init($content,$token)
    {
        $json = $this->search($content,$token);
        if (!is_null($json)) {
            print_r(json_encode($json));
        }
    }

    private function search($content,$token){
        try{
        $token = new TokenCheckPoint($token,$this->database);
        $cvClub = new CvClub();
        $resultObjs = $cvClub->search($content,$this->database);
        //todo: 把返回的数组等着成json


        }catch (Exception $e){
            $this->status = $e->getCode();
            echo $e->getMessage();
        }


    }



}