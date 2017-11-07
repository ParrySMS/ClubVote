<?php
/**
 * Created by PhpStorm.
 * User: haier
 * Date: 2017-11-6
 * Time: 11:44
 */

namespace classphp;


interface Crypt
{
    function encrypt($data, $key, $expire);
    /*
     * 加密
     */
    function decrypt($token, $database);
    /*
     * 解密
     */
    function tokenDecrypt($token, $database);
    /*
     * 对token解密 以获取信息
     */

}