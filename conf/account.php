<?php
/**
 * Created by 不要复制我的代码.
 * User: Administrator
 * Date: 2021/1/20 0020
 * Time: 16:08
 */

//系统内置的账号配置

//这个文件非常的危险 千万不要泄露出去了

return [
    'trx' => [
        [
            'private_key' => '0xcb3a318cb8df605ac04520c153790685b801edbb9febf6159bfd374de109aab3' ,//私钥
            'address' => '0x433038D7fA7B79817cB0D63645a7D5B4da3BDFd9' , //币地址
            'name' => '归集矿工费用' ,
            'remark' => 'ETH账号主要是归集或者转账的时候矿工费' ,
        ],
    ] ,//这个账号主要是,为了给其他的账号充值ETH使用的


    'SummaryAccount' => [
        [
            'address' => '0xa64d770f52CAAd197306271001d9F4a4104c9e35' ,
            'name' => '平台汇总账户' ,
            'remark' => '平台汇总账号备注' ,
            'private_key' => '0x1fd6c3366b6d24dc3c459b1321f2ef8d8f4ef42665e9fff59a0517b061c0eb33' ,//私钥
        ],
    ],//汇总账号  ， 这个汇总账号主要是为了 ， 最终把钱打给商户的，


];