<?php
/**
 * Created by PhpStorm.
 * User: haier
 * Date: 2017-11-6
 * Time: 13:35
 */

namespace classphp;


class Json
{
   // public $retcode;
   // public $retmsg;
    public $retdata ;

    /**
     * Json constructor.
     * @param $retcode
     * @param $retmsg
     * @param $retdata
     */
    public function __construct($retdata)
    {
   //     $this->retcode = $retcode;
     //   $this->retmsg = $retmsg;
        $this->retdata = $retdata;
    }


}