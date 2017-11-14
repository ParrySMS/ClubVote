<?php

/**关于cv_bottompic表的操作
 * Created by PhpStorm.
 * User: haier
 * Date: 2017-11-11
 * Time: 5:56
 */


namespace classphp;

class CvBottomPic
{
    /** 随机按照比例获取底部图
     * @param $d  foreach过后的关联数组
     * @return Image
     */
    private function randomPic(array $d)
    {
        $num = rand(1, 10 * (PR_A + PR_B + PR_C));
        if ($num <= 10 * PR_A) {
            return new \classphp\Image($d["img_urlA"], $d["target_urlA"]);
        } elseif ($num <= 10 * PR_A + 10 * PR_B) {
            return new \classphp\Image($d["img_urlB"], $d["target_urlB"]);
        } else {
            return new \classphp\Image($d["img_urlC"], $d["target_urlC"]);
        }
    }

    /**获取底部的两张图片对象数字
     * @param $school
     * @param $database
     * @return array
     * @throws Exception
     */
    public function getBottomObjs($school, $database, $table = "cv_bottompic")
    {
        $data = $database->select($table, [
            "img_url1",
            "target_url1",
            "img_urlA",
            "img_urlB",
            "img_urlC",
            "target_urlA",
            "target_urlB",
            "target_urlC",
        ], [
            "AND" => [
                "school" => $school,
                "visible" => 1
            ]
        ]);
        if (!is_array($data) || sizeof($data) != 1) {
            $up = new \classphp\Image(null, null);
            $dowm = new \classphp\Image(null, null);
            return $bottom = array($up, $dowm);
        } else {
            foreach ($data as $d) {
                $up = new \classphp\Image($d["img_url1"], $d["target_url1"]);
                $dowm = $this->randomPic($d);
                return $bottom = array($up, $dowm);
            }
        }
    }
}

