/* 
* @Author: Awe
* @Date:   2021-02-02 01:37:02
* @Last Modified by:   Awe
* @Last Modified time: 2021-02-02 02:34:40
* @desc 轮训usdt的交易  监控usdt 最新区块的交易记录
https://etherscan.io/Apis#misc
*/
var fs = require('fs'); // 引入fs模块
require("./config/config.js");
const superagent = require('superagent')
//var fs = require('fs'); // 引入fs模块
const CommonModules = require ('./module/common.js') 

const redis = require('redis');

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
        let result_data = await  queryErc20Trans(1000)

        let result = result_data.result 
        if(result.length > 0 ){
            for(var it in result ){
                let info = result[it]
                let transaction = info.hash
                let from = info['from']
                let to = info['to']
                let value = info['value']
                let tokenDecimal = info['tokenDecimal']
                value = value / Math.pow(10, tokenDecimal)

                let str  = "from："+from + " , to  :" +to +",transaction : "+transaction + ",value:"+value +",other:" + JSON.stringify(info) ;
                var req = {
                    from : from ,
                    to :to ,
                    txID :transaction ,
                    amount : value ,
                    extra : info
                }
                let redis_key = "system_address"
                let from_k_v = await sIsMember(redis_key , from.toLowerCase())
                let to_k_v = await sIsMember(redis_key , to.toLowerCase())

                if(from_k_v != 1 && to_k_v != 1 ){
                    //CommonModules.Common.consoleLog("from地址："+from+",to地址:"+to+",不是系统的 ， 交易id："+transaction+",无需处理。。。")
                    CommonModules.Common.consoleLog("from地址："+from+",to地址:"+to+", tokenDecimal是："+tokenDecimal+" , 数量："+value+" 不是系统的 ， 交易id："+transaction+",无需处理。。。")
                    continue;
                }
                let resp = await sendReq(req)
                console.log(resp)
                CommonModules.Common.consoleLog(str)
            }
        }
    }catch(err){
        console.log("err:" + err )
    }
}



async function queryErc20Trans(  count  ){
    var url =  etherscan_api_url+"/api?module=account&action=tokentx&contractaddress="+CONCART_ADDRESS+"&page=1&offset="+count+"&sort=desc&apikey="+etherscan_api_keys ;
    return new Promise(( resolve, reject ) => {
        try{
            superagent.get(url)
            .accept('application/json')
            .timeout(5000)
            .set('Content-Type', 'application/json')
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
                    let body = resp.body || {}
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


async function sendReq( postData ){
    var url = web_api_domain + "/Api_Rsync/usdt_trans?txID="+postData.txID;
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
