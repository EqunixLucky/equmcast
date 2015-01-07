function appInit()
{
	$('#messageIDInput, #messageIDButton').removeAttr('disabled');
	
	var data = {};
	data['printid'] = $('#printID').val();
	
	// hardcoded for a while...
	$('#printID').val('0001').removeAttr('disabled');
	$('#printIP').val('192.168.8.11').removeAttr('disabled');
	$('#camPort').val('3000').removeAttr('disabled');
	$('#mainStatConnect').html('Connect').removeClass('btn-danger').addClass('btn-success');
	$('#uploadFile').hide();
	$('#mainStatConnect').data('notfound', 0);
	$('#plogField').hide();
	
	// GET MESSAGE ID LIST
	/*listMessage(data, function(r){
		for(msg in r.result)
		{
			$('#messageIDInput').append($('<option>').html(r.result[msg]).val(r.result[msg]));
		}
	});*/
	
	$('#messageIDButton').click(function(){
		var data = {};
		data['msgid'] = $('#messageIDInput').val();
		data['printid'] = $('#printID').val();
		setMessage(data, function(r){
			$('#messageIDInput, #messageIDButton').attr('disabled', 'disabled');
		});
	});
	
	$('#mainStatConnect').unbind('click').click(function(){
		var data = {};
		data['printid'] = $('#printID').val();
		data['printip'] = $('#printIP').val();
		data['camport'] = $('#camPort').val();
		var obj = $(this);
		
		if($(this).html() == 'Connect')
		{
			connect(data, function(r){
				$('#printID, #printIP, #camPort').attr('disabled', 'disabled');
				obj.html('Disconnect').removeClass('btn-success').addClass('btn-danger');
				$('#mainCounterReset').removeAttr('disabled');
				$('#uploadFile').show();
			});
		}
		else if($(this).html() == 'Disconnect')
		{
			disconnect(data, function(r){
				/*$('#printID, #printIP, #camPort').removeAttr('disabled');
				obj.html('Connect').removeClass('btn-danger').addClass('btn-success');
				$('#mainCounterReset').attr('disabled', 'disabled');
				$('#uploadFile').hide();*/
				obj.html('Disconnecting');
			});
		}
	});
	
	$('#mainCounterReset').click(function(){
		var data = {};
		data['printid'] = $('#printID').val();
		counterReset(data, function(r){
			$('#printCounterSystem, #printCounterPrinter, #printCounterCam').val('');
		});
	});
	
	$('#fileUploadButton').click(function(){
		$('#messageIDHidden').val($('#messageIDInput').val());
	});
	
	
	$('#startJobButton').click(function(){
		var data = {};
		data['printid'] = $('#printID').val();
		var btn = $(this);
		
		switch(btn.html())
		{
		case 'Start Print':
			startPrint(data, function(r){
				//btn.html('Stop Print').removeClass('btn-success').addClass('btn-inverse');
				btn.attr('disabled', 'disabled');
			});
			break;
		case 'Stop Print':
			stopPrint(data, function(r){
				//btn.html('Start Print').removeClass('btn-inverse').addClass('btn-success');
				btn.attr('disabled', 'disabled');
			});
			break;
		}
	});
					
	_hb.addPayload('heartbeat', data, function(r){
		var res = r.result;
		
		if($('#mainStatConnect').html() == 'Disconnect')
		{
			if(typeof res['printstatus']['notfound'] != 'undefined')
			{
				if($('#mainStatConnect').data('notfound') > 2)
				{
					/*disconnect(data, function(r){
						$('#printID, #printIP, #camPort').removeAttr('disabled');
						$('#mainStatConnect').html('Connect').removeClass('btn-danger').addClass('btn-success');
						$('#mainCounterReset').attr('disabled', 'disabled');
						$('#uploadFile').hide();
					});*/
					$('#content').html('');
					_hb.stop();
					$('#mainStatConnect').data('notfound', 0);
				}
				else
					$('#mainStatConnect').data('notfound', $('#mainStatConnect').data('notfound') + 1);
				
				return;
			}
			else
				$('#mainStatConnect').data('notfound', 0);
		}
		
		/* SYSTEM STATUS */
		/*if(res['mainstatus']['status'] != "")
			$('#statusContent').html(res['mainstatus']['status']);*/
		
		/* PLOG */
		$('#content .well-plog').remove();
		if(res['plog'] != "")
		{
			for(i in res['plog'])
				$('#content').append($('<div class="well well-plog">').html(res['plog'][i]['text']));
		}
		else
			$('#plogField').hide();
		
		
		/* PRINT STATUS */
		if(res['printstatus'] != "")
		{
			$('#printStatus').html('');
			for(key in res['printstatus'])
			{
				switch(key)
				{
				case 'PRTSTAT':
					$('#prtState').html(res['printstatus'][key]);
					if(res['printstatus'][key].toUpperCase().indexOf('WAITING') >= 0)
					{
						$('#startJobButton').html('Stop Print').removeClass('btn-success').addClass('btn-inverse').removeAttr('disabled');
						$('#fileUploadButton').removeAttr('disabled');
					}
					else
					{
						$('#startJobButton').html('Start Print').removeClass('btn-inverse').addClass('btn-success').removeAttr('disabled');
						$('#fileUploadButton').attr('disabled', 'disabled');
					}
					break;
				case 'JETSTAT':
					$('#jetState').html(res['printstatus'][key]);
					break;
				case 'MSGNAME':
					$('#printMsg').html(res['printstatus'][key]);
					break;
				case 'FIELDLEN': $('#printMsgLen').html(res['printstatus'][key]);
					break;
				case 'CNTSYS':
					$('#printCounterSystem').html(res['printstatus'][key]);
					break;
				case 'CNTPRT':
					$('#printCounterPrinter').html(res['printstatus'][key]);
					break;
				case 'CNTCAM':
					$('#printCounterCam').html(res['printstatus'][key]);
					break;
				case 'PRTERROR':
					$('#printStatus').html($('#printStatus').html() + res['printstatus'][key] + '; ');
					break;
				case 'CMDERROR':
					$('#printStatus').html($('#printStatus').html() + res['printstatus'][key] + '; ');
					break;
				case 'JOBSTAT': $('#printStatus').val($('#printStatus').val() + 'Job: ' + res['printstatus'][key] + '; ');
					break;
				case 'STATUS':
					if(res['printstatus'][key] == 'RUNNING')
					{
						$('#mainStatConnect').html('Disconnect').removeClass('btn-success').addClass('btn-danger');
						$('#printID, #printIP, #camPort').attr('disabled', 'disabled');
						$('#uploadFile').show();
					}
					else
					{
						$('#mainStatConnect').html('Connect').removeClass('btn-danger').addClass('btn-success');
						$('#printID, #printIP, #camPort').removeAttr('disabled');
						$('#uploadFile').hide();
					}
					break;
				}
			}
		}
		
		/* JOBS */
		for(i in res['jobs'])
		{
			var id = 'job_' + res['jobs'][i]['fname'];
			id = id.replace(/[\.\s]/gi, "_");
			
			if($('#' + id).length == 0)
			{
				var jobNode = $('<div class="alert alert-success jobnode" id="' + id + '">');
				//jobNode.append($('<div class="jobfname">').html(res['jobs'][i]['fname']));
				//jobNode.append($('<div class="line_separator">'));
				/*jobNode.append($('<div class="jobbutton">').append(
					$('<button class="btn btn-success">')
					.html("Start Job")
					.click(function(){
						var data = {};
						data['printid'] = $('#printID').val();
						data['jobid'] = $('#' + id + ' .jobfname').html();
						var btn = $(this);
						
						switch(btn.html())
						{
						case 'Start Job':
							startJob(data, function(r){
								btn.html('Stop Job').removeClass('btn-success').addClass('btn-inverse');
							});
							break;
						case 'Stop Job':
							stopJob(data, function(r){
								btn.html('Start Job').removeClass('btn-inverse').addClass('btn-success');
							});
							break;
						}
					})
				));*/
				//jobNode.append($('<div class="line_separator">'));
				jobNode.append($('<div class="jobstatus">').html(res['jobs'][i]['status']));
				$('#jobsFieldset').append(jobNode);
			}
			else
			{
				if(res['jobs'][i]['status'] != "")
					$('#' + id + ' .jobstatus').html(res['jobs'][i]['status']);
			}
		}
	});
	
	showLoader();
	
	_hb.run();
}