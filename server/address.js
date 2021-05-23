var Web3 = require('web3');
var web3 = new Web3();
web3.setProvider('https://rinkeby.infura.io/v3/ea0fd95695b24e7e95c3ddd13044f572');
var address = web3.eth.accounts.create();





console.log("币地址："  , address.address );
console.log("私钥："  , address.privateKey )
