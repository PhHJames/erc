<?php
/**
 * Created by 不要复制我的代码.
 * User: Administrator
 * Date: 2019/10/23 0023
 * Time: 23:54
 */
class Api_RsyncController extends Api_CommonController
{
    public function init()
    {
        parent::init();
    }

   /* public function rsync_trc20Action(){
        $str = file_get_contents("php://input") ;
        $result=json_decode($str,true);
        if( empty($result )){
            echo "error";
            exit;
        }
        $businessOrders = Common::ImportBusiness("Orders" , "Api");
        echo  "数据已接收到了\n";
        try{
            $businessOrders->do_success_trc20_order($result);
        }catch(Exception $e ){
            echo  "trc20异步回调出错了，错误信息： " . $e->getMessage() . "\n" ;
        }
    }*/

    //接收trx的交易事件
    public function trx_transAction(){
        $str = file_get_contents("php://input") ;
        $result=json_decode($str,true);
        if( empty($result )){
            echo "error";
            exit;
        }
        $logFile = Log_file::getInstance(['filename' => 'trx_trans' ]);
        //echo "接收到数据：" . $str . "\n";
        echo "OK";
        $logFile->Write("info" , $str);
        $BlockTrans = Common::ImportBusiness("BlockTrans" , "Api");
        try{
            $BlockTrans->do_trx_trans($result);
        }catch(Exception $e ){
            echo  "TRX消息处理失败，错误信息： " . $e->getMessage() . "\n" ;
        }
    }
    //接收usdt的交易事件
    public function usdt_transAction(){
        $str = file_get_contents("php://input") ;
        $result=json_decode($str,true);
        if( empty($result )){
            echo "error";
            exit;
        }
        $logFile = Log_file::getInstance(['filename' => 'usdt_trans']);
        echo "接收到数据：" . $str . "\n";
        echo "OK \n";
        $logFile->Write("info" , $str);
        $BlockTrans = Common::ImportBusiness("BlockTrans" , "Api");
        try{
            $BlockTrans->do_usdt_trans($result);
        }catch(Exception $e ){
            echo  "USDT消息处理失败，错误信息： " . $e->getMessage() . "\n" ;
        }
    }

}