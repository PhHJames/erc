/* 
* @Author: Awe
* @Date:   2019-06-20 11:19:05
* @Last 不要复制我的代码不然后果自负
* @Last Modified time: 2019-07-09 10:12:04
* @desc server.js
*/
require("./config/config.js");
const superagent = require('superagent')
var express =require("express");
var app=express();
var bodyParser = require('body-parser');
var util = require("util")
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({extended: true, limit : '50mb' }));
app.use(bodyParser.json({limit: '1mb'}));  //body-parser 解析json格式数据
app.use(express.static(__dirname + "/root"));
app.listen(http_port,http_host  );
console.log( "ERC20服务器监听地址是："  +  http_host +":端口是："+http_port)

const CommonModules = require ('./module/common.js') 



app.all('*', function(req, res, next) {
    res.header("Access-Control-Allow-Origin", "*");
    res.header("Access-Control-Allow-Headers", "X-Requested-With,content-type");
    res.header("Access-Control-Allow-Methods","PUT,POST,GET,DELETE,OPTIONS");
    res.header("X-Powered-By",' my name is qiao daima ')
    res.header("Content-Type", "application/json");
    next();
});
/*
    生成一个不上链的地址
*/
app.post("/generate_address",function (req,res) {
    let  addressModule = require ('./module/address.js') 
    try{
        let addressObj = addressModule.address.generateAddress()
        CommonModules.Common.consoleLog("生成地址成功：，币地址>>>" + addressObj.address + " 私钥>>>>"+ addressObj.privateKey );
        res.send(
            CommonModules.Common.echoJsons(1 , 'ok' , addressObj)
        ) ;
    }catch(e){
        res.send(CommonModules.Common.echoJsons( 0 , "error:" + e) )
    }
    
});


/*
    判断地址是否正确
*/
app.post("/isAddress",function (req,res) {
    let  addressModule = require ('./module/address.js') 
    try{
        var  address = req.body.address  || "" ;//
        if(address == '' ){
            res.send(
                CommonModules.Common.echoJsons( 0  , '缺失地址' )
            ) ;
        }
        let status = addressModule.address.isAddress(address)
        res.send(
            CommonModules.Common.echoJsons(1 , 'ok' , {status:status} )
        ) ;
    }catch(e){
        res.send(CommonModules.Common.echoJsons( 0 , "error:" + e) )
    }
});



/*
    ERC20 转账
*/

app.post("/erc20_trans",function (req,res) {
    try{
        var  from_address_private = req.body.from_address_private  || "" ;//从哪个账号创建 就是那个账号的私钥
        var  fromAddress = req.body.fromAddress  || "" ;//从哪个账号转
        var  toAddress = req.body.toAddress  || "" ;//转给谁
        var  amount = req.body.amount  ||  0  ;//转多少usdt

        if(from_address_private == '' ){
            res.send(
                CommonModules.Common.echoJsons( 0  , '缺失创建者的私钥' )
            ) ;
        }
        if(fromAddress == '' ){
            res.send(
                CommonModules.Common.echoJsons( 0  , '请输入转账账号' )
            ) ;
        }
        if(toAddress == '' ){
            res.send(
                CommonModules.Common.echoJsons( 0  , '缺失目标账号' )
            ) ;
        }

        if(amount ==  0  ){
            res.send(
                CommonModules.Common.echoJsons( 0  , '请输入转账数量' )
            ) ;
        }
        let  usdtModule = require ('./module/concart.js') 

        usdtModule.concart.usdt_trans(fromAddress,from_address_private,toAddress , amount ).then(function(result){
            res.send(
                CommonModules.Common.echoJsons(1 , 'ok' , result)
            ) ;
        },function(e){
            res.send(CommonModules.Common.echoJsons( 0 , "USDT转账error:" + e) )
        });
        
    }catch(e){
        res.send(CommonModules.Common.echoJsons( 0 , "USDT转账error:" + e) )
    }
});


/*
    ERC20 转账 预估消耗的 ETH 
*/


app.post("/estimateEth",function (req,res) {
    try{
        var  fromAddress = req.body.fromAddress  || "" ;//从哪个账号转
        var  toAddress = req.body.toAddress  || "" ;//转给谁
        var  amount = req.body.amount  ||  0  ;//转多少usdt

        if(fromAddress == '' ){
            res.send(
                CommonModules.Common.echoJsons( 0  , '请输入转账账号' )
            ) ;
        }
        if(toAddress == '' ){
            res.send(
                CommonModules.Common.echoJsons( 0  , '缺失目标账号' )
            ) ;
        }

        if(amount ==  0  ){
            res.send(
                CommonModules.Common.echoJsons( 0  , '请输入转账数量' )
            ) ;
        }
        let  usdtModule = require ('./module/concart.js') 

        usdtModule.concart.estimateEth(fromAddress,toAddress , amount ).then(function(result){
            res.send(
                CommonModules.Common.echoJsons(1 , 'ok' , result)
            ) ;
        },function(e){
            res.send(CommonModules.Common.echoJsons( 0 , "获取预估转账消耗的ETH失败了:" + e) )
        });
        
    }catch(e){
        res.send(CommonModules.Common.echoJsons( 0 , "获取预估转账消耗的ETH失败了:" + e) )
    }
});


/*
   eth转账
*/




app.post("/eth_trans",function (req,res) {
    try{
        var  from_address_private = req.body.from_address_private  || "" ;//从哪个账号创建 就是那个账号的私钥
        var  fromAddress = req.body.fromAddress  || "" ;//从哪个账号转
        var  toAddress = req.body.toAddress  || "" ;//转给谁
        var  amount = req.body.amount  ||  0  ;//转多少eth

        var  all = req.body.all  ||  0  ; //是否账户里面的全部转出

        if(from_address_private == '' ){
            res.send(
                CommonModules.Common.echoJsons( 0  , '缺失创建者的私钥' )
            ) ;
        }
        if(fromAddress == '' ){
            res.send(
                CommonModules.Common.echoJsons( 0  , '请输入转账账号' )
            ) ;
        }
        if(toAddress == '' ){
            res.send(
                CommonModules.Common.echoJsons( 0  , '缺失目标账号' )
            ) ;
        }

        if(amount ==  0  ){
            res.send(
                CommonModules.Common.echoJsons( 0  , '请输入转账eth数量' )
            ) ;
        }
      
        let  ethModule = require ('./module/eth.js') 


        ethModule.eth.eth_trans(from_address_private , fromAddress , toAddress , amount ,all ).then(function(data){
            res.send(CommonModules.Common.echoJsons( 1 ,  'ok' , data ) )
        },function(err){
            res.send(CommonModules.Common.echoJsons( 0 , "ETH转账失败 :" + err) )
        })
        
       
    }catch(e){
        res.send(CommonModules.Common.echoJsons( 0 , "转账ETH出错：error:" + e) )
    }
});





async function getMoney(address ){

    let  ethModule = require ('./module/eth.js') 

    let eth = await ethModule.eth.money(address);

    let  usdtModule = require ('./module/concart.js') 

    let usdt = await usdtModule.concart.money(address)

    return {
        'usdt' : usdt,
        'trx' :eth 
    }
}

/*
   获取账户的 eth 余额 和 usdt 余额
*/

app.post("/get_money",function (req,res) {
    try{
        var  address = req.body.address  || "" ;//

        if(address == '' ){
            res.send(
                CommonModules.Common.echoJsons( 0  , '缺失地址' )
            ) ;
        }
        getMoney(address ).then(function(data){
            res.send(CommonModules.Common.echoJsons( 1 ,  'ok' , data ) )
        },function(err){
            res.send(CommonModules.Common.echoJsons( 0 , "获取账户余额 Balance失败:" + err) )
        })
    }catch(e){
        res.send(CommonModules.Common.echoJsons( 0 , "获取账户余额失败:" + e) )
    }
});




/*
   根据地址获取 ERC20的交易数据[注意这个里面包含了转入和转出]
*/

app.post("/GetErc20Trans",function (req,res) {
    try{
        var  address = req.body.address  || "" ;
        if(address == '' ){
            res.send(
                CommonModules.Common.echoJsons( 0  , '缺失地址' )
            ) ;
        }

        let  concartModule = require ('./module/concart.js') 

        concartModule.concart.GetErc20Trans(address,1,1000).then(function(result){
            res.send(CommonModules.Common.echoJsons( 1 ,  'ok' , result ) )
        },function(err){
            res.send(CommonModules.Common.echoJsons( 0 , "根据地址获取erc20历史交易记录失败:" + err) )
        })

    }catch(e){
        res.send(CommonModules.Common.echoJsons( 0 , "根据地址获取erc20历史交易记录失败:" + e) )
    }
});


/*
   获取交易详情  按交易哈希查询交易
*/

app.post("/GetTransactionById",function (req,res) {
    try{
        var  trxid = req.body.trxid  || "" ;//参数 trxid
        if(trxid == '' ){
            res.send(
                CommonModules.Common.echoJsons( 0  , '缺失trxid' )
            ) ;
        }
        let requestData = {
            "value" :trxid
        }
        var requestDatas = JSON.stringify(requestData);

        let url = etherscan_api_url + "/api?module=transaction&action=gettxreceiptstatus&txhash="+trxid+"&apikey="+etherscan_api_keys
        superagent.post(url)
            .accept('application/json')
            .timeout(5000)
            .set('Content-Type', 'application/json')
            .end(function(err, resp) {
                if (err) {
                    res.send(CommonModules.Common.echoJsons( 0 , "按交易哈希查询交易失败:" + err) )
                    return;
                }
                try{
                    let statusCode = resp.statusCode
                    let status = resp.status
                    let text = resp.text || ""
                    let repCode = resp.body.repCode || ""
                    if(statusCode == 200 && status == 200 ){
                        //let body = resp.body || {}
                        if(text){
                            text = JSON.parse(text)
                            //var owner_address = text.raw_data.
                        }

                        res.send(CommonModules.Common.echoJsons( 1 ,  'ok' , text ) )
                    }else{
                        res.send(CommonModules.Common.echoJsons( 0 , "按交易哈希查询交易失败:" + text) )
                    }
                }catch( err ){
                    res.send(CommonModules.Common.echoJsons( 0 , "按交易哈希查询交易失败:" + err) )
                }
            })
    }catch(e){
        res.send(CommonModules.Common.echoJsons( 0 , "按交易哈希查询交易失败:" + e) )
    }
});