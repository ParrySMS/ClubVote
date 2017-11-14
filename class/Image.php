<?php
/**
 * Created by PhpStorm.
 * User: haier
 * Date: 2017-11-7
 * Time: 21:55
 */

namespace classphp;


class Image{
    public $img_url;
    public $target_url;

    /**
     * Image constructor.
     * @param $img_url
     * @param $target_ur
     */
    public function __construct($img_url, $target_url)
    {
        $this->img_url = $img_url;
        $this->target_url = $target_url;
    }


}