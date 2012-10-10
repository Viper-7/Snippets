<!doctype html>
<html>
	<head>
		<title>Web Sockets Test</title>
		<script src="http://code.jquery.com/jquery-1.4.2.min.js"></script>
		<script type="text/javascript">
			var socket;
			
			$(function() {
				$('#disconnect').hide();
			
				$('#connect').bind('click', function() {
					$('#output').prepend('<p>connecting</p>');
					
					socket = new WebSocket("ws://home.viper-7.com:8000");
					socket.onopen = function(e) {
						$('#output').prepend('<p>connected</p>');
						$('#disconnect').show();
						$('#connect').hide();
					}
					socket.onmessage = function(e) {
						$('#output').prepend('<p>' + e.data + '</p>');
					};
					socket.onclose = function(e) {
						$('#output').prepend('<p>disconnected</p>');
						$('#connect').show();
						$('#disconnect').hide();
					}
					
				});
				
				$('#go').bind('click', function() {
					var msg = $('#message');
					if(msg.val()) {
						socket.send($('#name').val() + ': ' + msg.val());
						msg.val('');
					}
					return false;
				});
				
				$('#disconnect').bind('click', function() {
					if(typeof(socket.close) != 'undefined') socket.close();
					if(typeof(socket.disconnect) != 'undefined') socket.disconnect();
					return false;
				});
			});
		</script>
	</head>
	<body>
		<div id="content">
			<label>Your Name<input type="text" id="name"/></label><br/>
			<a id="connect" href="#">Connect</a><a id="disconnect" href="#">Disconnect</a><br/>
			<input type="text" id="message"/><input type="button" id="go" value="Say"/><br/>
			<div id="output">&nbsp;</div>
		</div>
	</body>
</html>