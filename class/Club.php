<?php
/**
 * Created by PhpStorm.
 * User: haier
 * Date: 2017-11-10
 * Time: 9:45
 */

namespace classphp;


class Club
{
    public $id;
    public $ascription; //归属 指的是学校
    public $name; //社团名
    public $img_url;
    public $fav_num;
   // public $info;

    /**
     * Club constructor.
     * @param $id
     * @param $school
     * @param $club
//     * @param $fav_num
//     * @param $info
     * @param $photo
     */
    public function __construct($id, $school, $name, $photo,$fav_num)
    {
        $this->id = $id;
        $this->ascription = $school;
        $this->name = $name;
        $this->fav_num = $fav_num;
//        $this->info = $info;
        $this->img_url = $photo;
    }


}