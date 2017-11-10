<?php

/**关于cv_bottompic表的操作
 * Created by PhpStorm.
 * User: haier
 * Date: 2017-11-11
 * Time: 5:56
 */



class CvBottomPic
{

    /**获取底部的两张图片对象数字
     * @param $school
     * @param $database
     * @return array
     * @throws Exception
     */
    public function getBottomObjs($school, $database,$table="cv_bottompic")
    {
        $data = $database->select($table,[
            "pic1",
            "loc1",
            "pic2",
            "loc2"
        ], [
            "AND" => [
                "school" => $school,
                "visible" => 1
            ]
        ]);
        if (!is_array($data) || sizeof($data) != 1) {
            $up = new \classphp\Pic(null, null);
            $dowm = new \classphp\Pic(null, null);
            return $bottom = array($up, $dowm);
        } else {
            foreach ($data as $d) {
                $up = new \classphp\Pic($d["pic1"], $d["loc1"]);
                $dowm = new \classphp\Pic($d["pic2"], $d["loc2"]);
                return $bottom = array($up, $dowm);
            }
        }
    }
}

