// require('socket.io-client');

// class ezviteSocket {
//     constructor(){
//         this.manager = new Manager("localhost")
//     }
//
// }

var ezviteSocket  = io("localhost");
// var myEzviteSocket = new ezviteSocket();

ezviteSocket.on('connect', onConnect);
ezviteSocket.on('connect_error',onConnectError);

function onConnectError(socket){
    console.log("connection error");
}

function onConnect(socket){
    socket.emit('hello', 'can you hear me?', 1, 2, 'abc');
    console.log("socket connected");
}

// $(document).ready(function() {
//
// }
