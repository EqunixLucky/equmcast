//MA Enroll system	
var APP_CONFIG = null;

var APP_NAME = "Hyperbio Enrollment";

function appInit()
{
	//return;
	initDeviceFunction();
	initWidgets();
	initTab();
	initCamFunction();
	initEnroll();

	setTimeout(function(){
		FVDevice.init();
	}, 1000);
	
	$(window).hashchange(function(){
		var path = location.hash;
		if(path == '')
			return;
		
		if(path == '#/logout')
		{
			logout();
			return;
		}
		
		loadPage(path);
	});
	$(window).hashchange();
	
	$('#mainNavBar').css('z-index', 900);
	
	$('#displayUserName').text(USER.userInfo.fullname);
	$('title').text(APP_NAME + ' - ' + USER.userInfo.fullname)
	
	$('#btnLogout').click(function(){
		confirm('Keluar dari sistem ?', function(){
			logout();
		});
	});
	
	var tabs = $('li.mainTab');
	var tabsZidx = tabs.length + 1;
	$(tabs).each(function(){
		$(this).css('z-index', tabsZidx--);
	});
	
	$('a', 'li.mainTab').click(function(e){
		if($(e.target).is('img'))
			return false;
		
		$('li.current').removeClass('current');
		$(this).parents('.mainTab').addClass('current');
		
		var tab = $(this).attr('href');
		$('.tab-pane.active').removeClass('active');
		$(tab).addClass('active');
		
		_hb.resetPayload();
		
		if(FVDevice.needReconnect())
			FVDevice.reconnectDevice();
		
		return true;
	});
	
	$('#tabUserHandler').click(function(){
		usersList();
	});
	
	$('#tabAdjudicationHandler').click(function(){
		$('#textCin').html('');
		$('#textName').html('');
		$('#textDob').html('');
		$('#textPhone').html('');
		$('#textEmail').html('');
		$('#accountInfoHolder').html('');
		$('#accountInfoImage').attr('src', FB_IMG_B64);
		$('#alertRegion').removeAttr('class').html('');
		$('#registerBtnMgmt, #deleteBtnMgmt, #blacklistBtnMgmt, #linktoBtnMgmt')
			.attr('disabled', 'disabled').addClass('disabled');
		accountList();
	}).trigger('clickx');
	
	$('#enrollEnrollBtn').click(function(){
		
	});
	
	$("#tabVerifyHandler").click(function(){
		$("#verifyBtn").addClass('disabled');
		$('#verifyFinger').val('');
		$("#verifyCinInput").val('');
		//$("#infoVerifyRegion").attr('class', '').addClass('alert alert-success').html("Silakan masukkan CIN dan Data Jari");
		$("#infoVerifyRegion").html("Silakan Masukkan CIN dan Data Jari Anda");
		$("#infoVerifyImage").html("");
		$('#verifyImg').attr('src', '/static/images/logo_content.png');
		$("#tabVerifyHandler").data("flag", 1);
		$("#tabAuthHandler").data("flag", 0);
		FVDevice.resetAcquireControl();
	});
	
	$("#tabAuthHandler").click(function(){
		$("#authBtn").addClass('disabled');
		$('#authFinger').val('');
		$("#infoAuthRegion").attr('class', '').addClass('alert alert-info').html("Silakan masukkan Data Jari");
		$("#tabAuthHandler").data("flag", 1);
		$("#tabVerifyHandler").data("flag", 0);
		FVDevice.resetAcquireControl();
		FVDevice.startAuthFV(-1);
	});
	
	$('#addUserButton').click(function(){
		$('#mode').val('ADD');
		$('#userID')
		.attr('disabled', 'disabled').val('');
		$('#userName').val('');
		$('#userFullName').val('');
		$('#userEmail').val('').removeAttr('disabled');
		$('#userTelp').val('');
		$('#userIdcard').val('');
		$('#userPass').val('');
		$('#userPassConfirm').val('');
		
		showUserDataForm();
	})
	
	$('#tabEnrollmentHandler').click(function(e){
		setHandImage(0,0);
		clearCam();
		//$('#')
		$('input', '#enrollTabContents').val('');
		$('#enrollBtn').addClass('disabled');
		FVDevice.resetAcquireControl();
		return true;
	}).trigger('click');
	
	$('#tabReportHandler').click(function(r){
		listUsers(function(r)
		{
			var cont = $('#rptUserId').empty().append(
				$('<option>').val('').text('Semua')
			);
			var users = listToArray(r.result);
			for(var i in users)
			{
				var user = users[i];
				$('<option>').text(user.name + '').val(user.id + '').appendTo(cont);
				//$('<option>').text(user.name + '').val(user.name + '').appendTo(cont);
			}
		});
		$('#rptStartDate, #rptStopDate').datepicker('setDate', new Date());
	});
	
	$('#tabCinHandler').click(function(r){
		
	}).trigger('clickx');
	
	$('html').bind('contextmenu', function(){
		hideAppContextMenu();	
		
		// prevent browser context menu
		//return false;
	}).click(function(){
		hideAppContextMenu();
	});
	
	$('#rptParamHandler').click(function(){
		var d = $('#reportParam').data('height');
		//alert(d);
		if(typeof d == 'undefined')
			return;
		
		var h = $('#reportParam').height();
		var theight = d;
		if(h >= d)
			theight = $(this).height();
		
		$('#reportParam').animate({
			height: theight
		});
	});
	
	//showConfigurationForm();
	//$('#reportParam').data('height', $('#reportParam').height());
	//$('#logParam').data('height', $('#logParam').height());
	
	_hb.run();
	
	$("#getReportBtn").click(function(){
		showReport();
	});
	
	getReportType(function(r){
		var box = $("#rptTypeservice");
		//console.log(r);
		var types = r;
		for(var i in types)
			$('<option>').val(i + '').text(types[i] + '').appendTo(box);
	});
	
	$('#accountInfoImage').attr('src', FB_IMG_B64);
	$('#FVMessageContainer').removeClass('alert-info clickable').unbind('click').removeClass('alert-success').addClass('alert-warning').html("Harap Tunggu");
}

function initTab()
{
	// Select Tab List
	//$('.tab-list')
	$('.tab-list')
	
	// Find the List Item .. makes an Array
	.find('li')
	
	// Loops So Each Item gets Assets
	.each(function () {
	
		// Assests for tab look
		var template = '<div class="left-mask">\
					<span></span>\
				</div>\
				<div class="right-mask">\
					<span></span>\
				</div>';
	
		//Append Asset / Template to each List Item
		$(this)
			.append(template);
	
	});


}

function initWidgets()
{	
	$('.inputDate').datepicker({
		dateFormat: 'yy-mm-dd',
		changeYear: true,
		yearRange: "1945:2013",
		defaultDate: new Date()
	}).datepicker('setDate', new Date());
	
	$('.dateWithIcon').datepicker({
		dateFormat: 'yy-mm-dd',
		showOn: 'button',
		buttonImage: '/static/images/calendar.jpg',
		buttonImageOnly: true,
		buttonText: ''
	});
	$('.inputInteger').each(function(){
		if($(this).data('inputinteger') == 1)
			return;
		
		// leave trace to prevent double event-binding
		$(this).css('text-align', 'right').data('inputinteger', 1);
		
		$(this).bind('keypress', function(e){
			var key = (e.which) ? e.which : e.keyCode;
			var chr = String.fromCharCode(key);
			//console.log(key);
			
			// INCOMPLETE, assign for some keys are not correct
			var numbers = /^[0-9]$/;
			if(parseInt(key) == 32) return false;
			
			return parseInt(key) < 42 || numbers.test(chr);
		});
	});
	
	$('.inputDecimal').each(function(){
		if($(this).data('inputDecimal') == 1)
			return;
		
		// leave trace to prevent double event-binding
		$(this).css('text-align', 'right').data('inputDecimal', 1);
		
		$(this).bind('keypress', function(e){
			var key = (e.which) ? e.which : e.keyCode;
			var chr = String.fromCharCode(key);
			
			// INCOMPLETE, assign for some keys are not correct
			var numbers = /^[0-9\.]$/;
			return parseInt(key) < 42 || numbers.test(chr);
		});
	});
	
	$('button, .inputButton').button();
	// format class displayInteger text with thousands separator
	$('.displayInteger').each(function(){
		if($(this).data('displayinteger') == 1)
			return;
		
		$(this).data('displayinteger', 1);
		var text = $(this).text();
		// $(this).text(formatNumber(text)); // GAK NGERTI KENAPA GAK JALAN INI DI IE
	});
	
	// LUCKY PUNYA
	$('#searchNameInput').autocomplete({
		source: function(request, resp){
			if(request.term.length > 10) return;
			
			$.ajax({
				type: 'POST',
				url: "/ajax/maenroll/getnames",
				dataType: "json",
				data: {req: $.toJSON(request)},
				success: function(data) {
					resp($.map(data, function(item){
							return {
								label: item,
								value: item
							}
						})
					)
				}
			});
		}
	})
	.blur(function(e){
		var x = {};
		x.name = $('#searchNameInput').val();
		getaccbyname(x, function(r){
			$('#textCin').html(r.cin);
			$('#textName').html(r.name);
			$('#textDob').html(r.dob);
			$('#textEmail').html(r.email);
			$('#textPhone').html(r.phone);
		});
	});
	
	$('#searchCinInput').autocomplete({
		source: function(request, resp){
			$.ajax({
				type: 'POST',
				url: "/ajax/maenroll/getcin",
				dataType: "json",
				data: {req: $.toJSON(request)},
				success: function(data) {
					resp($.map(data, function(item){
							console.log(item);
							return {
								label: item,
								value: item
							}
						})
					)
				}
			});
		},
		minLength: 3
	})
	.blur(function(e){
		var x = {};
		x.cin = $('#searchCinInput').val();	
		getaccbycin(x, function(r){
			$('#textCin').html(r.cin);
			$('#textName').html(r.name);
			$('#textDob').html(r.dob);
			$('#textEmail').html(r.email);
			$('#textPhone').html(r.phone);
		});
	});
	
	$('#deleteBtnMgmt').click(function(){
		if($(this).is('.disabled'))
			return;

		$("body, html").animate({
			scrollTop: $("#tabAdjudication").offset().top - 100
		});
		
		var dt = $('#adjudicationData').data('data');
		
		var p = {};
		p.id = dt.id;
		p.cin = dt.cin;
		del(p, function(r){
			accountList();
			$('#alertRegion').attr('class', 'blank');
			if(r.result != "1009")
			{
				$('#alertRegion').attr('class', 'alert alert-success').html("CIN " + p.cin + " berhasil dihapus");
				alert("CIN " + p.cin + " berhasil dihapus");
			}
			else
			{
				$('#alertRegion').attr('class', 'alert alert-error').html("CIN " + p.cin + " gagal dihapus");
				alert("CIN " + p.cin + " gagal dihapus");
			}

			$('#tabAdjudicationHandler').click();
		});
	});
	
	$('#registerBtnMgmt').click(function(){
		if($(this).is('.disabled'))
			return;

		$("body, html").animate({
			scrollTop: $("#tabAdjudication").offset().top - 100
		});
		
		var dt = $('#adjudicationData').data('data');
		
		var p = {};
		p.id = dt.id;
		p.cin = dt.cin;
		register(p, function(r){
			accountList();
			$('#alertRegion').attr('class', 'blank');
			if(r.result == "0")
			{
				$('#alertRegion').attr('class', 'alert alert-success').html("CIN " + p.cin + " berhasil didaftarkan");
				alert("CIN " + p.cin + " berhasil didaftarkan");
			}
			else if(r.result == "1006")
			{
				$('#alertRegion').attr('class', 'alert alert-warn').html("CIN " + p.cin + " sudah pernah didaftarkan");
				alert("CIN " + p.cin + " sudah pernah didaftarkan");
			}
			else
			{
				$('#alertRegion').attr('class', 'alert alert-warn').html("CIN " + p.cin + " gagal didaftarkan");
				alert("CIN " + p.cin + " gagal didaftarkan");
			}

			$('#tabAdjudicationHandler').click();
		});
	});
	
	$('#linktoBtnMgmt').click(function(){
		if($(this).is('.disabled'))
			return;

		$("body, html").animate({
			scrollTop: $("#tabAdjudication").offset().top - 100
		});
		
		var linkTo = $('#linkToSelection');
		
		var dt = $('#adjudicationData').data('data');
		
		var p = {};
		p.id = dt.id;
		p.cin = dt.cin;
		p.linkid = $(linkTo).val();
		if(p.cin == '' || p.linkid == '')
		{
			alert("Pilih CIN terlebih dahulu");
			return;
		}

		linkto(p, function(r){
			accountList();
			$('#alertRegion').attr('class', 'blank');
			
			if(r.result != "1009")
			{
				$('#alertRegion').attr('class', 'alert alert-success').html("CIN " + p.cin + " berhasil dihubungkan");
				alert("CIN " + p.cin + " berhasil dihubungkan");
			}
			else
			{
				$('#alertRegion').attr('class', 'alert alert-error').html("CIN " + p.cin + " gagal dihubungkan");
				alert("CIN " + p.cin + " gagal dihubungkan");
			}

			$('#tabAdjudicationHandler').click();
		});
	});
	
	$('#blacklistBtnMgmt').click(function(){
		if($(this).is('.disabled'))
			return;

		$("body, html").animate({
			scrollTop: $("#tabAdjudication").offset().top - 100
		});
		
		var dt = $('#adjudicationData').data('data');
		
		var p = {};
		p.id = dt.id;
		p.cin = dt.cin;
		blacklist(p, function(r){
			accountList();
			$('#alertRegion').attr('class', 'blank');
			if(r.result != "1009")
			{
				$('#alertRegion').attr('class', 'alert alert-success').html("CIN " + p.cin + " berhasil didaftarhitamkan");
				alert("CIN " + p.cin + " berhasil didaftarhitamkan");
			}
			else
			{
				$('#alertRegion').attr('class', 'alert alert-error').html("CIN " + p.cin + " gagal didaftarhitamkan");
				alert("CIN " + p.cin + " gagal didaftarhitamkan");
			}

			$('#tabAdjudicationHandler').click();
		});
	});
	
	$('#enrollBtn').click(function(){
		if($(this).is('.disabled'))
			return;
		
		// DUMMY DATA
		var p = {};
		p.cin = $('#enrollCin').val();
		p.accname = $('#enrollName').val();
		p.dob = $('#enrollDob').val();
		p.phone = $('#enrollPhone').val();
		p.email = $('#enrollEmail').val();
		p.photo = $('#enrollPictureData').val();
		
		p.fv1 = $('#finger1').val();
		p.fv2 = $('#finger2').val();
		p.fv3 = $('#finger3').val();
		p.fv4 = $('#finger4').val();
		
		if(p.cin == '' || p.accname == '')
		{
			alert('Harap isi CIN dan Nama');
			return;
		}
		
		// DUMMY DATA
		identify(p, function(r){			
			
			var matchnum = 0;
			var suspectnum = 0;
			var blacklist = 0;

			var cinMatch = "";
			var matchCinDetails = "";
			var suspectCinDetails = "";

			if(r.result * 1 == MSGSERV_RETVAL_REGALREADY)
			{
				alert("Maaf, CIN telah terdaftar.");
				return;
			}
			if(r.result * 1 == 1009)
			{
				alert("Maaf, gagal didaftarkan, terjadi kesalahan pada system.");
				return;
			}
			clearCam();
			$('#enrollBtn').addClass('disabled');
			FVDevice.resetAcquireControl();
			$('input', '#enrollTabContents').val('');
			
			for(var i = 0; i < r.matchlist.length; i++)
			{
				var cin = "";
				if(r.matchlist[i].match == 1003)
				{
					matchnum++;
					matchCinDetails += '<span class="label label-info">' + r.matchlist[i].cin + '</span>' + '&nbsp;' + r.matchlist[i].name + '&nbsp;<br/>';
				}
				else if(r.matchlist[i].match == 1004)
				{
					suspectnum++;
					suspectCinDetails += '<span class="label label-info">' + r.matchlist[i].cin + '</span>' + '&nbsp;' + r.matchlist[i].name + '&nbsp;<br/>';
				}
				else if(r.matchlist[i].match - 100 == 1003)
				{
					matchnum++;
					blacklist++;
					matchCinDetails += '<span class="label label-inverse">' + r.matchlist[i].cin + '</span>' + '&nbsp;' + r.matchlist[i].name + '&nbsp;<br/>';
				}
				else if(r.matchlist[i].match - 100 == 1004)
				{
					suspectnum++;
					blacklist++;
					suspectCinDetails += '<span class="label label-inverse">' + r.matchlist[i].cin + '</span>' + '&nbsp;' + r.matchlist[i].name + '&nbsp;<br/>';
				}
				cinMatch = r.matchlist[i].cin;
			}

			$('#enrollStatus').attr('class', 'blank').html("");

			var matchmsg = "", suspectmsg = "";
			var matchdetails = "", suspectdetails = "";
			if(matchnum < 1 && suspectnum < 1)
			{
				/*$('#popupForm #popupContainer')
					.html("<p>Pendaftaran Berhasil</p>");

				showForm('#popupForm', 'Pendaftaran', {
					width: 350,
					buttons: {
						OK: function(){
							$(this).dialog('close');
						}
					}
				});*/
				alert("Pendaftaran Berhasil");
				return;
			}
			else
			{
				if(matchnum != 0)
				{
					matchmsg += "Data anda MATCH dengan " + matchnum + " orang lainnya";

				if(matchnum == 1 && suspectnum < 1)
					matchdetails = "<span style=\"margin-left:-50px\">Data anda terhubung dengan CIN: " + matchCinDetails + "</span>";
				else
					matchdetails = matchCinDetails;
				}
				if(suspectnum != 0)
				{
					suspectmsg += "Data anda diduga(SUSPECT) sesuai dengan " + suspectnum + " orang lainnya";
					suspectdetails = suspectCinDetails;
				}
			}

			alert("<p>" + matchmsg + "</p>" + "<div style=\"padding-left:50px;\">" + matchdetails + "</div>" + "<br/><p>" + suspectmsg + "</p>" + "<div style=\"padding-left:50px;\">" + suspectdetails + "</div>");
		});
	});
	
	// TODO: add function to update alert region from device finger status

	$('#verifyBtn').click(function(){
		var p = {};
		p.cin = $('#verifyCinInput').val();

		p.cin = (p.cin + "").replace(/[^0-9]/g, '');
		//p.fv1 = "WSn3ovN9HD65jQdZ38sCauaAGe8xCdbRzSl8JKxSSdq5hI/fbKcNMgZr7eVqpf7Omsnw44gq6F6TLugsshouqfY8YxS8HE6eRNHt9Wn24NcnqMDYKbgZjtynVMUuO80V8VOdxk6kfk0jsx1PcodQrEohlpwcwzvzu0MRlZ0sWAimwKxbgy8vh7evTbLAQMZDqYw6BgExu1oQUIrPXiHgqStoMK2ghpuP5rYSqDDG81mLuRIguwjassxYr3KfRc5Ro1Y3xspieXzDCNKA9+VeTQlge+OJJjoMyx09H+BupOC3Wkl2Z80A1Eb+zfvjKKZYivw1SsCZIRAsqQpxjREscRX7oYcgIoMbZXOBsnhMBLDXtvOJEznwikk+PjlZg6K4";
		p.fv2 = "";
		p.fv3 = "";
		p.fv4 = "";
		
		p.fv1 = $('#verifyFinger').val();
		
		var msg = "";
		if(p.cin != '' && p.fv1 != '')
		{
			$('#infoVerifyRegion').html("Silakan tunggu...");
			verify(p, function(r){
				$('#verifyFinger').val('');
				$('#alertVerifyRegion').attr('class', 'blank').html("");

				FVDevice.resetAcquireControl();

				switch(parseInt(r.result, 10))
				{
					case MSGSERV_RETVAL_MATCHED:
						$('#infoVerifyImage').html("Verifikasi Berhasil");
						$('#verifyImg').attr('src', '/static/images/verifikasi_berhasil.jpg');
						//alert('Verifikasi Berhasil');
						break;
					case MSGSERV_RETVAL_UNMATCHED:
						// TODO: if current auth_mode = 2 then retry with auth_mode = 3 (twice GETM acquiring)
						if($('body').data('authfinger') == -1)
						{
							$('#verifyImg').attr('src', '/static/images/verifikasi_ulangi.jpg');
							$('#infoVerifyImage').html("Maaf, mohon ulangi");
							FVDevice.startAuthFV(-2);
							return;
						}

						$('#verifyImg').attr('src', '/static/images/verifikasi_gagal.jpg');
						$('#infoVerifyImage').html("Verifikasi Gagal");
						//alert('Maaf, jari anda tidak dapat diverifikasi');

						break;
					case MSGSERV_RETVAL_CIN_NOFOUND:
						$('#verifyImg').attr('src', '/static/images/verifikasi_gagal.jpg');
						$('#infoVerifyImage').html("Maaf, CIN tidak dapat ditemukan dalam sistem database kami");
						//alert('Maaf, CIN tidak dapat ditemukan dalam sistem database kami');
						break;
					default:
						$('#infoVerifyImage').html("Verifikasi Gagal");
						$('#verifyImg').attr('src', '/static/images/verifikasi_gagal.jpg');
						alert("Maaf, terjadi kesalahan dalam sistem (" + parseInt(r.result, 10) + ")");
				}
			});
		}
		else if(p.cin != '')
			$('#infoVerifyRegion').html("Silakan Masukkan Data Jari");
		else if(p.fv1 != '')
			$('#infoVerifyRegion').html("Silakan Masukkan CIN");
	});
	
	$('#verifyCinInput').unbind('blur').blur(function(){
		$('#verifyBtn').click();
	});
	
	$('#authBtn').click(function(){
		var p = {};
		//p.fv1 = "WSn3ovN9HD65jQdZ38sCauaAGe8xCdbRzSl8JKxSSdq5hI/fbKcNMgZr7eVqpf7Omsnw44gq6F6TLugsshouqfY8YxS8HE6eRNHt9Wn24NcnqMDYKbgZjtynVMUuO80V8VOdxk6kfk0jsx1PcodQrEohlpwcwzvzu0MRlZ0sWAimwKxbgy8vh7evTbLAQMZDqYw6BgExu1oQUIrPXiHgqStoMK2ghpuP5rYSqDDG81mLuRIguwjassxYr3KfRc5Ro1Y3xspieXzDCNKA9+VeTQlge+OJJjoMyx09H+BupOC3Wkl2Z80A1Eb+zfvjKKZYivw1SsCZIRAsqQpxjREscRX7oYcgIoMbZXOBsnhMBLDXtvOJEznwikk+PjlZg6K4";
		p.fv2 = "";
		p.fv3 = "";
		p.fv4 = "";
		p.fv1 = $('#authFinger').val();
		
		var msg = "";
		
		//$('#camAuthContainer')[0].capture();
		
		if(p.fv1 != '')
		{
			$('#infoAuthRegion').attr('class', 'alert alert-info').html("Silakan tunggu...");
			//setTimeout(function(){
			p.photo = $('#authPictureData').val();
		
			auth(p, function(r){
				$('#authFinger').val('');
				$('#alertAuthRegion').attr('class', 'blank').html("");

				FVDevice.resetAcquireControl();

				switch(parseInt(r.result, 10))
				{
					case MSGSERV_RETVAL_MATCHED:
						/* 	"result":"1003",
							"duration":"73",
							"resulttime":"2013-05-17 10:25:55.507",
							"servid":"152.0",
							"attendance":"attend",
							"matchlist":[
								{"name":"Lucky Haryadi",
								"score":"467",
								"match":"1004",
								"cin":"909090"}
							],
							"transid":"69"}
						*/
						$('#infoAuthRegion').attr('class', 'alert alert-success').html("Terima kasih sudah melakukan absen (" + r.duration + " ms)");
						//alert('Verifikasi Berhasil');
						$('#authInfoName').html(r.matchlist[0].name);
						$('#authInfoCIN').html(r.matchlist[0].cin);
						$('#authInfoDate').html(r.resulttime.substring(0,19));
						if(r.attendance == "1")
							$('#authInfoState').html("IN");
						else if(r.attendance == "0")
							$('#authInfoState').html("OUT");
						else
							$('#authInfoState').html("UNKNOWN");
							
						//$('#authInfoImg').val($('#authPictureData').val());
						$('#authImg').attr('src', '/static/images/verifikasi_berhasil.jpg');
						$('#authInfoImg').attr('src', 'data:image/jpeg;base64,' + $('#authPictureData').val());
						break;
					case MSGSERV_RETVAL_RETRY:
						// TODO: if current auth_mode = 2 then retry with auth_mode = 3 (twice GETM acquiring)
						if($('body').data('authfinger') == -1)
						{
							$('#authImg').attr('src', '/static/images/verifikasi_ulangi.jpg');
							$('#infoAuthRegion').attr('class', 'alert alert-warn').html("Mohon ulangi");
							FVDevice.startAuthFV(-2);
							return;
						}

						//$('#infoAuthRegion').attr('class', 'alert alert-danger').html("Verifikasi Gagal");
						//alert('Maaf, jari anda tidak dapat diverifikasi');

						break;
					case MSGSERV_RETVAL_NOTVALID:
						$('#authImg').attr('src', '/static/images/verifikasi_gagal.jpg');
						$('#infoAuthRegion').attr('class', 'alert alert-danger').html("Maaf jari anda tidak dapat dikenali");
						break;
					case MSGSERV_RETVAL_UNMATCHED:
						// TODO: if current auth_mode = 2 then retry with auth_mode = 3 (twice GETM acquiring)
						if($('body').data('authfinger') == -1)
						{
							$('#infoAuthRegion').attr('class', 'alert alert-warn').html("Mohon ulangi");
							$('#authImg').attr('src', '/static/images/verifikasi_ulang.jpg');
							FVDevice.startAuthFV(-2);
							return;
						}
						$('#authImg').attr('src', '/static/images/verifikasi_gagal.jpg');
						$('#infoAuthRegion').attr('class', 'alert alert-danger').html("Maaf data anda tidak ada dalam sistem");
						break;
					default:
						$('#infoAuthRegion').attr('class', 'alert alert-danger').html("Maaf, terjadi kesalahan dalam sistem (" + parseInt(r.result, 10) + ")");
				}
				
				FVDevice.startAuthFV(-1);
			});
			//}, 1000);
		}
	});
	// LUCKY PUNYA
}

// LUCKY PUNYA
$.fn.dataTableExt.afnFiltering.push(
	function( oSettings, aData, iDataIndex ) {
		var filterString = $('#filterString').html();
		if(filterString == 'Semua')
			return true;
		
		//console.log('row data:', aData);
		
		if (aData[5] == filterString)
			return true;	

		return false;
		// */
	}
);

$('tr.adjurow', '#accountTable').live('click', function(){
	var id = $(this).attr('id');
	var accountId = (id + "").replace(/^row_/, '');
	
	getaccount({id: accountId}, function(r){
		$('#adjudicationData').data('data', r);
		
		$('#registerBtnMgmt, #deleteBtnMgmt, #blacklistBtnMgmt, #linktoBtnMgmt')
		.attr('disabled', 'disabled')
		.addClass('disabled');
		
		var cont = $('#accountInfoHolder').empty();
		switch(parseInt(r.status,10))
		{
		case SUSPECT:
			$('#registerBtnMgmt, #deleteBtnMgmt, #linktoBtnMgmt')
			.removeAttr('disabled').removeClass('disabled');
		break;
		case REG:
			$('#deleteBtnMgmt, #blacklistBtnMgmt')
			.removeAttr('disabled').removeClass('disabled');
		break;
		case BLACKLIST:
			$('#deleteBtnMgmt')
			.removeAttr('disabled').removeClass('disabled');
			$('<span>').addClass('label label-inverse').text('BLACKLIST').appendTo(cont);
			$('<br>').appendTo(cont);
		break;
		case MATCH:
			$('#deleteBtnMgmt')
			.removeAttr('disabled').removeClass('disabled');
		break;
		}
		
		$('#textCin').html(r.cin);
		$('#textName').html(r.name);
		$('#textDob').html(r.dob);
		$('#textPhone').html(r.phone);
		$('#textEmail').html(r.email);
		
		if(r.photo != 1 || r.photofile == '')
			$('#accountInfoImage').attr('src', FB_IMG_B64);
		else
			$('#accountInfoImage').attr('src', 'data:image/jpeg;base64,' + r.photofile);
			
		if(r.linkedto != '')
		{
			$('<span>').text('Cocok Dengan').appendTo(cont);
			$('<div>')
			.css('width', '180px')
			.addClass('well well-small').appendTo(cont).text(r.linkcin);
		}
		
		var linkTo = r.linkto;
		if(linkTo.length > 0)
		{
			var sel = $('<select>').attr('id', 'linkToSelection').appendTo(cont).attr('multiple', 'multiple');
			for(var i in linkTo)
			{
				var l = linkTo[i];
				var opt = $('<option>').addClass('label')
				.text('' + l.cin).val(l.id + '').appendTo(sel);
				
				var cls = (l.matchgrade == 1) ? 'label-success' : 'label-warning';
				$(opt).addClass(cls).click(function(r){
					$('option', sel).removeAttr('selected');
					$(this).attr('selected', 'selected');
				});
			}
		}
		
		$("body, html").animate({
			scrollTop: $("#accountInfo").offset().top
		});
	});
});

function accountList(){
	
	var dtTableUrl = '/ajax/maenroll/listaccount';
	var table = "<table class='table table-condensed table-bordered table-hover' cellspacing=0 width='100%' id='accountTable'><thead><tr><th>ID</th><th>CIN</th><th>Nama</th><th>Email</th><th>Tanggal Daftar</th><th>Status</th></tr></thead><tbody id='listAccountData'></tbody></table>";
	$('#tableAccountContainer').html('');
	$('#tableAccountContainer').html(table);
	
	$('#filterMenu li a').unbind('click').click(function(){
		$('#filterString').html($(this).html());
		$("#filterString").attr('status', $(this).attr('sts'));
		//$('#accountTable').dataTable().fnDraw();
		$('#textCin').html('');
		$('#textName').html('');
		$('#textDob').html('');
		$('#textPhone').html('');
		$('#textEmail').html('');
		$('#accountInfoHolder').html('');
		$('#accountInfoImage').attr('src', FB_IMG_B64);
		$('#alertRegion').removeAttr('class').html('');
		$('#registerBtnMgmt, #deleteBtnMgmt, #blacklistBtnMgmt, #linktoBtnMgmt')
			.attr('disabled', 'disabled').addClass('disabled');
		accountList();
	});
	
	if($('#filterString').attr('status') == '')
		return;
	
	$('#accountTable').dataTable({
		//"sDom": "<'row'<'span5'l><'span4'f>>t<'row'<'span6'i><'span4'p>>",
		//"sPaginationType": "bootstrap",
		'bProcessing': true,
		'bServerSide': true,
		'sAjaxSource': dtTableUrl,
		'sServerMethod': 'POST',
		//'sAjaxDataProp': 'result',
		//'bFilter': false,
		bSort: false,
		'fnServerParams': function(aoData){
			var s = $('li.active', '#filterMenu');
			aoData.push({
				name: 'status',
				value: $('#filterString').attr('status')
			});
			
			var x = {};
			for(var i in aoData)
			{
				var y = aoData[i];
				x[y.name] = y.value + ""
			};
			while(aoData.length > 0)
				aoData.shift();
				
			aoData.push({
				name: 'req',
				value: $.toJSON(x)
			})
		}
	});
	return;
	// */

	listAccount(function(rs){
		var table = "<table class='table table-condensed table-bordered table-hover' cellspacing=0 width='100%' id='accountTable'><thead><tr><th>ID</th><th>CIN</th><th>Nama</th><th>Email</th><th>Tanggal Daftar</th><th>Status</th></tr></thead><tbody id='listAccountData'></tbody></table>";
		$('#tableAccountContainer').html('');
		$('#tableAccountContainer').html(table);
	
		var tableData = $('#listAccountData');
		
		for(var i in rs)
		{
			var dt = rs[i];
			var cl, stat;
			switch(parseInt(dt.status, 10))
			{
				case SUSPECT:cl = 'success';stat = "SUSPECT";break; // unregistered 
				case REG:cl = 'info';stat = "REG";break; // registered 
				case MATCH:cl = 'info';stat = "MATCH";break; // linked to 
				case DELETED:cl = 'error';stat = "DELETED";break; // deleted 
				case CANCELED:cl = 'error';stat = "CANCELED";break; // canceled 
				case MATCHDEL:cl = 'error';stat = "MATCHDEL";break; // link deleted 
				case BLACKLIST:cl = 'warning';stat = "BLACKLIST";break; // blacklist 
				case BLDELETED:cl = 'error';stat = "BLDELETED";break; // blacklist deleted
				default:cl = ''; 
			}

			if(stat == "DELETED" || stat == "CANCELED" || stat == "MATCHDEL" || stat == "BLDELETED") continue;
			
			var row = $('<tr>')		
			.appendTo(tableData)
			.data('rowdata', dt)
			.attr('id', 'adjucationRow_' + dt.id)
			.attr('class', cl)
			.css('cursor', 'pointer')
			.click(function(){
				var dt = $(this).data('rowdata');
				var id = dt.id;

				getaccount({id: id}, function(r){
					$('#adjudicationData').data('data', r);
					
					$('#registerBtnMgmt, #deleteBtnMgmt, #blacklistBtnMgmt, #linktoBtnMgmt')
					.attr('disabled', 'disabled')
					.addClass('disabled');
					
					var cont = $('#accountInfoHolder').empty();
					//$('<span>').addClass('label label-inverse').text('BLACKLIST').appendTo(cont);
					switch(parseInt(r.status,10))
					{
					case SUSPECT:
						$('#registerBtnMgmt, #deleteBtnMgmt, #linktoBtnMgmt')
						.removeAttr('disabled').removeClass('disabled');
					break;
					case REG:
						$('#deleteBtnMgmt, #blacklistBtnMgmt')
						.removeAttr('disabled').removeClass('disabled');
					break;
					case BLACKLIST:
						$('#deleteBtnMgmt')
						.removeAttr('disabled').removeClass('disabled');
						$('<span>').addClass('label label-inverse').text('BLACKLIST').appendTo(cont);
						$('<br>').appendTo(cont);
					break;
					case MATCH:
						$('#deleteBtnMgmt')
						.removeAttr('disabled').removeClass('disabled');
					break;
					}
					
					$('#textCin').html(r.cin);
					$('#textName').html(r.name);
					$('#textDob').html(r.dob);
					$('#textPhone').html(r.phone);
					$('#textEmail').html(r.email);
					
					if(r.photo != 1 || r.photofile == '')
						$('#accountInfoImage').attr('src', FB_IMG_B64);
					else
						$('#accountInfoImage').attr('src', 'data:image/jpeg;base64,' + r.photofile);
						
					if(r.linkedto != '')
					{
						$('<span>').text('Matched To').appendTo(cont);
						$('<div>')
						.css('width', '180px')
						.addClass('well well-small').appendTo(cont).text(r.linkcin);
					}
					
					var linkTo = r.linkto;
					if(linkTo.length > 0)
					{
						var sel = $('<select>').attr('id', 'linkToSelection').appendTo(cont).attr('multiple', 'multiple');
						for(var i in linkTo)
						{
							var l = linkTo[i];
							var opt = $('<option>').addClass('label')
							.text('' + l.cin).val(l.id + '').appendTo(sel);
							
							var cls = (l.matchgrade == 1) ? 'label-success' : 'label-warning';
							$(opt).addClass(cls).click(function(r){
								$('option', sel).removeAttr('selected');
								$(this).attr('selected', 'selected');
							});
						}
					}
					
					$("body, html").animate({
						scrollTop: $("#accountInfo").offset().top
					});
				});
			});
			
			$('<td>').appendTo(row).html(dt.id);
			$('<td>').appendTo(row).html(dt.cin);
			$('<td>').appendTo(row).html(dt.name);
			$('<td>').appendTo(row).html(dt.email);
			$('<td>').appendTo(row).html(dt.creadate);
			$('<td>').appendTo(row).html(stat);
			
			row.data('id', dt.id);
		}
		
		$('#accountTable').dataTable({
			"sDom": "<'row'<'span5'l><'span4'f>>t<'row'<'span6'i><'span4'p>>",
			"sPaginationType": "bootstrap"
		});
		
		$('#filterMenu li a').unbind('click').click(function(){
			$('#filterString').html($(this).html());
			$("#filterString").attr('status', $(this).attr('status'));
			$('#accountTable').dataTable().fnDraw();
		});
		
		/*showForm('#userForms', 'User Management', {
			width: 800
		});*/
	})
}

// LUCKY PUNYA

function showForm(frm, title, opt)
{
	if(!$(frm).length)
	{
		console.log('showForm() gagal, form tidak tersedia.');
		return;
	}
	
	if(typeof opt == 'undefined') opt = {};
	
	var prt = $(frm).parents()[0];
	
	var _opt = jQuery.extend({
		modal: true,
		title: title,
		width: 'auto',
		resizable: false,
		draggable: false,
		create: function(e, ui){
			var pane = $(".ui-dialog-buttonpane");
			$('button', pane).addClass('btn btn-primary');
		},
		close: function(){
			$($(this).dialog('widget')).remove();
			var prt = $(this).data('base-parent');
			if($(prt).length < 1)
				return;
			
			$(this).appendTo(prt).hide();
		}
	}, opt);
	
	$(frm).data('base-parent', prt).show().dialog(_opt).css('height', 'auto');
}

function setDataTable(table)
{
	$(table).dataTable({
		bSort: false
	})
}

function emailToNickName(email)
{
	var s = String(email).replace(/\@/g, '_');
	s = s.replace(/[^a-zA-Z0-9_]/g, '');
	
	return s;
}

function showConfigurationForm()
{
	function doShow(){
		// apply the default values of configuration
		$('#confMaxcinperworker').val(APP_CONFIG.maxcinperworker);
		$('#confAmthttpworker').val(APP_CONFIG.amthttpworker);
		$('#confPorthttp').val(APP_CONFIG.porthttp);
		$('#confRbaseport').val(APP_CONFIG.rbaseport);
		$('#confThold_match').val(APP_CONFIG.thold_match);
		$('#confThold_rough').val(APP_CONFIG.thold_rough);
		$('#confMaxfinger').val(APP_CONFIG.maxfinger);
		
		$('option', '.matcherRule').remove();
		
		var selectors = $('.matcherSelector');
		$('tr', selectors).remove();
		$(selectors).each(function(){
			var self = this;
			var s = $(self).attr('s');
			for(var i = 0; i < 5; i++)
			{
				var row = $('<tr>').appendTo(self);
				var cls = s + 'SelectorRules';
				
				var cell = $('<td align="center">').appendTo(row);
				
				$('<span>').text('Finger match ').appendTo(cell);
				
				var select = $('<select>').addClass('matcherRule matchRule input-mini').addClass(cls).appendTo(cell);
				for(var j = 0; j <= APP_CONFIG.maxfinger; j++)
					$('<option>').val(j).text(j).appendTo(select);
				
				cell = $('<td align="center">').appendTo(row);
				$('<span>').text(' & Rough ').appendTo(cell);
				var select = $('<select>').addClass('matcherRule suspectRule input-mini').addClass(cls).appendTo(cell);
				for(var j = 0; j <= APP_CONFIG.maxfinger; j++)
					$('<option>').val(j).text(j).appendTo(select);
					
				$('<span>').text(' Found').appendTo(cell);
			}
		});
		
		var doRenderRule = function(rule, container)
		{
			var row = $('<tr>').appendTo(container);
			$('<td>').addClass('rules').appendTo(row).text(rule);
			var removeBtn = $('<span>').addClass('btn btn-mini btn-danger').text('remove').click(function(){
				$(this).parents('tr').remove();	
			});
			$('<td>').appendTo(row).append(removeBtn);
		}
		
		var renderRules = function(rules, container)
		{
			$(container).empty();
			for(var i in rules)
			{
				var rule = rules[i];
				doRenderRule(rule, container);
			}
		}
		
		var fingerRule = new String(APP_CONFIG.fingerrule).split(';');
		var offset = 0;
		var mtable = $("#matchRules");
		$('tr', mtable).each(function(){
			var rule = fingerRule[offset++].split('-');
			var m = rule[0], s = rule[1];
			$('select.matchRule', this).val(m);
			$('select.suspectRule', this).val(s);
		})
		
		var stable = $("#suspectRules");
		$('tr', stable).each(function(){
			var rule = fingerRule[offset++].split('-');
			var m = rule[0], s = rule[1];
			$('select.matchRule', this).val(m);
			$('select.suspectRule', this).val(s);
		});		
		
		$('.matcherRule').change(function(){
			var v = $(this).val();
			if(v == 0)
				$(this).css('backgroundColor', '#EEE');
			else
				$(this).css('backgroundColor', '#FFF');
		}).trigger('change');
		
		//$('#matchRuleAddBtn').unbind('click').click(function(e){
		//	var m = $("#matchRuleMatch").val();
		//	var r = $("#matchRuleRough").val();
		//	doRenderRule(m + "M" + r + "R", '#matchRuleContainer');
		//});		
		//
		//$('#suspectRuleAddBtn').unbind('click').click(function(e){
		//	var m = $("#suspectRuleMatch").val();
		//	var r = $("#suspectRuleRough").val();
		//	doRenderRule(m + "M" + r + "R", '#suspectRuleContainer');
		//});
		
		showForm('#configurationForm', 'Konfigurasi Sistem', {
			buttons: {
				Apply: function(){
					doSaveConfig();
				},
				Propagate: function(){
					//alert('do propagate!');
					//propagate();
					propagate(function(r){
						console.log(r);
						if(r.result != 0)
						{
							alert(r.errmsg);
							return;
						}
						alert("Propagate berhasil");
					});
				}
			}
		});
	}
	
	if(APP_CONFIG == null)
	{
		loadConfig(function(r){
			var conf = listToArray(r.result);
			APP_CONFIG = conf[0];
			
			if(typeof APP_CONFIG == 'undefined')
			{
				APP_CONFIG = {};
				alert('Tidak dapat mendapatkan konfigurasi.');
			}
			
			doShow();
		});
	}
	else
		doShow();
}

function doSaveConfig()
{
	var collectForm = function(){
		var p = {};
		p.maxcinperworker = $('#confMaxcinperworker').val();
		p.amthttpworker = $('#confAmthttpworker').val();
		p.thold_match = $('#confThold_match').val();
		p.thold_rough = $('#confThold_rough').val();
		
		var tholdBound = function(n)
		{
			return n >= 200 && n <= 1000;
		}
		
		if(!tholdBound(p.thold_match) || !tholdBound(p.thold_rough))
		{
			alert('Threshold harus diantara 200 dan 1000');
			return null;
		}
		
		var sysMatchRules = [];
		var sysSuspectRules = [];
		$('.matcherSelector').each(function(){
			var s = $(this).attr('s');
			$('tr', this).each(function(){
				var match = $('.matchRule', this).val();
				var rough = $('.suspectRule', this).val();
				if(s == 'match')
					sysMatchRules.push(match + '-' + rough)
				else
					sysSuspectRules.push(match + '-' + rough);
			});
		});
		
		p.matchrule = sysMatchRules.join(';');
		p.suspectrule = sysSuspectRules.join(';');
		
		return p;
	}
	
	var p = collectForm();
	if(p == null)
		return;
	
	saveConfig(p, function(r){
		if(r.status != 0)
		{
			alert('Gagal menyimpan konfigurasi');
			return;
		}
		
		loadConfig(function(r){
			var conf = listToArray(r.result);
			APP_CONFIG = conf[0];
		});
		
		$('#configurationForm').dialog('close');
	});
}

function showHostForm(h)
{
	$('input', '#hostForm').val('').removeAttr('disabled');
	var edit = h != null;
	if(edit)
	{
		$('#hostId').val(h.id).attr('disabled', 'disabled');
		$('#hostIpaddress').val(h.ipaddr);
		$('#hostIpaddress2').val(h.ipaddr2);
		$('#hostIpaddress3').val(h.ipaddr3);
		$('#hostUdpport').val(h.portudp);
		$('#hostColor').val(h.hostcolor).trigger('change');
		$('#hostLabelid').val(h.labelid);
		$('#hostMacaddress').val(h.macaddr);
		$('#hostSerialnumber').val(h.serialno);
		$('#hostBrandtype').val(h.brandtype);
	}
	
	$('#hostColor').trigger('change');
	
	showForm('#hostForm', 'Properti Host', {
		buttons: {
			Save: function(r){
				var p = {};
				p.hostid = $('#hostId').val();
				p.ip = $('#hostIpaddress').val();
				p.ip2 = $('#hostIpaddress2').val();
				p.ip3 = $('#hostIpaddress3').val();
				//p.udp = $('#hostUdpport').val();
				p.color = $('#hostColor').val();
				p.label = $('#hostLabelid').val();
				p.mac = $('#hostMacaddress').val();
				p.serial = $('#hostSerialnumber').val();
				p.brand = $('#hostBrandtype').val();
				
				var action = (edit) ? editHost : saveHost;
				
				action(p, function(r){
					if(r.rowAffected < 1)
					{
						alert(r.error);
						return;
					}
					
					$('#hostForm').dialog('close');
					//alert('saved');
					renderHostTab();
				});
			}
		}
	});
}

function hideAppContextMenu()
{
	$('.appContextMenu').remove();
}

function hostContextMenu(host, e)
{
	hideAppContextMenu();
	var d = $('<div>').addClass('dropdown appContextMenu').appendTo('body');
	
	var m = $('<ul>').addClass('dropdown-menu').attr('role', 'menu');
	
	$('<li>').append(
		$('<div>').text('Host ' + host.id).addClass('contextMenuTitle')
	).appendTo(m);
	
	var n = $('<li>').appendTo(m);
	$('<a>')
	.text('Edit')
	.appendTo(n)
	.attr({
		href: '#',
		tabindex: '-1'
	})
	.click(function(r){
		hideAppContextMenu();
		
		showHostForm(host);
		return false;
	});
	
	n = $('<li>').appendTo(m);
	$('<a>')
	.text('Delete')
	.appendTo(n)
	.attr({
		href: '#',
		tabindex: '-1'
	})
	.click(function(r){
		hideAppContextMenu();
		
		confirm('Hapus host ' + host.id + ' ?', function(r){
			host.hostid = host.id;
			deleteHost(host, function(r){
				console.log(r);
				if(r.result == 0)
					$('#hostBox_' + host.id).remove();
				else
					alert('ERROR: ' + r.errmsg);
			});
		})
		
		return false;
	});
	
	$(m).css('display', 'block').appendTo(d);
	$(d).css({
		position: 'absolute',
		left: e.pageX,
		top: e.pageY
	});
}

function workerContextMenu(w, e)
{
	hideAppContextMenu();
	var d = $('<div>').addClass('dropdown appContextMenu').appendTo('body');
	
	var m = $('<ul>').addClass('dropdown-menu').attr('role', 'menu');
	
	$('<li>').append(
		$('<div>').text('Worker ' + w.id).addClass('contextMenuTitle')
	).appendTo(m);
	
	var n = $('<li>').appendTo(m);
	$('<a>')
	.text('Restart')
	.appendTo(n)
	.attr({
		href: '#',
		tabindex: '-1'
	})
	.click(function(r){
		hideAppContextMenu();
		w.workerid = w.id;
		restartWorker(w, function(r){
			
		});
		
		return false;
	});
	
	n = $('<li>').appendTo(m);
	$('<a>')
	.text('Stop')
	.appendTo(n)
	.attr({
		href: '#',
		tabindex: '-1'
	})
	.click(function(r){
		hideAppContextMenu();
		w.workerid = w.id;
		stopWorker(w, function(r){
			
		});
		
		return false;
	});
	
	//n = $('<li>').appendTo(m).addClass('disabled');
	//$('<a>')
	//.text('Ping')
	//.appendTo(n)
	//.attr({
	//	href: '#',
	//	tabindex: '-1'
	//})
	//.click(function(r){
	//	hideAppContextMenu();
	//	w.workerid = w.id;
	//	//stopWorker(w, function(r){
	//	//	
	//	//});
	//	
	//	return false;
	//});
	
	n = $('<li>').appendTo(m).addClass('disabled');
	$('<a>')
	.text('Transfer')
	.appendTo(n)
	.attr({
		href: '#',
		tabindex: '-1'
	})
	.click(function(r){
		hideAppContextMenu();
		
		return false;
	});
	
	n = $('<li>').appendTo(m);
	$('<a>')
	.text('Destroy')
	.appendTo(n)
	.attr({
		href: '#',
		tabindex: '-1'
	})
	.click(function(r){
		hideAppContextMenu();
		
		if($(this).parents('li').is('.disabled'))
			return false;
		
		w.workerid = w.id;
		deleteWorker(w, function(r){
			//console.log('delete worker result: ', r);
			if(r.result == 0)
				$('#workerIcon_' + w.id).remove();
			else
				alert(r.errmsg);
		});
		
		return false;
	});
	if(w.status != WORKER_OFFLINE && w.status != 0)
		$(n).addClass('disabled');
	
	$(m).css('display', 'block').appendTo(d);
	$(d).css({
		position: 'absolute',
		left: e.pageX,
		top: e.pageY
	});
}

function clusterContextMenu(data, e)
{
	if($(e.target).is('.workerIcon') || $(e.target).is('.workerIcon > *'))
		return;
	
	hideAppContextMenu();
	var w = data;
	var d = $('<div>').addClass('dropdown appContextMenu').appendTo('body');
	
	var m = $('<ul>').addClass('dropdown-menu').attr('role', 'menu');
	
	$('<li>').append(
		$('<div>').text('Cluster ' + data.id).addClass('contextMenuTitle')
	).appendTo(m);
	
	var n = $('<li>').appendTo(m);
	$('<a>')
	.text('Refresh')
	.appendTo(n)
	.attr({
		href: '#',
		tabindex: '-1'
	})
	.click(function(r){
		hideAppContextMenu();
		w.clustid = w.id;
		refreshCluster(w, function(r){
			
		});
		
		return false;
	});
	
	n = $('<li>').appendTo(m);
	$('<a>')
	.text('Pause')
	.appendTo(n)
	.attr({
		href: '#',
		tabindex: '-1'
	})
	.click(function(r){
		hideAppContextMenu();
		w.clustid = w.id;
		pauseCluster(w, function(r){
			
		});
		
		return false;
	});
	
	n = $('<li>').appendTo(m);
	var del = $('<a>')
	.text('Stop Cluster')
	.appendTo(n)
	.attr({
		href: '#',
		tabindex: '-1'
	})
	.click(function(r){
		hideAppContextMenu();
		w.clustid = w.id;
		stopCluster(w, function(r){
			
		});
		
		return false;
	});
	
	n = $('<li>').appendTo(m);
	var del = $('<a>')
	.text('Restart Cluster')
	.appendTo(n)
	.attr({
		href: '#',
		tabindex: '-1'
	})
	.click(function(r){
		hideAppContextMenu();
		w.clustid = w.id;
		restartCluster(w, function(r){
			
		});
		
		return false;		
	});
	
	n = $('<li>').appendTo(m);
	var del = $('<a>')
	.text('Destroy')
	.appendTo(n)
	.attr({
		href: '#',
		tabindex: '-1'
	})
	.click(function(r){
		hideAppContextMenu();
		if($(this).parents('li').is('.disabled'))
			return;
		
		confirm('Delete cluster ' + data.id + ' ?', function(r){
			data.clustid = data.id;
			deleteCluster(data, function(r){
				if(r.result == 0)
					$('#clusterBox_' + w.id).remove();
				else
					alert(r.errmsg);
			});
		})
	});
	if($('.workerIcon', $('#clusterBox_' + w.id)).length > 0)
		$(n).addClass('disabled').attr('disabled', 'disabled');
	
	$(m).css('display', 'block').appendTo(d);
	$(d).css({
		position: 'absolute',
		left: e.pageX,
		top: e.pageY
	});
}

// tab cluster
function renderCluster (c, cont)
{
	var divID = 'clusterBox_' + c.id;
	var d = $('<div>').addClass('clusterBox').data('cluster', c);
	$(d).attr('id', divID);
	
	var topToolbar = $('<div>').addClass('clusterTopbar');
	$(topToolbar).appendTo(d);
	var addBtn = $('<img>').addClass('clickable clusterAddWorkerBtn').attr('src', '/static/images/add.png').prependTo(topToolbar);
	
	$(addBtn).click(function(){
		var d = $(this).parents('div.clusterBox');
		var c = $(d).data('cluster');
		
		showAddWorkerForm(c, $('.clusterWorker', d));
	});
	
	// [clusterid] clustername ipbloc tcp:tcpport udp:udpport
	var cname = ['CLUSTER ', '[' + c.id + ']', c.name, c.ipblock, 'HostPort:' + c.hostport, 'RespPort:' + c.respport].join(' ');
	$('<span>').appendTo(topToolbar).text(cname);
	
	$('<div>').addClass('clusterSplashInfo').text(' ').appendTo(topToolbar);
	setClusterSplash(d, c.splashtext);
	
	var workers = $('<div>').addClass('clusterWorker');
	$(workers).appendTo(d);
	
	$(d).appendTo(cont);
	
	renderClusterWorker(c, workers);
	$(d).bind('contextmenu', function(e){
		clusterContextMenu(c, e);
		
		return false;
	});
	
	return d;
}
function renderClusterTab()
{
	return;
	
	var cont = $('#clusterContainer').empty();
}

function renderWorkerIcon(w)
{
	var icon = $('<div>').data('worker', w).attr('id', 'workerIcon_' + w.id);
	$(icon).addClass('workerIcon');
	$(icon).css({
		borderColor: '#' + w.hostcolor
	});
	
	if(w.status == WORKER_READY)
		$(icon).addClass('workerRunning');
	else
		$(icon).addClass('workerNotRunning');
		
	renderWorkerInfo(icon);
	
	return icon;
}

function setClusterSplash(cluster, info)
{
	if(!info)
		return;
	
	if(cluster.id)
		cluster = $('#clusterBox_' + cluster.id)
	else if(typeof cluster == 'string')
		cluster = $('#clusterBox_' + cluster);
		
	if(info == "-")
		info = " ";
		
	$('.clusterSplashInfo', cluster).empty().append($('<marquee>').text(info));
}

function renderWorkerInfo(icon, w)
{	
	if(typeof w == 'undefined')
		w = $(icon).data('worker');
		
	if(!w)
	{
		console.log('render worker info failed, info is ' + w);
		return;
	}
	
	$(icon).removeClass('workerRunning workerNotRunning');
	if(w.status != WORKER_OFFLINE && w.status != 0)
		$(icon).addClass('workerRunning');
	else
		$(icon).addClass('workerNotRunning');
	
	if(w.amtcin == '' || typeof w.amtcin == 'undefined')
		w.amtcin = 0;
		
	$('.workerInfo, .workerStatus', icon).remove();
	
	//$('<div>').addClass('workerInfo').text(w.id + ' ' + w.portudp).appendTo(icon);
	$('<div>').addClass('workerInfo').text(w.id + ' ' + w.workerport).appendTo(icon);
	$('<div>').addClass('workerInfo').text('#CIN ' + formatHumanReadableNumber(w.amtcin)).appendTo(icon);
	
	var battery = $('<div>').addClass('workerStatus').appendTo(icon);
	//console.log(w.id,w.type);
	switch(w.type)
	{
	case WORKER_SPARE:$(battery).addClass('workerSpare');break;
	case WORKER_FRAGMENTED:$(battery).addClass('workerFragmented');break;
	case WORKER_GROWING:$(battery).addClass('workerGrowing');break;
	case WORKER_FULL:$(battery).addClass('workerFull');break;
	case WORKER_PARTIAL:$(battery).addClass('workerPartial');break;
	default:$(battery).addClass('workerSpare');break;
	}
}

function renderClusterWorker(c, d)
{
	var p = {
		clusterid: c.id
	}
	$(d).empty();
	//listClusterWorker(p, function(r){
	//	var rows = listToArray(r.result);
	//	for(var i in rows)
	//	{
	//		var row = rows[i];
	//		var icon = renderWorkerIcon(row);
	//		$(icon).addClass('clickable').appendTo(d)
	//		.bind('contextmenu', function(e){
	//			var w = $(this).data('worker');
	//			workerContextMenu(w, e);
	//			return false;
	//		});
	//	}
	//});
}

function showAddWorkerForm(c, workerList)
{
	var cont = $('#listWorkerForm').empty();
	hostByCluster(c.id, function(r){
		var rows = listToArray(r.result);
		var j = 0;
		for(var i in rows)
		{
			if(j == 4)
			{
				$('<div>').css('clear', 'both').appendTo(cont);
				j = 0;
			}
			
			var row = rows[i];
			var host = renderHost(row, cont);
			$(host)
			.unbind('contextmenu')
			.click(function(e){
				$('.selectedHost').removeClass('selectedHost');
				$(this).addClass('selectedHost');
			});
			j++;
		}
		
		if( j < 1)
		{
			alert('Tidak ada Host tersedia di cluster ini');
			return;
		}
		
		showForm(cont, 'Choose Host', {
			create: function(ui, r){
				var pane = $(ui.target.nextSibling);
				var d = $('<div>');
				$(d)
				.addClass('form-inline')
				.css({
					float: 'left',
					width: '250px',
					marginTop: '8px'
				})
				.appendTo(pane);
				
				$('<span>').text('Worker Type ').appendTo(d);
				
				var s = $('<select>').addClass('input input-mini').appendTo(d);
				$(s).attr('id', 'workerType');
				$('<option>').val(WORKER_FULL + '').text('Full').appendTo(s);
				$('<option>').val(WORKER_SPARE + '').text('Spare').appendTo(s);
				
				$('button', pane).addClass('btn btn-primary');
				initWidgets();
			},
			buttons: {
				"Tambahkan ke Cluster": function(r){
					var s = $('.selectedHost');
					if(s.length < 1)
					{
						alert('Pilih dulu host untuk dimasukkan ke cluster');
						return;
					}
					
					var host = $(s).data('host');
					var p = {};
					p.hostid = host.id;
					p.clustid = c.id;
					//p.clusterport = c.hostport;
					p.type = $('#workerType').val();
					//p.hostudp = host.portudp;
					
					if(p.type == '')
					{
						alert('Tipe Worker harus dipilih');
						return;
					}
					
					saveWorker(p, function(r){
						console.log(r);
						if(r.result != 0)
						{
							alert(r.errmsg);
							return;
						}
						
						$(cont).dialog('close');
					});
					
				}
			}
		});
	});
}

function showClusterForm(c)
{
	var edit = c != null;
	if(edit)
	{
		$('#clusterId').val(c.id).attr('disabled', 'disabled');
		$('#clusterName').val(c.name);
		$('#clusterUdpport').val(c.portudp);
		$('#clusterTcpport').val(c.porttcp);
		$('#clusterIppblock').val(c.ipblock);
	}
	else
	{
		$('#clusterId').val('').removeAttr('disabled');
		$('#clusterName').val('');
		$('#clusterUdpport').val('');
		$('#clusterTcpport').val('');
		$('#clusterIpblock').val('');
	}
	
	showForm('#clusterForm', 'Host Properties', {
		buttons: {
			Save: function(r){
				var p = {};
				p.id = $('#clusterId').val();
				
				if(String(p.id).search(/\s/) >= 0)
				{
					alert('Cluster ID tidak boleh mengandung spasi');
					return;
				}
				
				p.description = $('#clusterDescription').val();
				p.hostport = $('#clusterHostport').val();
				p.respport = $('#clusterRespport').val();
				p.ipblock = $('#clusterIpblock').val();
				
				var action = (edit) ? editCluster : saveCluster;
				
				action(p, function(r){
					if(r.rowAffected < 1)
					{
						alert(r.error);
						return;
					}
					
					$('#clusterForm').dialog('close');
					renderClusterTab();
				});
			}
		}
	});
}

// tab host
function renderHostTab()
{
	$('option', '#hostColor').remove();
	for(var i in HOST_COLORS)
	{
		var c = HOST_COLORS[i];
		$('<option>').val(c).appendTo('#hostColor')
		.html("&nbsp;")
		.css({
			backgroundColor: '#' + c
		})
	}
	$('#hostColor').trigger('change');
	
	//var cont = $('#hostContainer').empty();
	
	//loadHost({}, function(r){
	//	var rows = listToArray(r.result);
	//	var j = 0;
	//	for(var i in rows)
	//	{
	//		if(j == 8)
	//		{
	//			$('<div>').css('clear', 'both').appendTo(cont);
	//			j = 0;
	//		}
	//		
	//		var row = rows[i];
	//		renderHost(row, cont);
	//		j++;
	//	}
	//});
}

	
function renderHost(host, cont)
{
	var divId = 'hostBox_' + host.id;
	var d = $('#' + divId);
	if($(d).length < 1)
	{
		d = $('<div>');
		$(d).addClass('displayHost').attr('id', divId);
	}

	$(d).empty();
	
	//render sysinfo	
	var hostLoad = (Math.random() * 100 + 0);
	var hostMem = (Math.random() * 100 + 0);
	//var hostLoad = host.load || 0;
	//var hostMem = host.freemem || 0;
	var hinfo = $('<div>').addClass('hostSysInfo').appendTo(d);
	var loadVal = $('<div>').addClass('hostLoadBar').appendTo(hinfo).css('height', hostLoad + '%').attr('title', 'system load');
	var memVal = $('<div>').addClass('hostMemBar').appendTo(hinfo).css('height', hostMem + '%').attr('title', 'system memory');
	
	var uptime = host.uptime || 0;
	
	var hostAddText = [
		minToDays(uptime)
	].join('<br>');
	
	var specBar = $('<div>').addClass('hostSpecBar').appendTo(d);
	$('<div>')
	.html(hostAddText)
	.appendTo(specBar);
	
	var icon = $('<img>');
	$(icon).addClass('hostIcon').attr('src', '/static/images/hosticon.png').appendTo(d);
	$(icon).css({
		borderColor: '#' + host.hostcolor
	});
	
	$('<div>').addClass('hostInfoDisplay hostInsideText').text(host.brandtype).appendTo(d);
	$('<div>').addClass('hostIdDisplay hostInsideText').text('#' + host.id).appendTo(d);
	
	var t = $('<table>').attr('border', 0).appendTo(d);
	var row = $('<tr>').appendTo(t);
	
	//$('<td>').text(host.ipaddr + ':' + host.portudp).appendTo(row);
	$('<td>').text(host.ipaddr).appendTo(row);
	
	var amtw = (host.amtworker == '') ? 0 : host.amtworker;
	row = $('<tr>').appendTo(t);
	$('<td>').text('#Worker').appendTo(row);
	$('<td>').text(amtw + '').appendTo(row);
	
	$(d).appendTo(cont);
	
	$(d).data('host', host).bind('contextmenu', function(e){
		var host = $(this).data('host');
		hostContextMenu(host, e);
		return false;
	});
	
	return d;
}


// handling clusterMonitor response,
// rendering necessary stuffs

function updateCluster(c)
{
	var cluster = $('#clusterBox_' + c.id);
	
	// caller should call renderCluster instead
	if($(cluster).length < 1)
		return;
	
	// set color
	var bar = $('.clusterTopbar', cluster);
	$(bar).removeClass('clusterHandicap clusterInitializing clusterPaused clusterReady');
	switch(c.status)
	{
	case CLUSTER_HANDICAP:$(bar).addClass('clusterHandicap');break;
	case CLUSTER_INITIALIZING:$(bar).addClass('clusterInitializing');break;
	case CLUSTER_PAUSED:$(bar).addClass('clusterPaused');break;
	case CLUSTER_READY:$(bar).addClass('clusterReady');break;
	default:$(bar).addClass('clusterReady');break;
	}
	
	// set cluster splash text
	setClusterSplash(cluster, c.splashtext);
}

function clustMonHandler(r)
{
	var cont = $('#clusterContainer');
	for(var i in r)
	{
		var c = r[i];
		var divID = 'clusterBox_' + c.id;
		
		if($('#' + divID).length < 1)
			renderCluster(c, cont);
		
		updateCluster(c);
		
		var workers = c.workers;
		var cluster = $('#' + divID);
		for(var w in workers)
		{
			var worker = workers[w];
			var icon = $('#workerIcon_' + worker.id);
			//console.log('w', worker, icon);
			
			if($(icon).length < 1)
			{
				//console.log('creating new w icon', $(icon).length, '#workerIcon_' + worker.id);
				icon = renderWorkerIcon(worker);
				$(icon)
				.addClass('clickable')
				.bind('contextmenu', function(e){
					var w = $(this).data('worker');
					workerContextMenu(w, e);
				});
				
				$('.clusterWorker', cluster).append(icon);
			}
			else
			{
				$(icon).data('worker', worker);
				renderWorkerInfo(icon);
			}
		}
	}
}

// hostMonitor response handler
function hostMonitorHandler(r)
{
	var cont = $('#hostContainer');
	for(var i in r)
	{
		var host = r[i];
		renderHost(host, cont);
	}
}

function showReport()
{
	var p = {};
	p.servtype = $('#rptTypeservice').val();
	p.start = $('#rptStartDate').val();
	p.stop = $('#rptStopDate').val();
	//p.duration = $('#rptDuration').val();
	p.acctype = $("#rptAccType").val();
	p.hostid = $("#rptHostId").val();
	p.cin = $("#rptCin").val();
	p.hosttrxid = $("#rptHostTrxId").val();
	p.userid = $("#rptUserId").val();
	
	if(p.servtype == 0)
	{
		alert('Harap pilih jenis Laporan');
		return;
	}
	
	var oneYear = 1000 * 3600 * 24 * 366;
	var dsta = new Date(p.start);
	var dsto = new Date(p.stop);
	var ms = Math.abs(dsta.getTime() - dsto.getTime());
	
	if(Math.floor(ms / oneYear) > 0)
	{
		alert('Waktu laporan terbatas untuk 1 tahun saja');
		return;
	}
	
	$('#reportError').remove();
	getReport(p, function(r){
		var cont = $("#reportContainer").empty();		
		var report = $('<div>').addClass('report').appendTo(cont);
		
		var repTitle = r.title;
		var downloadPath = r.downloadPath;
		console.log('report download path', downloadPath);
		var repTitle = $('<h3>').appendTo(report).text(repTitle);

		if(typeof downloadPath != 'undefined')
		{
			$(report).append(
				$('<a>')
				.css({
					position: 'absolute',
					top: '25px',
					right: '10px'
				}).attr('href', downloadPath).text('Unduh Sebagai berkas Excel')
			);
		}
		
		if(r.error != "")
		{
			$('<div>')
			.attr({
				id: 'reportError'
			})
			.addClass('alert alert-dangerous')
			.html(r.error + '')
			.insertBefore(cont);
			return;
		}
		
		if(r.rowResulted < 1)
		{
			$('<h4>').css('margin-left', '10px').text('tidak ada laporan tersedia').appendTo(report);
			return;
		}
		
		var t = $('<table>').addClass('table table-condensed table-striped').appendTo(report);
		//$('<caption>').appendTo(t).text(repTitle);
		
		var rows = r.result;
		var fields = rows.shift();
		var thead = $('<tr>').appendTo(t);
		for(var i in fields) $('<th>').appendTo(thead).text(toTitleCase(fields[i]));
		
		/*
		for(var i in rows)
		{
			var row = rows[i];
			var trow = $('<tr>').appendTo(t);
			for(var j in row)
				$('<td>').appendTo(trow).text(row[j]);
		}
		return;
		// */
		
		var len = rows.length
		var renderLine = (len > 100) ? 100 : len;
		var doRenderRows = function(start){
			console.log('rendering from: ' + start, len);
			var n = 0;
			var limit = start + renderLine;
			for(var i = start; i <= limit; i++)
			{
				if(n >= renderLine)
					break;
				
				var row = rows[i];
				var trow = $('<tr>').appendTo(t);
				for(var j in row)
				{
					var s = row[j];
					var cls = (/^[0-9\.]*$/.test(s)) ? 'displayInteger' : '';
					$('<td>').addClass(cls).appendTo(trow).text(row[j]);
				}
				
				n++;
			}
			
			console.log(i, len, n);
			if(i >= len)
			{
				console.log('done', i, len);
				return;
			}
		
			setTimeout(function(){
				doRenderRows(i);
			}, 300);
		}
		doRenderRows(0);
	});
}
