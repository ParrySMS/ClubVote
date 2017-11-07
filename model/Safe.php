<?php

// PHP整站防注入程序，需要在公共文件中require_once本文件
// 摘自 http://www.jb51.net/article/30079.htm，并稍作了修改
class Safe
{
    private $status=200;
    private $msg;
    private $str;

    /**
     * @return mixed
     */
    public function getStr()
    {
        return $this->str;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return mixed
     */
    public function getMsg()
    {
        return $this->msg;
    }

    function __construct($str)
    {
        // 判断magic_quotes_gpc状态
        if (@get_magic_quotes_gpc()) {
            $_GET = $this->sec($_GET);
            $_POST = $this->sec($_POST);
            $_COOKIE = $this->sec($_COOKIE);
            $_FILES =$this-> sec($_FILES);
        }

        $_SERVER =$this-> sec($_SERVER);
        $this->str =$this->safe_check($str);
    }

// 字符串过滤函数
    function safe_check($str)
    {
        $str = trim($str);
        $str = strip_tags($str);
        $str = $this->search_check($this->str_check($str));
        $str = preg_replace("/\r\n/", "", $str);
        $str = str_replace("<", "", $str);
        $str = str_replace(">", "", $str);
        return $str;
    }

// 整型过滤函数
    function num_check($id)
    {
        if (!$id) {     // 判断是否为空
            return null;
        } elseif (inject_check($id)) {
            return null;
        } elseif (!is_numeric($id)) {
            return null;
        }
        $id = intval($id);
        return $id;
    }

// 数组过滤函数
    function sec(&$array)
    {
        //如果是数组，遍历数组，递归调用
        if (is_array($array)) {
            foreach ($array as $k => $v) {
                $array [$k] = $this->sec($v);
            }
        } else if (is_string($array)) {
            //使用addslashes函数来处理
            $array = addslashes($array);
        } else if (is_numeric($array)) {
            $array = intval($array);
        }
        return $array;
    }

// 字符过滤函数
    function str_check($str)
    {
        if ($this->inject_check($str)) {
           $this->msg="illegal argument exception";
           $this->status=400;
//           throw new Exception("illegal argument exception");
           // die ('Illegal Argument Exception');
        }
        //注入判断
        $str = htmlspecialchars($str);
        //转换html
        return $str;
    }

// 替换字符的过滤函数
    function search_check($str)
    {
        //$str = str_replace("_", "\_", $str);
        //把"_"过滤掉
        $str = str_replace("%", "\%", $str);
        //把"%"过滤掉
        $str = htmlspecialchars($str);
        //转换html
        return $str;
    }

// 表单过滤函数
    function post_check($str, $min, $max)
    {
        if (isset ($min) && mb_strlen($str) < $min) {
            $this->msg="min: $min byte";
           $this->status=400;
            //die ("min: $min byte");
        } else if (isset ($max) && mb_strlen($str) > $max) {
            $this->msg="max: $max byte";
            $this->status=400;
            //die ("max: $max byte");
        }
        return stripslashes_array($str);
    }

// 防注入函数
    function inject_check($sql_str)
    {
        return preg_match('/select|insert|update|delete|\'|\/\*|\*|\.\.\/|\.\/|UNION|into|load_file|outfile/', $sql_str);
        // 进行过滤，防注入
    }

    function stripslashes_array(&$array)
    {
        if (is_array($array)) {
            foreach ($array as $k => $v) {
                $array [$k] = stripslashes_array($v);
            }
        } else if (is_string($array)) {
            $array = stripslashes($array);
        }
        return $array;
    }
}

?>