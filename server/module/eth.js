
/*
    关于eth的
*/

var Web3 = require('web3');
var web3 = new Web3();
web3.setProvider(web3_setProvider);

async function eth_trans(private_key, from , to , amount ,all   ){
    //let gas = await web3.eth.getGasPrice()
    let wei  = web3.utils.toWei(amount, 'ether');
    let my_money =  await web3.eth.getBalance(from);

    
    let show_money = web3.utils.fromWei(my_money, 'ether');
    if( parseFloat(amount) > parseFloat(show_money) ){
        throw '账户的币不足无法转出,账户目前有：'+show_money +" , 转出："+amount;
    }
    let remark = web3.utils.utf8ToHex("q:84075041")

    let gasPrice = await web3.eth.getGasPrice()


    let gas = 21000 
    
  

    if( all == 1 ){//如果是全部转出
        let cost =  gas *  gasPrice ;
        wei =  my_money - cost ;
        if( wei < 0 ){
            throw  "出错了，账户的ETH币不够矿工费，矿工费："+cost + " , 账户目前："+  my_money
        }
    }


    let rawTx = {
        to: to,
        gas: gas,
        data : '0x',
        gasPrice: gasPrice,//Gas Price 是用户愿意为每个 Gas 支付的价格，单位是 GWEI，1 GWEI = 0.000000001 ETH。
        value : wei 
    }
    //let gas = await web3.eth.estimateGas(rawTx)

    //rawTx.gas = gas 


    //console.log("---------eth trans ")
    //console.log(rawTx)
    //console.log("gas : " , gas , "gasPrice : " , gasPrice )
    //console.log("--------------" , gas * gasPrice , " 剩下："  + (my_money - (gas * gasPrice ))  )
    let sign = await web3.eth.accounts.signTransaction(rawTx, private_key )
    //console.log(sign)
    let rawTransaction = sign.rawTransaction
    let result = await web3.eth.sendSignedTransaction(rawTransaction)
    return result ;
}

async function money( address ){
    let eth = await web3.eth.getBalance(address);
    eth = web3.utils.fromWei(eth)
    return eth 
}

async function getNewBlockTrans(  ){
    let  result =  await  web3.eth.getBlock('latest' ,true)
    return result 
}


function fromWei(wei){
    return web3.utils.fromWei(wei)
}

module.exports.eth = {
    eth_trans :eth_trans ,
    money : money,
    getNewBlockTrans:getNewBlockTrans,
    fromWei:fromWei
};