/*
    关于合约的
*/

var Web3 = require('web3');
var web3 = new Web3();
web3.setProvider(web3_setProvider);


var Contract = require('web3-eth-contract');

var superagent = require('superagent')


async function balance(address){
    var jsonInterface= CONCART_ABI;
    var concat_address = CONCART_ADDRESS
    var contract = new web3.eth.Contract(jsonInterface,concat_address );
    let balance = await contract.methods.balanceOf(address).call()
    let decimals = await contract.methods.decimals().call()
    balance = balance / Math.pow(10, decimals)
    return balance;
}


async function get_balance(address){
    var jsonInterface= CONCART_ABI;
    var concat_address = CONCART_ADDRESS
    var contract = new web3.eth.Contract(jsonInterface,concat_address );
    let balance = await contract.methods.balanceOf(address).call()
    let decimals = await contract.methods.decimals().call()
    balance = balance / Math.pow(10, decimals)
    return balance;
}


//代币转账

async function usdt_trans(fromAddress , private_key , toAddress , amount ){
    var jsonInterface= CONCART_ABI;
    var concat_address = CONCART_ADDRESS
    let my_money = await get_balance(fromAddress)
    console.log("测试转账")
    balance = amount * Math.pow(10,  6 )

    if( balance > (my_money * Math.pow(10,  6 )) ){
        throw "账户余额不足，目前有："+ my_money + " , 转出："+ amount
    }
    var contract = new web3.eth.Contract(jsonInterface,concat_address );
    let tokenData = await contract.methods.transfer(toAddress, balance).encodeABI()
    
    let nonce = await web3.eth.getTransactionCount(fromAddress)
    let gasPrice = await web3.eth.getGasPrice()
    console.log("gasPrice is " , gasPrice)
    var rawTx = {
        from: fromAddress,
        nonce: nonce,
        gasPrice: gasPrice,//Gas Price 是用户愿意为每个 Gas 支付的价格，单位是 GWEI，1 GWEI = 0.000000001 ETH。
        to: contract.options.address,//如果转的是Token代币，那么这个to就是合约地址
        data: tokenData  //转Token会用到的一个字段
    }
    let gas = await web3.eth.estimateGas(rawTx)
    rawTx.gas = gas
    //矿工费 = 交易消耗的 Gas 数量 * Gas 的价格 = Gas * Gas Price
    
    var _gasPrice = web3.utils.hexToNumber(rawTx.gasPrice);
    
    console.log("_gasPrice is " , _gasPrice)
    
    let all_eth = _gasPrice * gas
    all_eth = all_eth.toString()
    let _eth =web3.utils.fromWei( all_eth  ); //计算出预估消耗的ETH数量


    //获取当前的账户里面有多少个ETH
    let  ethModule = require (__dirname + '/eth.js') 
    let c_eth = await ethModule.eth.money(fromAddress);

    //throw "能量不足，当前账户有："+ c_eth +" 个eth ， 预估消耗："+  _eth
    if( _eth > c_eth ){
        throw "能量不足，当前账户有："+ c_eth +" 个eth ， 预估消耗："+  _eth
    }

    let sign = await web3.eth.accounts.signTransaction(rawTx, private_key)
    let rawTransaction = sign.rawTransaction
    let result = await web3.eth.sendSignedTransaction(rawTransaction)
    return result
}

//usdt 转账预估消耗的ETH数
async function estimateEth(fromAddress  , toAddress , amount ){
    var jsonInterface= CONCART_ABI;
    var concat_address = CONCART_ADDRESS

    let balance = amount * Math.pow(10,  6 )

   
    
    var contract = new web3.eth.Contract(jsonInterface,concat_address );

    let tokenData = await contract.methods.transfer(toAddress, balance).encodeABI()
    
    let nonce = await web3.eth.getTransactionCount(fromAddress)
    let gasPrice = await web3.eth.getGasPrice()

    
    
    var rawTx = {
        from: fromAddress,
        nonce: nonce,
        gasPrice: gasPrice,//Gas Price 是用户愿意为每个 Gas 支付的价格，单位是 GWEI，1 GWEI = 0.000000001 ETH。
        to: contract.options.address,//如果转的是Token代币，那么这个to就是合约地址
        data: tokenData  //转Token会用到的一个字段
    }
    /*console.log(balance ,rawTx )

    return ;*/
    
    let gas = await web3.eth.estimateGas(rawTx)
    rawTx.gas = gas
    //矿工费 = 交易消耗的 Gas 数量 * Gas 的价格 = Gas * Gas Price
    
    var _gasPrice = web3.utils.hexToNumber(rawTx.gasPrice);

    let all_eth = _gasPrice * gas
    all_eth = all_eth.toString()
    let _eth =web3.utils.fromWei( all_eth  ); //计算出预估消耗的ETH数量
    return {
        _eth : _eth 
    }
}


//获取某个地址下面的ERC20代币记录
var GetErc20Trans = function( address , page = 1  ,offset = 200 ) {
    let url = etherscan_api_url+"/api?module=account&action=tokentx&contractaddress="+CONCART_ADDRESS+"&address="+address+"&page="+page+"&offset="+offset+"&sort=desc&apikey="+etherscan_api_keys
    // 返回一个 Promise
    return new Promise(( resolve, reject ) => {
        superagent.get(url)
        .timeout(5000)
        .set('Content-Type', 'application/json')
        .accept('application/json')
        .end(function(err, resp) {
            if (err) {
                reject( err )
                return 
            }
         
            let statusCode = resp.statusCode || ""
            let status = resp.status|| ""
            let text = resp.body.text || ""
            let repCode = resp.body.repCode || ""
            if(statusCode == 200 && status == 200 ){
                let body = resp.body || {}
                
                resolve( body )
                return 
            }
            reject( resp )
        })
    })
}



module.exports.concart = {
    money : balance,
    usdt_trans : usdt_trans,
    GetErc20Trans : GetErc20Trans,
    estimateEth: estimateEth
};