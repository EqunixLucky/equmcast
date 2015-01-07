function appsInit()
{	
	$('#menu_1').bind
	(
		'click',
		function()
		{
			sendRequest
			(
				'/ajax/overview/listupload',
				{'1':'1'},
				function(result)
				{
					overview_drawcanvas(result)
				}
			);
			
		}
	);
	
	$('#dateReport').datepicker({ format: 'mm-dd-yyyy' });
	
	$('#menu_2').bind
	(
		'click',
		function()
		{
			sendRequest
			(
				'/ajax/monitoring/drawcanvas',
				'',
				function(result)
				{
					console.log(result);
					$('#pagetab_2_1').html(result);
				}
			);			
		}
	);
	
	
	$('#BtnfileUploadClose').bind
	(
		'click',
		function()
                {
			$('#menu_1').click();
		}
	);
}

function overview_drawcanvas(result)
{
	$('#pagetab_1_1').html(result['content']);
	
	var regexp = new RegExp('\.start$', 'i');
	var idregex = '\\.(.*)\.start$';

	$('.jobID').each
	(
		function()
		{
			if(regexp.exec($(this).html()))
			{
				$(this).parent().find('.jobControl').html('Stop Job').removeClass('btn-info').addClass('btn-inverse').data('first', 0);
				var idjob = $(this).html().match(idregex);
				if(idjob != null) $(this).data('id', idjob[1]);
				else $(this).data('id', 0);
			}
		}
	)
	
	$('.well .close').unbind('click').click
	(
		function()
		{
			if(!confirm('Are you sure want to abandon this job?')) return false;
			
			var jobname = $(this).parent().find('.jobID').html();
			var thisData = $(this);
			sendRequest
			(
				'/ajax/overview/abandonjob',
				{'jobname':jobname},
				function(result)
				{
					thisData.parent().remove();
				}
			);
		}
	);
	
	$('.jobControl').unbind('click').click
	(
		function()
		{
			var ajaxURL = "/ajax/overview/";
			
			var jobParent = $(this).parent();
			var jobName = jobParent.find('.jobID').html();
			var thisData = $(this);
			var jobID;
			
			switch($(this).html())
			{
			case 'Restart':
				ajaxURL += "restartjob";
				jobID = jobParent.find('.jobID').data('id');
				break;
			case 'Start Job':
				ajaxURL += "startjob";
				jobID = jobName;
				break;
			case 'Stop Job':
				ajaxURL += "stopjob"; 
				$(this).data('first', 0);
				jobID = jobParent.find('.jobID').data('id');
				break;
			}
			
			sendRequest
			(
				ajaxURL,
				{'jobname': jobName, 'jobid': jobID},
				function(result)
				{
					switch(thisData.html())
					{
					case 'Restart':
					case 'Start Job':
						thisData.html('Stop Job').removeClass('btn-info').addClass('btn-inverse');
						jobParent.find('.jobID').data('id', result['jobid']);
						var thisID = thisData.attr('id');
						var spanJob = thisData.parent().find('.jobID');
						var acc = $('<a data-toggle="collapse" data-target="#' + thisID + '_acc" style="cursor: pointer;">');
						acc.html(thisData.parent().find('.jobID').html());
						spanJob.html('').append(acc);
						//console.log($(this).attr('id'));
						
						/* JOB CONTENT */
						var accdiv = $('<div id="' + thisID + '_acc" class="collapse in">');
						accdiv.append('<br>');
						for(idx in result['batchlist'])
						{
							var batch = result['batchlist'][idx];
							var acccontent = $('<div class="alert alert-success">');
							acccontent.html(batch);
							accdiv.append(acccontent);
						}
						thisData.parent().append(accdiv);
						/* JOB CONTENT */
						
						$(".collapse").collapse();
						break;
					case 'Stop Job':
						var spanJob = thisData.parent().find('.jobID');
						var spanJobID = spanJob.find('a').html();
						if(spanJobID == null) spanJobID = spanJob.html();
						spanJob.html(spanJobID);
						thisData.html('Restart').removeClass('btn-inverse').addClass('btn-info');
						$('#' + thisData.attr('id') + '_acc').remove();
						break;
					}
				}
			);
		}
	);
}


function ledControl(devid, ledpos, ledcolor)
{
	var ledh;
	switch(ledcolor)
	{
	case 0:
		ledh = 0;
		break;
	case 1: // GREEN
		ledh = -29;
		break;
	case 2: // YELLOW
		ledh = -44;
		break;
	case 3: // RED
		ledh = -13;
		break;
	}
	
	var ledid = "#";
	switch(devid)
	{
	case 1:
		ledid += 'sprite_prn1_led';
		break;
	case 2:
		ledid += 'sprite_cam1_led';
		break;
	}
	
	$(ledid + ledpos).css({'background-image':'url("/static/apps/2Monitoring/icon_indicator' + ledpos + '.png")', 'background-position' : ledh + 'px 0px'});
}


//daniel
//ledControlA dan ledControlB untuk cover Opr_PrinterA dan Opr_PrinterB
function ledControlA(devid, ledpos, ledcolor)
{
	var ledh;
	switch(ledcolor)
	{
	case 0:
		ledh = 0;
		break;
	case 1: // RED
		ledh = -13;
		break;
	case 2: // GREEN
		ledh = -29;
		break;
	case 3: // YELLOW
		ledh = -44;
		break;
	}
	
	var ledid = "#";
	switch(devid)
	{
	case 1:
		ledid += 'sprite_prn1_led';
		break;
	case 2:
		ledid += 'sprite_cam1_led';
		break;
	}
	
	$(ledid + ledpos + 'A').css('background', 'url("/static/images/icon_indicator' + ledpos + '.png") ' + ledh + 'px 0');
}

function ledControlB(devid, ledpos, ledcolor)
{
	var ledh;
	switch(ledcolor)
	{
	case 0:
		ledh = 0;
		break;
	case 1: // RED
		ledh = -13;
		break;
	case 2: // GREEN
		ledh = -29;
		break;
	case 3: // YELLOW
		ledh = -44;
		break;
	}
	
	var ledid = "#";
	switch(devid)
	{
	case 1:
		ledid += 'sprite_prn1_led';
		break;
	case 2:
		ledid += 'sprite_cam1_led';
		break;
	}
	
	$(ledid + ledpos + 'B').css('background', 'url("/static/images/icon_indicator' + ledpos + '.png") ' + ledh + 'px 0');
}


// PLAYGROUND
function star()
{
	var devid, ledpos, ledcolor;

	devid = [1,2,3];
	ledpos = [1,2,3];
	ledcolor = [1,2,3];
	
	setTimeout(
		function()
		{
			ledControl
			(
				devid[(Math.floor((Math.random()*10) + 1) % 3)],
				ledpos[(Math.floor((Math.random()*10) + 1) % 3)],
				ledcolor[(Math.floor((Math.random()*10) + 1) % 3)]
			);
			star();
		}
	,50);
}
// PLAYGROUND
