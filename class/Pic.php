<?php
/**
 * Created by PhpStorm.
 * User: haier
 * Date: 2017-11-7
 * Time: 21:55
 */

namespace classphp;


class Pic
{
    public $pic;
    public $location;

    /**
     * Slider constructor.
     * @param $pic
     * @param $location
     */
    public function __construct($pic, $location)
    {
        $this->pic = $pic;
        $this->location = $location;
    }

}