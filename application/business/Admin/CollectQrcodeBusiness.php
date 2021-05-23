<?php
/**
 * @Author: Awe
 * @Date:   2016-05-15 23:49:50
 * @Last 不要复制我的代码不然后果自负
 * @Last Modified time: 2018-11-08 11:13:56
 */
class CollectQrcodeBusiness extends AbstractModel {
    public function getCollectQrcodeList($params = array(),$pageSize ){
        $page = isset($params['page']) ? $params['page'] : 1 ;
        $field = isset($params['field']) ? $params['field'] : "" ;
        $order = isset($params['order']) ? $params['order'] : "" ;
        $getWhere = " where 1 = 1  ";
        $getWhere.=$this->getCollectQrcodeWhere($params);
        $orderby = " order by a.qr_id desc ";
        if($field && $order ){
            $orderby = " order by {$field} {$order}";
        }
        $sql = "select a.*,b.account,b.name as c_name    from collect_qrcode as a 
        left join collect_user as b on a.user_id = b.user_id    {$getWhere} {$orderby} "   ;
        $sql_count = "select count(*) as c   from collect_qrcode as a 
        left join collect_user as b on a.user_id = b.user_id  {$getWhere} " ;
        return $this->getCommonDataList($sql , $sql_count , $pageSize,$page,0);
    }
    public function getCollectQrcodeWhere($params=array()){
        $getWhere = "";
        if(isset($params['user_id']) && $params['user_id']){
            $getWhere.=" and a.`user_id` = '{$params['user_id']}' ";
        }
        if(isset($params['account']) && $params['account']){
            $getWhere.=" and b.`account` = '{$params['account']}' ";
        }
        if(isset($params['content']) && $params['content']){
            $getWhere.=" and a.`content` = '{$params['content']}' ";
        }
        if(isset($params['status']) AND  $params['status'] != 99){
            $getWhere.=" and a.`status` = '{$params['status']}' ";
        }
        if(isset($params['active']) AND  $params['active'] != 99){
            $getWhere.=" and a.`active` = '{$params['active']}' ";
        }
        if(isset($params['qr_id']) AND $params['qr_id']){
            $params['qr_id'] = intval($params['qr_id']);
            $getWhere.=" and a.`qr_id` = '{$params['qr_id']}' ";
        }
        return $getWhere;
    }
    public function doAddQrCode( $params = []  ){
        $Source_QrCode = new Source_QrCode();
        $CollectQrcodeModel = new CollectQrcodeModel();
        $Source_USTDTRC = new Source_USTDTRC();
        $Source_Account = new Source_Account();
        //$path =  isset($params['path']) ? trim($params['path']) :"" ;
        //$content =  isset($params['content']) ? trim($params['content']) :"" ;
        $c_name =  isset($params['c_name']) ? trim($params['c_name']) :"" ;
        $num =  isset($params['num']) ? intval($params['num']) :1 ;
        $remark =  isset($params['remark']) ? trim($params['remark']) :"" ;
        if( empty($c_name )){
            throw new \Exception("请选择币种");
        }
        $max_num = 500 ;
        if($num > $max_num ){
            throw new \Exception("一次性最多生成{$max_num}个地址");
        }
        $success_num = 0 ;
        $error_num = 0 ;
        $error_msg = '' ;
        for( $i =0 ;$i<$num ;$i++){
            try{
                $qr_id = $this->makeAddressBeatch($c_name  , $remark  );
                if($qr_id > 0 ){
                    $success_num++; ;
                }
            }catch (Exception $e ){
                //echo $e->getMessage();
                $error_num++;
                $error_msg.= $e->getMessage() . "<br>" ;
            }
        }
        return ['success_num' => $success_num , 'error_num' => $error_num ,'error_msg' => $error_msg];
    }


    //生成币地址
    private function makeAddressBeatch($c_name , $remark = '' ){
        $Source_QrCode = new Source_QrCode();
        $CollectQrcodeModel = new CollectQrcodeModel();
        $Source_USTDTRC = new Source_USTDTRC();
        $Source_Account = new Source_Account();
        $addressData = $Source_USTDTRC->geneateLocalAccount();
        if( empty($addressData )){
            throw new \Exception("生成地址失败，请查看系统日志");
        }

        $address = isset($addressData['data']['address']) ? $addressData['data']['address']:"";

        //$address = strtolower($address);

        if( empty($address )){
            throw new \Exception("生成地址失败，请查看系统日志");
        }
        $privateKey = isset($addressData['data']['privateKey']) ? $addressData['data']['privateKey']:"";
        $txid = isset($addressData['data']['result']['txid']) ? $addressData['data']['result']['txid']:"";
        if( empty( $privateKey )){
            throw new \Exception("生成地址失败，没有私钥");
        }


        $info = $CollectQrcodeModel->getInfo(['content' => $address]);
        if( !empty($info) ){
            if( !in_array($info['status'] , [-1 , -2 ] ) ){
                throw new \Exception("充币地址已经存在！！,请更换地址");
            }
        }
        //生成新的二维码
        $qrData = $this->makeAndParsePic( $address);
        $path = isset($qrData['path']) ? $qrData['path'] :"";
        if( empty($path)){
            throw new \Exception("查询解析二维码生成错误。");
        }
        $insert = [
            'content' => $address,
            'path' => $path,
            'create_time' => date("Y-m-d H:i:s"),
            'update_time' => date("Y-m-d H:i:s"),
            'status' => 1 ,
            'chain_name' =>$c_name ,
            'privatekey' => $privateKey ,
            'remark' => $remark ,
            'txid' => $txid,
            'active' => 1 ,

        ];
        $qr_id = $CollectQrcodeModel->insertData($insert);
        if( !$qr_id ){
            return false ;
        }
        return $qr_id;
    }

    //根据图片的远程地址 解析二维码图片的内容 并且重新生成一个图片地址
    private function makeAndParsePic( $text = ''  )
    {
        include_once(APP_PATH . '/application/library/qrcode/phpqrcode.php');
        $errorCorrectionLevel = 'L';//容错级别
        $matrixPointSize = 6;//生成图片大小
        $t = time() . "_" . rand(1000, 99999);
        $folder = APP_PATH . "/public/Uploads/qrcode/";
        if( !file_exists($folder)){
            mkdir($folder , 0777 , true );
        }
        $QR = $folder  . $t . ".new.png";
        \QRcode::png($text, $QR, $errorCorrectionLevel, $matrixPointSize, 2);
        return [
            'text' => $text,
            'path' =>  'Uploads/qrcode/' . $t . ".new.png"
        ];
    }
    public function auto_match( $params ){
        $auto_match = isset($params['auto_match'])?intval($params['auto_match']): 0 ;
        $qr_id = isset($params['qr_id'])?intval($params['qr_id']): 0 ;
        if( empty($qr_id) ){
            throw new Exception("缺少二维码ID");
        }
        $model = new CollectQrcodeModel();
        $info = $model->getInfo( array('qr_id' => $qr_id   ) );
        if( empty($info) ){
            throw new Exception( "没有查询到币地址！！");
        }
        $request = array(
            'auto_match' => $auto_match ,
        );
        $status   = $model->updateData($request , [ 'qr_id' => $qr_id] );
        if( !$status ){
            throw new Exception( "修改失败请稍后");
        }
        return true ;
    }

    public function active( $params ){
        $active = isset($params['active'])?intval($params['active']): 0 ;
        $qr_id = isset($params['qr_id'])?intval($params['qr_id']): 0 ;
        if( empty($qr_id) ){
            throw new Exception("缺少二维码ID");
        }
        $model = new CollectQrcodeModel();
        $info = $model->getInfo( array('qr_id' => $qr_id   ) );
        if( empty($info) ){
            throw new Exception( "没有查询到币地址！！");
        }
        $txid  = $info['txid'];

        $Source_USTDTRC = new Source_USTDTRC();
        $res = $Source_USTDTRC->GetTransactionById($txid);

        $contractRet = isset($res['data']['ret'][0]['contractRet'])?$res['data']['ret'][0]['contractRet']:"";
        if($contractRet != 'SUCCESS' ){
            throw new Exception( "此地址暂未在链上同步，请稍等激活");
        }
        $request = array(
            'active' => $active ,
        );
        $status   = $model->updateData($request , [ 'qr_id' => $qr_id] );
        if( !$status ){
            throw new Exception( "修改失败请稍后");
        }
        return true ;
    }

    /*public function updatePrivate($params = [] ){
        $qr_id = isset($params['qr_id'])?intval($params['qr_id']): 0 ;
        if( empty($qr_id) ){
            throw new Exception("缺少二维码ID");
        }
        $model = new CollectQrcodeModel();
        $info = $model->getInfo( array('qr_id' => $qr_id   ) );
        if( empty($info) ){
            throw new Exception( "没有查询到币地址！！");
        }
        $Source_Ztpay = new Source_Ztpay();
        $result = $Source_Ztpay->get_privatekey($info['chain_name'] , $info['content']);
        $privatekey = isset($result['privatekey']) ? $result['privatekey'] :"";
        if( empty($privatekey) ){
            throw new Exception( "没有获取到私钥地址 请去查看平台日志！！");
        }

        $is_first =  false ;
        if( empty($info['privatekey']) ){
            $is_first = true ;
        }
        $request = array(
            'privatekey' => $privatekey ,
        );
        $status   = $model->updateData($request , [ 'qr_id' => $qr_id] );
        if( !$status ){
            throw new Exception( "修改失败请稍后， 有可能已经有了 无需修改更新");
        }
        $info['is_first'] = $is_first ;
        $info['privatekey'] = $privatekey ;
        return $info ;
    }*/

    public function get_balance($params = []){
        $qr_id = isset($params['qr_id'])?intval($params['qr_id']): 0 ;
        if( empty($qr_id) ){
            throw new Exception("缺少二维码ID");
        }
        $model = new CollectQrcodeModel();
        $info = $model->getInfo( array('qr_id' => $qr_id   ) );
        if( empty($info) ){
            throw new Exception( "没有查询到币地址！！");
        }
        $Source_Ztpay = new Source_USTDTRC();
        $result = $Source_Ztpay->getAccountMoney( $info['content']);
        //更新余额
        $CollectQrcodeModel = new CollectQrcodeModel();
        $CollectQrcodeModel->updateData(
            ['trx' =>$result['trx'] ,
                'usdt' => $result['usdt'] , 'update_time'=>date("Y-m-d H:i:s")  ],['qr_id' => $qr_id]);
        return $result;
    }

    //充值ETH
    public function recharge_trx( $from_address , $qr_id = 0 , $amount = 0 ){
        $Source_Account = new Source_Account();
        $CollectQrcodeModel = new CollectQrcodeModel();
        $AddressTransCommonBusiness = Common::ImportBusiness("AddressTransCommon" ,"Common");
        $fromData = $Source_Account->getTrxByAddress($from_address);
        if( empty($fromData) ){
            throw new \Exception("没有获取到系统ETH账号");
        }
        $private_key = isset($fromData['private_key']) ? $fromData['private_key'] :"";
        if( empty($fromData) ){
            throw new \Exception("没有获取到系统ETH账号私钥");
        }
        $qr_info = $CollectQrcodeModel->getInfo(['qr_id' => $qr_id]);
        $to_address =  $qr_info['content'];
        $remark = "系统内置的ETH充值到用户的钱包里面" ;
        $res = $AddressTransCommonBusiness->trx_trans($private_key , $from_address  , $to_address , $amount,$remark);
        return $res;
    }
    //eth转出
    public function Out_trx( $to_address , $qr_id = 0 , $amount = 0 , $all = 0 ){
        $Source_Account = new Source_Account();
        $CollectQrcodeModel = new CollectQrcodeModel();
        $AddressTransCommonBusiness = Common::ImportBusiness("AddressTransCommon" ,"Common");
        $CollectQrcodeModel = new CollectQrcodeModel();
        $qrInfo = $CollectQrcodeModel->getInfo(['qr_id' => $qr_id ]);
        if( empty($qrInfo) ){
            throw new \Exception("没有获取到二维码币信息");
        }
        $private_key = isset($qrInfo['privatekey']) ? $qrInfo['privatekey'] :"";
        if( empty($private_key) ){
            throw new \Exception("没有获取到私钥");
        }
        if( empty($amount )){
            throw new \Exception("eth币没有无法转出");
        }
        $from_address = isset($qrInfo['content']) ? $qrInfo['content'] :"";
        $remark = "收款币地址里面的ETH转出" ;
        $res = $AddressTransCommonBusiness->trx_trans($private_key , $from_address  , $to_address , $amount,$remark,$all);
        return $res;
    }
    //转出USDT
    public function trans_usdt( $qr_id = 0 ,  $to_address = ''  , $amount = 0 ){
        $Source_Account = new Source_Account();
        $CollectQrcodeModel = new CollectQrcodeModel();
        $AddressTransCommonBusiness = Common::ImportBusiness("AddressTransCommon" ,"Common");
        $qr_info = $CollectQrcodeModel->getInfo(['qr_id' => $qr_id]);
        $from_address =  $qr_info['content'];
        $private_key = isset($qr_info['privatekey']) ? $qr_info['privatekey'] :"";
        if( empty($private_key) ){
            throw new \Exception("没有获取到私钥 二维码的id是：{$qr_id}");
        }
        $remark = "用户钱包里面的USDT转出到归集账户：二维码id：【{$qr_id}】{$to_address}" ;
        $res = $AddressTransCommonBusiness->trc20_trans($private_key , $from_address  , $to_address , $amount,$remark , 5 );
        return $res;
    }


    //计算erc20转账预估消耗的ETH
    public function estimateEth( $params =  []  ){
        $qr_id = isset($params['qr_id']) ? $params['qr_id'] : 0 ;
        $Source_Account = new Source_Account();
        $CollectQrcodeModel = new CollectQrcodeModel();
        $Source_USTDTRC = new Source_USTDTRC();
        $AddressTransCommonBusiness = Common::ImportBusiness("AddressTransCommon" ,"Common");
        $qr_info = $CollectQrcodeModel->getInfo(['qr_id' => $qr_id]);
        $from_address =  $qr_info['content'];
        $private_key = isset($qr_info['privatekey']) ? $qr_info['privatekey'] :"";
        if( empty($private_key) ){
            throw new \Exception("没有获取到私钥 二维码的id是：{$qr_id}");
        }
        $to_address = $Source_Account->account['SummaryAccount'][0]['address'];
        $remark = "" ;
        $usdt = $qr_info['usdt'];
        if($usdt <= 0 ){
            throw new \Exception("当前账户二维码没有币 无法进行预估");
        }
        $res = $Source_USTDTRC->estimateEth($from_address,$to_address , $usdt );
        $data = isset($res['data']) ? $res['data'] : [] ;
        return $data;
    }

}