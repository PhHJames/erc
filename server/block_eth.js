/* 
* @Author: Awe
* @Date:   2021-02-02 01:37:02
* @Last Modified by:   Awe
* @Last Modified time: 2021-02-02 02:34:40
* @desc 轮训eth的交易  监控eth 最新区块的交易记录
https://etherscan.io/Apis#accounts
*/
var fs = require('fs'); // 引入fs模块
require("./config/config.js");
const superagent = require('superagent')
//var fs = require('fs'); // 引入fs模块
const CommonModules = require ('./module/common.js') 

const redis = require('redis');


var eth_module = require('./module/eth.js');



var redisClient = redis.createClient(redis_port,redis_host);
if(redis_pass){
    redisClient.auth(redis_pass); 
}
redisClient.select(15);
redisClient.on('error',function(error){
    console.log("redis: " + error);
});




async function sIsMember(key , value ){
    return new Promise( (resolve) => {
        redisClient.sismember(key,value ,function(err, res){
            return resolve(res);
        });
    });
}


async function resJob(){
    try{
        let resultData = await eth_module.eth.getNewBlockTrans()
        let result  = resultData.transactions
       // console.log(resultData)

       //console.log(result)
        if(result.length > 0 ){
            for(var it in result ){
                let info = result[it]
                //console.log("--------------")
                //console.log(info)
                let transaction = info.hash
                let from = info['from']
                let to = info['to']
                if( from == '' || from == null || from == 'null' ){
                    continue ;
                }
                if( to == '' || to == null || to == 'null' ){
                    continue ;
                }
                let wei = info['value']
                if( wei <= 0 ){
                    continue ;
                }
                value = eth_module.eth.fromWei(wei)
                let str  = "from："+from + " , to  :" +to +",transaction : "+transaction + ",value:"+value +",other:" + JSON.stringify(info) ;
                var req = {
                    owner_address : from ,
                    to_address :to ,
                    txID :transaction ,
                    amount : value ,
                    extra : info
                }
                let redis_key = "system_address"
                let from_k_v = await sIsMember(redis_key , from.toLowerCase() )
                let to_k_v = await sIsMember(redis_key , to.toLowerCase())

                if(from_k_v != 1 && to_k_v != 1 ){
                    CommonModules.Common.consoleLog("from地址："+from+",to地址:"+to+", wei是："+wei+" , 数量："+value+" 不是系统的 ， 交易id："+transaction+",无需处理。。。")
                    continue;
                }
                let resp = await sendReq(req)
                console.log(resp)

                console.log(str)
                
            }
        }
    }catch(err){
        console.log("err:" + err )
    }
}






async function sendReq( postData ){
    var url = web_api_domain + "/Api_Rsync/trx_trans?txID="+postData.txID;
    return new Promise(( resolve, reject ) => {
        try{
            superagent.post(url)
            .accept('application/json')
            .timeout(5000)
            .set('Content-Type', 'application/json')
            .send(postData)
            .end(function(err, resp) {
                if (err) {
                    reject( err )
                    return ;
                }
                //console.log(typeof resp.statusCode)
                let statusCode = ""
                if(resp.hasOwnProperty("statusCode") ){
                    statusCode = resp.statusCode
                }
                let status = ""
                if( resp.hasOwnProperty("status")  ){
                    status = resp.status
                }
                //let text = resp.body.text || ""
                //let repCode = resp.body.repCode || ""
                if(statusCode == 200 && status == 200 ){
                    let body = resp.text || {}
                    resolve( body )
                }else{
                    reject( resp )
                }
            })
        }catch(err){
            reject( err )
        }
        
    })
}

resJob()

setInterval(resJob, 3000);//循环执行
