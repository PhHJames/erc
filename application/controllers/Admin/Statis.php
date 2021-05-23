<?php
/**
 * @Author: Awe
 * @Date:   2018-10-20 22:30:23
 * @Last 不要复制我的代码不然后果自负
 * @Last Modified time: 2018-11-08 11:15:41
 */
class Admin_StatisController extends Admin_BaseAuthController {
    public function init(){
        parent::init();
    }
    public function indexAction(){
        $params = $this->getParams();
        $begin_date = isset($params['begin_date']) ? $params['begin_date'] :  '';
        $end_date = isset($params['end_date']) ? $params['end_date'] :  '';

        $begin_date = $begin_date ? $begin_date :  date("Y-m-d" , strtotime("-7 days"));
        $end_date = $end_date ? $end_date :  date("Y-m-d");
        $days = Tools::diffBetweenTwoDays($begin_date , $end_date );
        if($days > 30 or $days <= 0  ){
            $this->showError("温馨提示" , "时间范围必须大于1小于30");
        }
        $commonOrderBusiness = Common::ImportBusiness("Order" , "Common");
        $list = $commonOrderBusiness->getStatisByDate( $begin_date , $end_date);
        $res = array(
            'begin_date' => $begin_date,
            'end_date' => $end_date,
            'list' => $list,
        );
        $this->displayTemplate('statis/index' , $res);
    }



}