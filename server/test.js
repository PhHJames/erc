require("./config/config.js");
var Web3 = require('web3');
var web3 = new Web3();
web3.setProvider(web3_setProvider);

let a = web3.utils.fromWei('50000000000000')


console.log(a)


