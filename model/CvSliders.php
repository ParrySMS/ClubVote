<?php

/**
 * Created by PhpStorm.
 * User: haier
 * Date: 2017-11-7
 * Time: 21:58
 */
class CvSliders
{
    private $database;
    private $status = 200;

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * CvSliders constructor.
     * @param $database
     */
    public function __construct($token, $database)
    {

        $this->init($token, $database);

    }

    private function init($token, $database)
    {
        $this->database = $database;
        $json = $this->sliders($token);
        if (!is_null($json)) {
            print_r(json_encode($json));
        }

    }

    private function sliders($token)
    {
        try {
            //调用TokenCheckPoint 检查token
                $token = new TokenCheckPoint($token,$this->database);

                // 获取图片对象
                $slidersPic =array(SLIDER_PIC1,SLIDER_PIC2,SLIDER_PIC3,SLIDER_PIC4,SLIDER_PIC5);
                $slidersLoc =array(SLIDER_LOC1,SLIDER_LOC2,SLIDER_LOC3,SLIDER_LOC4,SLIDER_LOC5);
                $sliders = array();
                for($i=0;$i<SLIDER_NUM;$i++){
                    $slider = new classphp\Pic($slidersPic[$i],$slidersLoc[$i]);
                    $sliders[] =$slider;
                }
                $retdata =new classphp\Sliders($sliders);
                //var_dump($retdata);
                return new classphp\Json($retdata);

        } catch (Exception $e) {
            $this->status = $e->getCode();
            echo $e->getMessage();
        }
    }


}