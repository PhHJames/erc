/*
    关于地址的公共模块
*/


var Web3 = require('web3');
var web3 = new Web3();
web3.setProvider(web3_setProvider);

function generateAddress(){
    var address = web3.eth.accounts.create();
    return {
        address:address.address , 
        privateKey : address.privateKey
    }
}

function isAddress(address){
    return web3.utils.isAddress(address);
}

module.exports.address = {
    generateAddress :generateAddress ,
    isAddress : isAddress
};