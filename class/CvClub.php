<?php
//与club表有关的一些操作

/**
 * Created by PhpStorm.
 * User: haier
 * Date: 2017-11-9
 * Time: 23:25
 */
class CvClub
{
    /**
     * @param $content
     * @param $database
     * @return $results 对象数组
     */
    public function search($content, $database, $table = "cv_club")
    {
        $data = $database->select($table, [
            "id",
            "school",
            "club"
        ], [
            "AND" => [
                "OR" => [
                "school[~]"=>$content,
                "club[~]"=>$content,
                "info[~]"=>$content,
                "id[~]"=>$content,
                ],
                "visible"=>1
            ]
        ]);
        var_dump($data);
        if(!is_array($data)){
            throw new Exception("searching DB error",500);
        }elseif (sizeof($data)==0){
            return null;
        }else{
       foreach ($data as $d){
           //todo:获得的数据装进对象数组里
       }
    }

}