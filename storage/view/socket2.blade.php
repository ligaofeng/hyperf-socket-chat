<html>
<head>
    <title>{{$title}}</title>
    <meta charset="UTF-8">
    <link rel="shortcut icon" href="//hyperf.wiki/2.0/favicon.ico" />
    <link rel="stylesheet" type="text/css" href="/socket.css">
    <script src="https://cdn.bootcss.com/jquery/2.2.2/jquery.min.js"></script>
    <script src="https://cdn.bootcss.com/socket.io/2.3.0/socket.io.js"></script>
</head>
<body>

<ul id="messages"></ul>
<form action="">
    <input id="m" autocomplete="off" /><button>Send</button>
</form>

<script src="/socket.js"></script>
<script>
    var token = '{!! $token !!}';
    var room_id = {!! $roomId !!}; //房间ID
    socketIo('ws://127.0.0.1:9502', token, room_id);
</script>
</body>
</html>