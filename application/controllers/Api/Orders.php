<?php
/**
 * Created by 不要复制我的代码.
 * User: Administrator
 * Date: 2019/10/19 0019
 * Time: 21:13
 */
class Api_OrdersController extends Api_CommonController
{
    public function init(){
        parent::init();
    }
    public function placeAction(){
        $Business = Common::ImportBusiness("Orders" ,"Api" );
        $params = $this->getParams(1) ;
        $log = Log_file::getInstance( array('filename' => "MerchPlaceOrder" ) );
        $log->Write("info" , "商户请求参数:" . json_encode($params ,JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ));
        global $_G;
        try{
            $data = $Business->placeOrder($params);
            $resp = [
                'order_sn' => $data['order_sn'],
                'pay_url' => $_G['config']['domain']['domain']['API_DOMAIN'] . 'Api_Pay/pay?order_sn='.$data['order_sn'],
                'money' => $data['money'],
                'r_money' => $data['r_money'],
            ];
            $this->echoJson( 1, "下单成功" , $resp );
        }catch(Exception $e ){
            $log->Write("info" , "下单失败:" . $e->getMessage());
            $this->echoJson( $e->getCode(),$e->getMessage() );
        }
    }


    //查询订单
    public function queryAction(){
        $Business = Common::ImportBusiness("Orders" ,"Api" );
        $params = $this->getParams(1) ;
        try{
            $data = $Business->query($params);
            $this->echoJson( 1, "查询成功" , $data );
        }catch(Exception $e ){
            $this->echoJson( $e->getCode(),$e->getMessage() );
        }
    }
}