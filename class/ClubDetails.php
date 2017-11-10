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
    //头像
    public $icons=array();
    //底部图
    public $bottomPic=array();

    public $rankSch;
    public $rankAll;

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
     * @param array $bottomPic
     */
    public function __construct($id, $school, $club, $photo,$fav_num, $info,$rankSch,$rankAll,array $icons, array $bottomPic)
    {
        parent::__construct($id, $school, $club, $photo,$fav_num, $info);
        $this->rankSch = $rankSch;
        $this->rankAll = $rankAll;
        $this->icons = $icons;
        $this->bottomPic = $bottomPic;
    }


}