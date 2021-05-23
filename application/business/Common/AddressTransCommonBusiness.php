<?php
/**
 * @Author: 不要复制我的代码
 * @Date:   2016-05-15 23:49:50
 * @Last 不要复制我的代码不然后果自负
 * @Last Modified time: 2019-04-26 22:59:32
 * @des 二维码
 */
class AddressTransCommonBusiness extends AbstractModel {
    /**
     * @des ETH 转账
     * $from_private_key 发送人的私钥
     * $from_address 发送人地址
     * $to_address 接收人地址
     * $amount 转账金额
     * $remark 备注
     * $all 是否全部转
     */
    public function trx_trans( $from_private_key = '' ,  $from_address = '' , $to_address = '' , $amount = 0 , $remark = ''  ,$all =0 ){
        $Source_USTDTRC = new Source_USTDTRC();
        $is_status_to_address = $Source_USTDTRC->validateaddress($to_address);
        if(!$is_status_to_address){
            throw new Exception("接收人的地址不正确");
        }

        $is_status_to_address = $Source_USTDTRC->validateaddress($from_address);
        if(!$is_status_to_address){
            throw new Exception("发送人的地址不正确");
        }
        if( empty($from_private_key) ){
            throw new \Exception("发送人的私钥没有查询到");
        }
        if( $amount * pow(10 , NUMBER_FORMAT ) <= 0  ){
            throw new \Exception("转出的币数量格式错误 必须大于0");
        }
        $fromMoneyData = $Source_USTDTRC->getAccountMoney($from_address);
        if( empty($fromMoneyData['trx'] )){
            throw new \Exception("ETH数量不够 无法转出");
        }
        if($fromMoneyData['trx'] < $amount ){
            throw new \Exception("ETH数不够。。。。");
        }
        $res = $Source_USTDTRC->trx_trans($from_private_key , $from_address  , $to_address , $amount , $all );

        $txid = isset($res['data']['transactionHash']) ? $res['data']['transactionHash'] :"";
        $d_result = isset($res['data']['status']) ? $res['data']['status'] : 0 ;
        $code = isset($res['code']) ? $res['code'] : 0 ;
        $messgge = isset($res['msg']) ? $res['msg'] :  '' ;
        if( $code != 1 ){
            throw new \Exception("操作失败：" . $messgge );
        }
        if( empty($txid) ){
            throw new \Exception("ETH转账提交失败， 请查看系统日志相关信息");
        }
        if($d_result != 1 ){
            throw new \Exception("ETH转账提交失败， 请查看系统日志相关信息");
        }
        /*$AddressTransLogModel = new AddressTransLogModel();
        $AddressTransLogModel->addTrans(
            [
                'from' => $from_address ,
                'to' => $to_address ,
                'amount' => $amount ,
                'remark' => "TRX币资金转出($remark)"  ,
                'txid' => $txid ,
                'channel' =>  'TRX'  ,
            ]
        );*/
        return [
            'txid' => $txid,
        ];
    }
    /**
     * @des TRC20 转账
     * $from_private_key 发送人的私钥
     * $from_address 发送人地址
     * $to_address 接收人地址
     * $amount 转账金额
     * $remark 备注
     */
    public function trc20_trans( $from_private_key = '' ,  $from_address = '' , $to_address = '' , $amount = 0 , 
        $remark = '' , $min_trx_num = 1   ){
        $Source_USTDTRC = new Source_USTDTRC();
        $is_status_to_address = $Source_USTDTRC->validateaddress($to_address);
        if(!$is_status_to_address){
            throw new Exception("接收人的地址不正确");
        }

        $is_status_to_address = $Source_USTDTRC->validateaddress($from_address);
        if(!$is_status_to_address){
            throw new Exception("发送人的地址不正确");
        }
        if( empty($from_private_key) ){
            throw new \Exception("发送人的私钥没有查询到");
        }
        if( $amount * pow(10 , NUMBER_FORMAT ) <= 0  ){
            throw new \Exception("转出的币数量格式错误 必须大于0");
        }
        $fromMoneyData = $Source_USTDTRC->getAccountMoney($from_address);
        if( empty($fromMoneyData['usdt'] )){
            throw new \Exception("usdt数量不够无法转出");
        }
        if( empty($fromMoneyData['trx'] )){
            throw new \Exception("ETH数量不够 无法转出,账户里面必须预留 ETH ");
        }
        if($fromMoneyData['trx'] < $min_trx_num ){
            //throw new \Exception("trx数量不够， 能量不足{$min_trx_num}个");
        }
        if( $amount * pow(10 , NUMBER_FORMAT ) <= 0  ){
            throw new \Exception("转出的币数量格式错误 必须大于0");
        }
        if($fromMoneyData['usdt'] < $amount ){
            throw new \Exception("usdt数不够。。。。");
        }
        $res = $Source_USTDTRC->trc20_trans($from_private_key , $from_address  , $to_address , $amount);

        $txid = isset($res['data']['transactionHash']) ? $res['data']['transactionHash'] :"";
        $d_result = isset($res['data']['status']) ? $res['data']['status'] : 0 ;

        $code = isset($res['code']) ? $res['code'] : 0 ;
        $messgge = isset($res['msg']) ? $res['msg'] :  '' ;
        if( $code != 1 ){
            throw new \Exception("操作失败：" . $messgge );
        }

        if( empty($txid) ){
            throw new \Exception("ERC20转账提交失败， 请查看系统日志相关信息");
        }

        if($d_result != 1 ){
            throw new \Exception("ERC20转账提交失败， 请查看系统日志相关信息");
        }
        /*$AddressTransLogModel = new AddressTransLogModel();
        $AddressTransLogModel->addTrans(
            [
                'from' => $from_address ,
                'to' => $to_address ,
                'amount' => $amount ,
                'remark' => $remark  ,
                'txid' => $txid ,
                'channel' =>  'USDT'  ,
            ]
        );*/
        return [
            'txid' => $txid,
        ];
    }

}