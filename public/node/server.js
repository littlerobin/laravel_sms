var app = require('express')();
var http = require('http').Server(app);
var io = require('socket.io')(http);
var Redis = require('ioredis');
var envData = require('./env.js');

var channelToSubscribe = '';
if(process.argv && process.argv.length) {
    for (var j = 0; j < process.argv.length; j++) {
        if(j === 2) {
            channelToSubscribe = process.argv[j]
        }
    }
}

var redis = new Redis({
    port: 6379,
    host: envData.REDIS_URL,
    db: envData.REDIS_DB
});

var channels = [
    'update-preview-message',
    'update-verification-message',
    'update-phonenumber-status',
    'user-data-updated',
    'update-message',
    'address-book-updated'
];

if(channelToSubscribe) {
    var channelExist = false;
    for (var i = 0; i < channels.length; i++) {
        if(channels[i] === channelToSubscribe) {
            channelExist = true;
        }
    }
    if(!channelExist) {
        if(channelToSubscribe === 'all' ) {
            console.log('Subscribed to All Channels ');
        } else {
            console.log(channelToSubscribe+' Channel not exist!');
            console.log('Available channels below:');
            console.log('all');
            channels.forEach(function (channel) {
                console.log(channel);
            });
        }
    } else {
        console.log('Subscribed to Channel '+channelToSubscribe+'.');
    }
}

redis.subscribe(channels);

redis.on('message', function(channel, message) {
    message = JSON.parse(message);

    if(channelToSubscribe === channel || channelToSubscribe === 'all') {
        console.log('channel',channel);
        console.log('message',message);
    }

    switch (channel) {
        case 'update-message':
            io.emit('update-message-'+message.data.data.user_id, message.data);
            break;
        default:
            io.emit(channel, message.data);
            break;
    }
});

http.listen(3000, function(){
    console.log('Listening on Port 3000');
});