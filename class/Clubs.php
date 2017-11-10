<?php
/**
 * Created by PhpStorm.
 * User: haier
 * Date: 2017-11-10
 * Time: 9:55
 */

namespace classphp;


class Clubs
{
    public $clubs=array();

    /**
     * Sliders constructor.
     * @param $clubs
     */
    public function __construct($clubs)
    {
        $this->clubs = $clubs;
    }
}