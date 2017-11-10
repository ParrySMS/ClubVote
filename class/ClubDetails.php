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
    public $rankSch;
    public $rankAll;
    public $info;
    //头像
    public $icons = array();
    public $fav_num;
    //底部图
    public $bottom;

    /**
     * ClubDetails constructor.
     * @param $id
     * @param $school
     * @param $club
     * @param $photo
     * @param null $fav_num
     * @param null $info
     * @param $rankSch
     * @param $rankAll
     * @param array $icons
     * @param array $bottom
     */
    public function __construct($id, $school, $club, $photo, $fav_num, $info, $rankSch, $rankAll, array $icons, $bottom)
    {
        parent::__construct($id, $school, $club, $photo, $fav_num, $info);
        $this->fav_num = $fav_num;
        $this->info = $info;
        $this->rankSch = $rankSch;
        $this->rankAll = $rankAll;
        $this->icons = $icons;
        $this->bottom = $bottom;
    }

    /**
     * @param mixed $rankAll
     * @return ClubDetails
     */
    public function setRankAll($rankAll)
    {
        $this->rankAll = $rankAll;
        return $this;
    }


}