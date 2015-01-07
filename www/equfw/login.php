<html>
<head>
	<title>EMARKVISION&trade; System</title>
	<link rel='shortcut icon' href='/static/apps/favicon.ico' />
	<script type='text/javascript' src='/static/equfw/ui/jquery.1.7.js'></script>
	<script type='text/javascript' src='/static/equfw/ui/framework.js'></script>
	<script type='text/javascript' src='/static/apps/apps.js'></script>
	
	<style type='text/css'>
		html, body
		{
			font-family: Calibrix, "Segoe UIx", "Helvetica Neue", Helvetica, Arial,  "Lucida Grande", Verdana;
			font-weight:bold;
			background-color: #175db9;
			text-align: center;
			vertical-align: middle;
			font-size: 13px;
		}
		.mainbox 
		{
			display: table;
			background-color: #FFFFFF;
			margin: auto;
			border: 2px solid #C3C3C3;
			border-radius: 15px;
			box-shadow: 5px 5px 5px 0px rgba(214,211,214,1);
		}
		.mainbox > div
		{
			margin: 15px;
		}
		.forminput
		{
			width: 250px;
			font-size: 1.3em;
			border-radius: 4px;
			padding:4px 0;
		}
		.pad
		{
			font-weight: bold;
			height: 50px;
			width: 50px;
			border-radius: 15px;
		}
		.numpad
		{
			font-size: 1.8em;
		}
		.funcpad
		{
			font-size: 13px;
		}
	</style>
	
</head>
<body>
	
	<div class="mainbox">
		<div>
			<table align="center" cellspacing="8">
				<tr>
					<td rowspan="2" align="center"><img src="/static/equfw/img/logo.png" height="100" /></td>
					<td><input class="pad numpad" type="button" value="1" /></td>
					<td><input class="pad numpad" type="button" value="2" /></td>
					<td><input class="pad numpad" type="button" value="3" /></td>
				</tr>
				<tr>
					<td><input class="pad numpad" type="button" value="4" /></td>
					<td><input class="pad numpad" type="button" value="5" /></td>
					<td><input class="pad numpad" type="button" value="6" /></td>
				</tr>
				<tr>
					<td>
						<select class="forminput" id="username" style="margin-bottom:10px;">
							<option>Select Login</option>
						</select>
					</td>
					<td><input class="pad numpad" type="button" value="7" /></td>
					<td><input class="pad numpad" type="button" value="8" /></td>
					<td><input class="pad numpad" type="button" value="9" /></td>
				</tr>
				<tr>
					<td>
						<input id="password" class="forminput" type="password"  value="" placeholder="passcode" />
					</td>
					<td><input id="btnClear" class="pad funcpad" type="button" value="Clear" /></td>
					<td><input class="pad numpad" type="button" value="0" /></td>
					<td><input class="pad funcpad" type="button" value="Del" /></td>
				</tr>
				<tr>
					<td colspan="4"><input id="btnLogin" class="pad" type="button" value="Login" style="width:430px;font-size: 1.8em;" /></td>
				</tr>
			</table>
		</div>
	</div>
	
	<script type='text/javascript'>
		
		$(function() {
			sendRequest(
				'ajax/common/getUserList',
				{},
				function(result)
				{						
					//console.log(result);
					$("#username").html('');
					var optRep = $('<option>').val('0').html('Select Login');
					$("#username").append(optRep);
					for (idx in result) {
					    optRep = $('<option>').val(result[idx]['idlogin']).html(result[idx]['username']);
					    $("#username").append(optRep);
					}
					
				}
			)
		}
		);
		
		
		$('#btnLogin').click(function()
		{
			var params = {};
			params['username'] = $('#username').val();
			params['password'] = $('#password').val();
			
			$.ajax({
				url: '/login',
				async: true,
				cache: false,
				timeout: 30 * 1000,
				error: function(xhr, status, err)
				{
					console.log('AJAX Error on requesting login');
				},
				type: 'POST',
				dataType: 'json',
				data: {req: params},
				beforeSend: function(xhr, settings)
				{
					
				},
				success: function(data, status, xhr)
				{
					console.log(data);
					if(data.rspcode != 0)
						alert(data.errmsg + '(' + data.rspcode + ')');
					else
						window.location = '/';
				},
				complete: function(xhr, status)
				{
					console.log('login ajax complete', status);
				}
			})
			return false;
		});
	
		$('.pad.numpad').click(function() {
			var tmp = $('#password').val();
			tmp += $(this).val();
			$('#password').val(tmp);
		}
		);
		

		//$('#btnLogin').click(function() {
		//	alert($('#password').val());
		//}
		//);
		
		$('#btnClear').click(function() {
			$('#password').val('');
		}
		);
		
    	</script>
</body>
</html>
