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
    /** 搜索内容
     * @param $content
     * @param $database
     * @return  $clubObjs 对象数组
     */
    public function search($content, $database, $table = "cv_club")
    {
        $data = $database->select($table, [
            "id",
            "school",
            "club",
            "photo"
        ], [
            "AND" => [
                "OR" => [
                    "school[~]" => $content,
                    "club[~]" => $content,
                    "info[~]" => $content,
                    "id[~]" => $content,
                ],
                "visible" => 1
            ]
        ]);
        //var_dump($data);
        unset($clubObjs);
        $clubObjs = array();
        if (!is_array($data)) {
            throw new Exception("searching DB error", 500);
        } elseif (sizeof($data) == 0) {
            return null;
        } else {
            foreach ($data as $d) {
                $id = $d["id"];
                $school = $d["school"];
                $club = $d["club"];
                $photo = $d["photo"];
                $clubObj = new \classphp\Club($id, $school, $club, $photo);
                $clubObjs[] = $clubObj;
            }
            return $clubObjs;
        }
    }


    public function getBaseDetails($id, $database, $table = "cv_club")
    {
        //只获取基本信息 还差排名计算 用户头像 和底部图
        $data = $database->select($table, [
            "id",
            "school",
            "club",
            "fav_num",
            "info",
            "photo"
        ], [
            "AND" => [
                "id" => $id,
                "visible" => 1
            ]
        ]);


        if (!is_array($data)) {
            throw new Exception("gettingBaseDetails DB error", 500);
        } elseif (sizeof($data) == 0) {
            throw new Exception("club_id invaild", 406);
        } else {
            return $data;
        }
    }

    public function getRankAll($id, $database, $table = "cv_club")
    {
        $clubIds = $database->select($table, [
            "id",
            "fav_num"
        ], [
            "AND" => [
                "visible" => 1
            ],
            "ORDER" => [
                "fav_num" => "DESC"
            ]
        ]);

        if (!is_array($clubIds)) {
            throw new Exception("RankAll DB error", 500);
        } elseif (sizeof($clubIds) == 0) {
            throw new Exception("RankAllList not found", 500);
        } else {

            unset($rankAll);
            $rankAll = -1;//初始默认报错值
            for ($i = 0, $rank = 0; $i < sizeof($clubIds); $i++, $rank++) {
                //rank记录当前数到的排名值
                //先排除第一名
                if ($i != 0) {
                    //如果上一名同票
                    if ($clubIds[$i - 1]["fav_num"] == $clubIds[$i]["fav_num"]) {
                        //当前排名-1
                        $rank--;
                    }
                }
                if ($clubIds[$i]["id"] == $id) {
                    $rankAll = $rank+1;
                    break;
                }

            }
            //for循环后没有找到这个club 找不到排名
            if ($rankAll == -1) {
                throw new Exception("club in RankAllList not found", 500);
            } else {
                return $rankAll;
            }

        }
    }

}