<?php
/**
 * Created by PhpStorm.
 * User: haier
 * Date: 2017-11-16
 * Time: 2:02
 */

namespace classphp;


class ClubList
{
    public $ids = array();
    public $clubs= array();

    /**
     * ClubList constructor.
     * @param array $ids 乱序id数组
     * @param array $clubs 社团对象数组
     */
    public function __construct(array $ids, array $clubs)
    {
        $this->ids = $ids;
        $this->clubs = $clubs;
    }

}