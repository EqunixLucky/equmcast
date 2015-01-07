var JSON_OK = 0;
var JSON_IDLE = 444;
var JSON_IDLE_GIVEUP = 445;
var MAX_AJAX_TRY = 1;
//var JSON_ENDPOINT = '/ajax/';

function showLoader()
{
	$('#loaderScreen').show();
}

function hideLoader()
{
	$('#loaderScreen').hide();
}

// callbackFx parameters is only 'result' in json format.
function sendRequest(method, params, callbackFx, async)
{	
	if(typeof async == 'undefined') async = true;
	
	console.log(params);
	//var json = $.toJSON(params);
	
	//var path = String(method).replace('^\/', '');
	//var endpath = (method == 'logout') ? "/" + method : JSON_ENDPOINT + path;

	$.ajax({
		url: method,
		async: async,
		cache: false,
		//timeout: 30 * 1000,
		error: function(xhr, status, err)
		{
			console.log('AJAX Error on requesting ' + method);
			//requestError(method, status, err);
		},
		type: 'POST',
		dataType: 'json',
		data: {req: params},
		beforeSend: function(xhr, settings)
		{
			if(method != 'heartbeat') showLoader();
		},
		success: function(data, status, xhr)
		{
			hideLoader();
			var rspcode = data.rspcode;
			var errmsg = data.errmsg;
			if(rspcode != JSON_OK)
			{
				alert('An Error occured: ' + errmsg + ' (' + rspcode + ')');
				return;
			}

			var result = data.result;
			
			if(typeof callbackFx == 'function') callbackFx(result);
		},
		complete: function(xhr, status)
		{
			console.log('ajax complete', status);
			hideLoader();
		}
	})
}


function frameworkInit()
{
	$('.tab-nav li').click(function()
	{
		var pageid = '#page_' + $(this).attr('id');
		$('.mainContent').each(
			function()
			{
				if($(this).attr('active') == 1)
				{
					$(this).hide();
					$(pageid).attr('active','0');
				}
			}
		);
		$(pageid).attr('active','1');
		$(this).siblings().removeClass('tab-active');
		$(this).addClass('tab-active');
		$(pageid).show();
	}
	);

	$('#menu img').click(function()
	{
		var pageid = '#page_' + $(this).attr('id');
		$(pageid).show();
		$(pageid).siblings().hide();

		if ($(this).attr('src').indexOf('_hover.png') == -1)
		{
			$(this).attr('src', $(this).attr('src').replace('.png', '') + '_hover.png');
			console.log($(this));
		}

		$(this).siblings().each(
			function()
			{
				$(this).removeClass('active');
				$(this).attr('src', $(this).attr('src').replace('_hover', ''));
			}
		);
		$(this).addClass('active');
		
		$(pageid).find(":first").click();
	}
	);
	
	$('#logout').bind
	(
		'click',
		function()
		{
			sendRequest
			(
				'logout',
				{'req':'x'},
				function(result){ window.location = result['url']; }
			)
		}
	);

	
	$('#btnChgPass').bind
	(
		'click',
		function()
                {
			$('#chgPassCnt').modal('hide');
			if ($('#newPasswordInput').val() != $('#newPasswordInput2').val())
			{
				alert('Sorry, your password not match! please try again...');
			}
			else if ($('#newPasswordInput').val() == '')
			{
				alert('Sorry, your password should not be empty! please try again...');
			}
			else
			{
				sendRequest(
					'changepassword',
					{
						'password': $('#newPasswordInput').val(),
						'username': $('#usernameInput').val(),
					
					},
					function(result)
					{
						console.log(result);
						alert("Successfully change password, login will be restarted.");
						$('#logout').click();
					}
				);
			}
                }
	);

	swfu = new SWFUpload({
		// Backend Settings
		upload_url: 'fileupload',
		post_params: {'req': '1'},
	
		// File Upload Settings
		file_size_limit : '20 MB',
		file_types : "*.*",
		file_types_description : "Any Type of Files",
		file_upload_limit : '0',
	
		// Event Handler Settings - these functions as defined in Handlers.js
		//  The handlers are not part of SWFUpload but are part of my website and control how
		//  my website reacts to the SWFUpload events.
		file_queue_error_handler 	: fileQueueError,
		file_dialog_complete_handler 	: fileDialogComplete,
		upload_progress_handler 	: uploadProgress,
		upload_error_handler 		: uploadError,
		upload_success_handler 		: uploadSuccess,
		upload_complete_handler 	: uploadComplete,
	
		// Button Settings
		button_image_url:'static/images/fileupload.jpg',
		button_placeholder_id:'spanButtonPlaceholder',
		button_width:240,
		button_height:80,
		//button_text:"<button type='button' class='btn btn-primary' id='btnUploadFile'>Select File to Upload</button>",
		button_text_style : '',
		button_text_top_padding: 0,
		button_text_left_padding: 18,
		button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
		button_cursor: SWFUpload.CURSOR.HAND,
		
		// Flash Settings
		flash_url : "static/swfupload.swf",
	
		custom_settings : {
			upload_target : "divFileProgressContainer"
		},
		
		// Debug Settings
		debug: false
	});
	
	$('.mainContent, .tab-nav').hide();
	
	appsInit();
	
	$('#menu').find(":first").click();
}