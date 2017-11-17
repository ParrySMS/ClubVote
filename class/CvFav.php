<?php

/** 对cv_fav表的一些操作
 * Created by PhpStorm.
 * User: haier
 * Date: 2017-11-14
 * Time: 14:20
 */
//id uid cid time visible

namespace classphp;
class CvFav
{
    //获取最新的投票人的头像 连表查询 默认6个
    public function getAvators($club_id, $database, $num = null, $table = "cv_fav")
    {
        $num = is_null($num) ? AVATORS_NUM : $num;
        $data = $database->select($table, [
            "[><]cv_user(u)" => ["uid" => "id"],
        ], [
            "cv_fav.uid",
            "u.icon",
        ],
            [
                "AND" => [
                    "cv_fav.cid" => $club_id,
                    "cv_fav.visible" => 1,
                    "u.visible" => 1
                ],
                "ORDER" => [
                    "cv_fav.time" => "DESC"
                ],
                "LIMIT" => 5*$num //考虑要去重 留多5倍数量
            ]);;

        //去重
        $data_uni = array_unique($data, SORT_REGULAR);
//         var_dump($data_uni);
        //取若干个出来
        unset($avators);
        $avators = array();

        foreach ($data_uni as $d) {
            if(sizeof($avators)==$num){
                break;
            }
            $avators[]=$d["icon"];
        }
        //var_dump($avators);
        return $avators;

    }

//時間鉴权
    public
    function hasVoted($user_id, $club_id, $database, $table = "cv_fav")
    {

        $data = $database->select($table, [
            "time"
        ], [
            "AND" => [
                "uid" => $user_id,
//                "cid" => $club_id,
                "visible" => 1
            ]
            ,
            "ORDER" => [
                "time" => "DESC"
            ]
        ]);
        //数组空 == false 判断为真
        if ($data == false||sizeof($data)<FAV_LIMIT) {
            //查不到没发过问题
            return false;
        } else {
            //var_dump($data);
            //检查时间
                        //每天限制投票三次
            $latest_time = $data[FAV_LIMIT-1]["time"];//自取第n-1个最新的时间
            $latest_date = date("Y-m-d", strtotime($latest_time));
            $now_date = date("Y-m-d", time());
            // var_dump($latest_date);
            // var_dump($now_date);
            if ($latest_date == $now_date) {
                //echo "true";
                //已经发过

                return true;
            } else {
                return false;
            }

        }
    }

    /**用userid 调用user表查找unionid 然后发向内部接口 判断是否已经关注
     * @param $user_id
     * @return bool
     */
    public
    function hasFollowed($user_id, $database)
    {
        //user表 獲取unionid
        $user = new CvUser();
        $unionid = $user->getUnionidByUserid($user_id, $database);
        //判断是否关注公众号
        $crypt = new ThinkCrypt();
        $str = $crypt->encrypt("T@oKeLiZh!", '', 0, 0);
        $url = "https://api.szu.me/api_com/weixin_sub/gx_sub.php?unionid=$unionid&str=$str";
        $json_array = json_decode($this->https_request($url), true);
        if (isset($json_array["subscribe"]) && $json_array["subscribe"] == 1) {
            return true;
        } else {
//            var_dump($json_array);
            return false;
        }

    }

    public
    function vote($club_id, $user_id, $database, $msg = null, $table = "cv_fav")
    {


        //对club表数据更新 放在classphp\CvClub里面

        //投票记录
        $insert_id = $database->insert($table, [
            "uid" => $user_id,
            "cid" => $club_id,
            "msg" => $msg,
            "time" => date("Y-m-d H:i:s", time()),
            "visible" => 1
        ]);
        if (!is_numeric($insert_id) || $insert_id < 1) {
            throw new \Exception("voteLog insert error", 500);
        }

    }

    public
    function https_request($url, $data = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }

}