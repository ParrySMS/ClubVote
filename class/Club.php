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
    public $school;
    public $club;
    public $photo;

    /**
     * Club constructor.
     * @param $id
     * @param $school
     * @param $club
//     * @param $fav_num
//     * @param $info
     * @param $photo
     */
    public function __construct($id, $school, $club, $photo)
    {
        $this->id = $id;
        $this->school = $school;
        $this->club = $club;
//        $this->fav_num = $fav_num;
//        $this->info = $info;
        $this->photo = $photo;
    }


}