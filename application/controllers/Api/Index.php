<?php
/**
 * Created by 不要复制我的代码.
 * User: Administrator
 * Date: 2019/10/23 0023
 * Time: 23:54
 */
class Api_IndexController extends Api_CommonController
{
    public function init()
    {
        parent::init();
    }
    public function indexAction(){
        echo " this is API  控制器的API目录就是API存放的地方 ， 业务放在 Business/Api 目录";
    }
}