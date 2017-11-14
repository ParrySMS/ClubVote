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
    //時間鉴权
    public function hasVoted($club_id, $user_id, $database, $table = "cv_fav")
    {
        $has = $database->has($table, [
            "uid" => $user_id,
            "cid" => $club_id,
            "time[<>]" => [date("Y-m-d 00:00:00"), date("Y-m-d H:i;s")],
            "visible" => 1
        ]);
        return $has;

//        $data = $database->select($table, [
//            "time"
//        ], [
//            "AND" => [
//                "uid" => $user_id,
//                "visible" => 1
//            ]
//            ,
//            "ORDER" => [
//                "time" => "DESC"
//            ]
//        ]);
////      print_r($data);
////    echo gettype($data);
//        //数组空 == false 判断为真
//        if ($data == false) {
//            //查不到没发过问题
//            return false;
//        } else {
//            //检查时间
//            $latest_time = $data[0]["time"];//自取第一个最晚的时间
//            $latest_date = date("Y-m-d", strtotime($latest_time));
//            $now_date = date("Y-m-d", time());
//            // var_dump($latest_date);
//            // var_dump($now_date);
//            if ($latest_date == $now_date) {
//                //echo "true";
//                //已经发过
//                return true;
//            } else {
//                return false;
//            }
//
//        }
    }

    /**用userid 调用user表查找unionid 然后发向内部接口 判断是否已经关注
     * @param $user_id
     * @return bool
     */
    public function hasFollowed($user_id,$database)
    {
        //user表 獲取unionid
        $user = new CvUser();
        $unionid = $user->getUnionidByUserid($user_id,$database);
        //判断是否关注公众号
        $crypt = new ThinkCrypt();
        $str = $crypt->encrypt("T@oKeLiZh!",'',0,0);
        $url = "https://api.szu.me/api_com/weixin_sub/gx_sub.php?unionid=$unionid&str=$str";
        $json_array = json_decode(https_request($url), true);
       if (isset($json_array["subscribe"]) && $json_array["subscribe"] === 1) {
           return true;
       } else {
           return false;
       }

    }

    public function vote($club_id, $user_id, $database, $msg = null, $table = "cv_fav")
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
            throw new Exception("voteLog insert error", 500);
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