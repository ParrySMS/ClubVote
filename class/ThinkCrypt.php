<?php
/**
 * 加密与解密
 * Created by PhpStorm.
 * User: haier
 * Date: 2017-11-6
 * Time: 11:49
 */

namespace classphp;


class ThinkCrypt implements Crypt
{
    /**
     * 系统加密方法
     * @param string $data 要加密的字符串
     * @param string $key 加密密钥
     * @param int $expire 过期时间 单位 秒
     * @return string
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    function encrypt($data, $key = '', $expire = 0, $withNonstr = 1)
    {
        if ($withNonstr == 1) {
            $data = $data . "+" . NONSTR;
        }
        $key = md5(empty($key) ? DATA_AUTH_KEY : $key);
        $data = base64_encode($data);
        $x = 0;
        $len = strlen($data);
        $l = strlen($key);
        $char = '';

        for ($i = 0; $i < $len; $i++) {
            if ($x == $l) $x = 0;
            $char .= substr($key, $x, 1);
            $x++;
        }

        $str = sprintf('%010d', $expire ? $expire + time() : 0);

        for ($i = 0; $i < $len; $i++) {
            $str .= chr(ord(substr($data, $i, 1)) + (ord(substr($char, $i, 1))) % 256);
        }
        return str_replace(array('+', '/', '='), array('-', '_', ''), base64_encode($str));
    }



//echo tokenDecrypt("MDAwMDAwMDAwMK6bppu8uX-dfriNaIy3nZ2xurmkvLV6zn56epm1d7adrqx3rbKpg5uJzo6cf8p2bw");


    /**
     * token解密方法
     * @param  string $token 要解密的token （必须是think_encrypt方法加密混淆的字符串）
     * @param object $database 数据库
     * @return array $tokenAr
     * @author Parry < yh@szer.me >
     */
    function tokenDecrypt($token, $database)
    {
        if ($token == null) {
            return null;
        } else {
            $str = $this->thinkDecrypt($token);
            $user_id = strtok($str, "+");
            $openid = strtok("+");
            $time = strtok("+");
            $taoke = strtok("+");
            if ($taoke != "taoke") {
                return null;
            } else {
                unset($tokenAr);
                $tokenAr = array();
                $tokenAr["user_id"] = $user_id;
                $tokenAr["openid"] = $openid;
                $tokenAr["time"] = $time;
                return $tokenAr;
            }
        }
    }

    /**
     * 系统解密方法
     * @param  string $data 要解密的字符串 （必须是think_encrypt方法加密的字符串）
     * @param  string $key 加密密钥
     * @return string
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */

    function thinkDecrypt($data, $key = '')
    {
        $key = md5(DATA_AUTH_KEY);
        $data = str_replace(array('-', '_'), array('+', '/'), $data);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
        $data = base64_decode($data);
        $expire = substr($data, 0, 10);
        $data = substr($data, 10);

        if ($expire > 0 && $expire < time()) {
            return '';
        }
        $x = 0;
        $len = strlen($data);
        $l = strlen($key);
        $char = $str = '';

        for ($i = 0; $i < $len; $i++) {
            if ($x == $l) $x = 0;
            $char .= substr($key, $x, 1);
            $x++;
        }

        for ($i = 0; $i < $len; $i++) {
            if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1))) {
                $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
            } else {
                $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
            }
        }
        return base64_decode($str);
    }


}