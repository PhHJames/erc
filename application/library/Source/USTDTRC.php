<?php
/**
 * Created by 不要复制我的代码.
 * User: Administrator
 * Date: 2021/1/16 0016
 * Time: 20:35
 */

class Source_USTDTRC{
    public $req_url = '' ;
    public function __CONSTRUCT(){
        global $_G;
        $this->req_url = $_G['config']['domain']['domain']['USDT_API_DOMAIN'];
    }
    /**
     * 获取账户历史TRC20交易记录
     * @return
     */

    public function getTrcTransactionsListByAddress( $address = '' ,$max_num = 200 ){
        $url = $this->req_url . "/GetErc20Trans";
        $resp = $this->sendPostRawReq($url , ['address' => $address ] );
        $result = json_decode($resp , true );
        return $result ;
    }
    /**
     * 判断 USTD 收款地址的格式是否正确
     * @return
     */
    public function validateaddress( $address = '' ){
        $url = $this->req_url . "/isAddress";
        $resp = $this->sendPostRawReq($url , ['address' => $address ] );
        Log_Db::WriteLog("validateaddress" , "检测地址: {$address} 返回：" . $resp ) ;
        $result = json_decode($resp , true );
        $status  = isset($result['data']['status'])?$result['data']['status']: '' ;
        return ($status == 'true' ) ? true : false ;
    }


    /**
     * 查询一个账号的信息, 包括余额, TRC10 余额, 冻结资源, 权限等.
     * @return
     */
    public function GetAccount($address = '' ){
        $url = $this->req_url . "/get_account";
        $data = ['address' => $address ];
        $resp = $this->sendPostRawReq($url , $data );
        Log_Db::WriteLog("GetAccount" , "查询账户: {$address} 返回：" . $resp ) ;
        $result = json_decode($resp , true );
        return $result ;
    }


    /**
     * 查询一个账号的eth余额 和 usdt 余额 调用nodejs服务
     * @return
     */
    public function getAccountMoney($address = ''){

        $url = $this->req_url . "/get_money";
        $data = ['address' => $address ];
        $resp = $this->sendPostRawReq($url , $data );
        Log_Db::WriteLog("getAccountMoney" , "查询账户: {$address} 返回：" . $resp ) ;
        $result = json_decode($resp , true );

        $usdt = isset($result['data']['usdt']) ? $result['data']['usdt'] : 0 ;

        $trx = isset($result['data']['trx']) ? $result['data']['trx'] : 0 ;


        $hash = [
            'usdt' => $usdt ,
            'trx' => $trx ,
        ];
        return $hash ;
    }

    /**
     * 生成一个本地的 TRC20 账号 ， 这个账号是未激活的
     * @return
     */
    public function geneateLocalAccount(){
        $url = $this->req_url . "/generate_address";
        $data = [];
        $resp = $this->sendPostRawReq($url , $data );
        Log_Db::WriteLog("geneateLocalAccount" , "生成一个本地账户失败： 返回：" . $resp ) ;
        $result = json_decode($resp , true );
        return $result ;
    }
    /**
     * 生成一个在线的 TRC20 账号 账号是激活的
     * @return
     */
    public function geneateOnlineAccount($from_address_private = ''){
        $url = $this->req_url . "/generate_address_online";
        $data = ['from_address_private' => $from_address_private ];
        $resp = $this->sendPostRawReq($url , $data );
        Log_Db::WriteLog("geneateOnlineAccount" , "生成一个线上账户失败： 返回：" . $resp ) ;
        $result = json_decode($resp , true );
        return $result ;
    }
    /**
     * 查询交易的信息
     * @return
     */
    public function GetTransactionById( $tx_id = '' ){
        $url = $this->req_url . "/GetTransactionById";
        $data = ['trxid' => $tx_id ];
        $resp = $this->sendPostRawReq($url , $data );
        Log_Db::WriteLog("GetTransactionById" , "查询交易信息失败： 返回：" . $resp ) ;
        $result = json_decode($resp , true );
        return $result ;
    }
    /**
     * 查询交易的信息 根据交易id 查询是否付款成功
     * @return
     */
    public function checkTransIsSuccess( $txid = '' ){
        $info = $this->GetTransactionById( $txid );
        $contractRet = isset($info['data']['result']['status'])?$info['data']['result']['status']:"";
        if($contractRet == '1' ){
            return true ;
        }
        return false ;
    }


    /**
     * Trc20 转账
     * @return
     */
    public function trc20_trans($from_address_private = '' , $fromAddress = '' , $toAddress = '' , $ammout =  0  ){
        $url = $this->req_url . "/erc20_trans";
        $data = [
            'from_address_private' => $from_address_private,
            'fromAddress' => $fromAddress,
            'toAddress' => $toAddress,
            'amount' => $ammout,

        ];
        $resp = $this->sendPostRawReq($url , $data );
        Log_Db::WriteLog("erc20_trans" , "Erc20 转账： 返回：" . $resp ) ;
        $result = json_decode($resp , true );
        return $result ;
    }


    /**
     * erc20 转账 查询预估消耗的ETH
     * @return
     */

    public function estimateEth($fromAddress = '' , $toAddress = '' , $ammout =  0  ){
        $url = $this->req_url . "/estimateEth";
        $data = [
            'fromAddress' => $fromAddress,
            'toAddress' => $toAddress,
            'amount' => $ammout,
        ];
        $resp = $this->sendPostRawReq($url , $data );
        Log_Db::WriteLog("estimateEth" , "Erc20转账预估消耗： 返回：" . $resp ) ;
        $result = json_decode($resp , true );
        return $result ;
    }

    /**
     * TRX 转账
     * @return
     */
    public function trx_trans($from_address_private = '' , $fromAddress = '' , $toAddress = '' , $ammout =  0 ,$all = 0  ){
        $url = $this->req_url . "/eth_trans";
        $data = [
            'from_address_private' => $from_address_private,
            'fromAddress' => $fromAddress,
            'toAddress' => $toAddress,
            'amount' => $ammout,
            'all' => $all

        ];
        $resp = $this->sendPostRawReq($url , $data );
        Log_Db::WriteLog("eth_trans" , "ETH 转账： 返回：" . $resp ) ;
        $result = json_decode($resp , true );
        return $result ;
    }
    public  function sendPostRawReq($url , $data = array()){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,            $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($ch, CURLOPT_POST,           1 );
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_POSTFIELDS,      json_encode($data , JSON_UNESCAPED_UNICODE) );
        curl_setopt($ch, CURLOPT_HTTPHEADER,     array('Content-Type: application/json'));
        $result=curl_exec($ch);
        curl_close($ch);
        return $result ;
    }

    public  function sendPostRawJsonReq($url , $str = '' ){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,            $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($ch, CURLOPT_POST,           1 );
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_POSTFIELDS,      $str);
        curl_setopt($ch, CURLOPT_HTTPHEADER,     array('Content-Type: application/json'));
        $result=curl_exec($ch);
        curl_close($ch);
        return $result ;
    }
}