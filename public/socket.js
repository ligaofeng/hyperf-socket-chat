Date.prototype.Format = function (fmt) {
    var o = {
        "M+": this.getMonth() + 1, //月份
        "d+": this.getDate(), //日
        "H+": this.getHours(), //小时
        "m+": this.getMinutes(), //分
        "s+": this.getSeconds(), //秒
        "q+": Math.floor((this.getMonth() + 3) / 3), //季度
        "S": this.getMilliseconds() //毫秒
    };
    if (/(y+)/.test(fmt)) fmt = fmt.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
    for (var k in o)
        if (new RegExp("(" + k + ")").test(fmt)) fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
    return fmt;
}


function socketIo(ws, token, room_id) {
    var socket = io(ws, {
        query: {token: token},
        transports: ["websocket"],
        forceNew: false
    });

    socket.on('connect', data => {//including a successful reconnection
        console.log(socket.connected);
        console.log('Socket Id:' + socket.id);

        //监听服务端发送的event消息
        socket.on('event', function (result) {
            console.log('接收到的event数据：');
            console.log(result);

            var jsonData = JSON.parse(result);
            $('#messages').append('<li>' + jsonData.data.time + ' ' + jsonData.data.msg + '</li>');
        });

        //发送event类型的消息给服务端，给房间内的人(除了自己)广播消息
        var emitData = '{"type":1,"data":{"room_id":' + room_id + '}}';
        socket.emit('event', emitData, function (result) {
            console.log('event result：');
            console.log(result);
        });
    });

    $('form').submit(function (e) {
        e.preventDefault(); // prevents page reloading
        var yourmsg = $.trim($('#m').val()); //获取消息输入框中的内容
        if (yourmsg == '') {
            alert('发送的内容不能为空');
            return false;
        }
        var nowtime = new Date().Format("yyyy-MM-dd HH:mm:ss");
        $('#messages').append('<li>' + nowtime + ' You said:' + yourmsg + '</li>');

        var emitData = '{"type":3,"data":{"room_id":' + room_id + ',"msg":"' + yourmsg + '"}}';
        console.log('发送的数据:');
        console.log(emitData);
        console.log(socket);
        socket.emit('event', emitData, function (result) {
            console.log('event result：');
            console.log(result);
            $('#messages').scrollTop(messages.scrollHeight);
        });

        $('#m').val(''); //把消息输入框内容清空
        return false;
    });


    socket.on('disconnect', function () {
        console.log("与服务连接断开");
        //socket.open(); //再重新连接
        // socket.connect();
    });

    socket.on('ping', function () {
        var nowtime = new Date().Format("yyyy-MM-dd HH:mm:ss");
        console.log(nowtime + ":心跳请求已发出");
    });

    socket.on('pong', function () {
        var nowtime = new Date().Format("yyyy-MM-dd HH:mm:ss");
        console.log(nowtime + ":心跳响应已收到");
    });


    socket.on('reconnecting', function () {
        console.log("正在重连....");
    });

    socket.on('reconnect', function () {
        console.log("成功重连");
    });

//连接错误(连接不上socket服务时)，会主动关闭连接，不再重连，即不再调用reconnecting
    socket.on('connect_error', function (error) {
        console.log("连接错误，socket连接关闭");
        socket.close();
    });

    socket.on('connect_timeout', function (error) {
        console.log("连接超时，socket连接关闭");
        socket.close();
    });
}