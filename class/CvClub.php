<?php
//与club表有关的一些操作

/**
 * Created by PhpStorm.
 * User: haier
 * Date: 2017-11-9
 * Time: 23:25
 */

namespace classphp;
class CvClub
{
    /**根据id数组 返回club对象数组
     * @param array $ids
     * @param $database
     * @param string $table
     * @return array
     */
    public function getClubsByIds(array $ids, $database, $table = "cv_club")
    {
        //遍历数组获取club
        $data = $database->select($table, [
            "id",
            "school",
            "club",
            "photo",
            "fav_num"
        ], [
            "AND" => [
                "id" => $ids,
                "visible" => 1
            ]
        ]);
        if (!is_array($data) || sizeof($data) == 0) {
            throw new \Exception("getClubByIds DB error", 500);
        }
        unset($clubs);
        $clubs = array();
        foreach ($data as $d) {
            $id = $d['id'];
            $school = $d["school"];
            $name = $d["club"];
            $photo = $d["photo"];
            $fav_num = $d["fav_num"];
            $club = new Club($id, $school, $name, $photo, $fav_num);
            $clubs[] = $club;
        }
        shuffle($clubs);
        return $clubs;


    }

    /**获取全部的club id 用于后续做ids用
     * @param $database
     * @param string $table
     * @return array
     */
    public function getAllCid($database, $table = "cv_club")
    {
        $data = $database->select($table, [
            "id"
        ], [
            "visible" => 1
        ]);
        if (!is_array($data) || sizeof($data) == 0) {
            throw new \Exception("getAllCid DB error", 500);
        }
        unset($ids);
        $ids = array();
        foreach ($data as $d) {
            $ids[] = $d["id"];
        }
        return $ids;

    }

    /** 对club表的票数数据进行更新
     * @param $club_id
     * @param $database
     * @param string $table
     *
     */
    public function voteUpdate($club_id, $database, $table = "cv_club")
    {
        $affected = $database->update($table, [
            "fav_num[+]" => POINT
        ], [
            "AND" => [
                "id" => $club_id,
                "visible" => 1
            ]
        ]);
        if (!is_numeric($affected) || $affected != 1) {
            throw new \Exception("fav_num update error", 500);
        }
    }


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
            "photo",
            "fav_num"
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
            throw new \Exception("searching DB error", 500);
        } elseif (sizeof($data) == 0) {
            return null;
        } else {
            foreach ($data as $d) {
                $id = $d["id"];
                $school = $d["school"];
                $club = $d["club"];
                $photo = $d["photo"];
                $fav_num = $d["fav_num"];
                $clubObj = new \classphp\Club($id, $school, $club, $photo, $fav_num);
                $clubObjs[] = $clubObj;
            }
            return $clubObjs;
        }
    }

    /**根据id 获取社团的基本信息  "school","club","fav_num","info","photo"
     * @param $id
     * @param $database
     * @param string $table
     * @return array 含基本信息的关联数组
     * @throws Exception
     */
    public function getBaseDetails($id, $database, $table = "cv_club")
    {
        //只获取基本信息 还差排名计算 用户头像 和底部图
        $data = $database->select($table, [
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
            throw new \Exception("gettingBaseDetails DB error", 500);
        } elseif (sizeof($data) != 1) {
            throw new \Exception("clubInfo not found", 500);
        } else {
            return $data[0];
        }
    }

    /**获取指定id的社团 在列表里的总排名 排名考虑了并列
     * @param $id
     * @param $database
     * @param string $table
     * @return int $rankAll 排名
     * @throws Exception
     */
    public function getRankAll($id, $database, $table = "cv_club")
    {
        $clubIdFav = $database->select($table, [
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

        if (!is_array($clubIdFav)) {
            throw new \Exception("RankAll DB error", 500);
        } elseif (sizeof($clubIdFav) == 0) {
            throw new \Exception("RankAllList not found", 500);
        } else {
            $rankAll = $this->rankInList($id, $clubIdFav);
            return $rankAll;
        }
    }

    /**获取指定id的社团 在列表里的校排名 排名考虑了并列
     * @param $id
     * @param $database
     * @param string $table
     * @return int $rankSch 排名
     * @throws Exception
     */
    public function getRankSch($id, $school, $database, $table = "cv_club")
    {
        $clubIdFav = $database->select($table, [
            "id",
            "fav_num"
        ], [
            "AND" => [
                "school" => $school,
                "visible" => 1
            ],
            "ORDER" => [
                "fav_num" => "DESC"
            ]
        ]);

        if (!is_array($clubIdFav)) {
            throw new \Exception("RankAll DB error", 500);
        } elseif (sizeof($clubIdFav) == 0) {
            throw new \Exception("RankAllList not found", 500);
        } else {
            $rankSch = $this->rankInList($id, $clubIdFav);
            return $rankSch;
        }


    }

    /**根据id 在社团票数降序下的IdFav的List里面 考虑并列的情况 返回排名值
     * @param $id
     * @param $clubIdFav
     * @return int
     * @throws Exception
     */
    private function rankInList($id, $clubIdFav)
    {
        unset($rankNum);
        $rankNum = -1;//初始默认报错值
        for ($i = 0, $rank = 0; $i < sizeof($clubIdFav); $i++, $rank++) {
            //rank记录当前数到的排名值
            //先排除第一名
            if ($i != 0) {
                //如果上一名同票
                if ($clubIdFav[$i - 1]["fav_num"] == $clubIdFav[$i]["fav_num"]) {
                    //当前排名-1
                    $rank--;
                }
            }
            if ($clubIdFav[$i]["id"] == $id) {
                $rankNum = $rank + 1;
                break;
            }

        }
        //for循环后没有找到这个club 找不到排名
//        var_dump($clubIdFav);
        if ($rankNum == -1) {
            throw new \Exception("club in RankList not found", 500);
        } else {
            return $rankNum;
        }
    }

}