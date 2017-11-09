<?php

/**与user表有关的一些操作
 * Created by PhpStorm.
 * User: haier
 * Date: 2017-11-2
 * Time: 0:30
 */
class CvUser
{

    /**
     * 以openid换取user_id
     * @param string $openid 微信用户的openid
     * @param object $database 数据库
     * @return int $user_id 用户id
     * @author Parry < yh@szer.me >
     */
    public function getUseridByOpenid($openid, $database, $table = "cv_user")
    {

        $data = $database->select($table, [
            "id",
            "visible"
        ], [
            "AND" => [
                "openid" => $openid,
                "visible" => 1
            ]
        ]);
//        echo "getuserid";
//        print_r($data);
        if ($data == null) {
            $user_id = $this->getUseridByCreatingUser($database, $openid);
            return $user_id;
        } else {
            foreach ($data as $d) {
                //检验是否被封号
                if ($d["visible"] != 1) {
                    return -1;
                } else {
                    $user_id = $d['id'];
                    return $user_id;
                }
            }
        }
    }

    /**判断是否存在该用户的openid
     * @param $openid
     * @param $database
     * @param string $table
     * @return mixed
     */

    public function isVaildOpenid($openid, $database, $table = "cv_user")
    {

        $has = $database->has($table, [
            "AND" => [
                "openid" => $openid,
                "visible" => 1
            ]
        ]);
        return $has;
    }

    public function isDenyOpenid($openid, $database, $table = "cv_user")
    {

        $has = $database->has($table, [
            "AND" => [
                "openid" => $openid,
                "visible[!]" => 1
            ]
        ]);
        return $has;
    }

    /**
     * 以openid 创建新用户 并返回user_id
     * @param object $database 数据库
     * @param string $openid 微信openid
     * @return int $user_id 用户id null 报错
     * @author Parry < yh@szer.me >
     */
    private function getUseridByCreatingUser($database, $openid, $table = "cv_user")
    {

        $insert_id = $database->insert($table, [
            "openid" => $openid,
            "time" => date("Y-m-d H:i:s"),
            "visible" => 1
        ]);
        if (is_numeric($insert_id) && $insert_id != 0) {
            return $insert_id;
        } else {
            return null;
        }

    }


    public function isMatchUidOid($user_id, $openid,$database,$table="cv_user")
    {
        $has = $database->has($table, [
            "AND" => [
                "id" => $user_id,
                "openid" => $openid,
                "visible" => 1
            ]
        ]);
        return $has;

    }

}