<?php
/**
 * Created by PhpStorm.
 * User: haier
 * Date: 2017-11-10
 * Time: 15:18
 */

namespace classphp;


class ClubDetails extends Club
{
    public $schRank;
    public $totalRank;
    public $desc;
    //头像
    public $avatars = array();
    //底部图
    public $sponsors;

    /**
     * ClubDetails constructor.
     * @param $schRank
     * @param $totalRank
     * @param $desc
     * @param array $avatars
     * @param $fav_num
     * @param $sponsors
     */
    public function __construct($id, $school, $club, $photo,$fav_num,$schRank, $totalRank, $desc, array $avatars, $sponsors)
    {
        parent::__construct($id, $school, $club, $photo,$fav_num);
        $this->schRank = $schRank;
        $this->totalRank = $totalRank;
        $this->desc = $desc;
        $this->avatars = $avatars;
        $this->sponsors = $sponsors;
    }


}