
function getPrinterList(data, fx)
{
	sendRequest('iface/getlist', data, function(result, rspcode, data){
                fx(data);
        });	
}

function listMessage(data, fx)
{
	sendRequest('ns/listmsg', data, function(result, rspcode, r){
                fx(r);
        });
}

function setMessage(data, fx)
{
	sendRequest('ns/setmsg', data, function(result, rspcode, r){
                fx(r);
        });
}

function startPrint(data, fx)
{
	sendRequest('ns/startprint', data, function(result, rspcode, r){
                fx(r);
        });
}

function stopPrint(data, fx)
{
	sendRequest('ns/stopprint', data, function(result, rspcode, r){
                fx(r);
        });
}

function startPrint(data, fx)
{
	sendRequest('ns/startprint', data, function(result, rspcode, r){
                fx(r);
        });
}

function connect(data, fx)
{
	sendRequest('ns/connect', data, function(result, rspcode, r){
                fx(r);
        });
}

function disconnect(data, fx)
{
	sendRequest('ns/disconnect', data, function(result, rspcode, r){
                fx(r);
        });
}

function stopPrint(data, fx)
{
	sendRequest('ns/stopprint', data, function(result, rspcode, r){
                fx(r);
        });
}

function counterReset(data, fx)
{
	sendRequest('ns/counterreset', data, function(result, rspcode, r){
                fx(r);
        });
}
// end AJAX CALLS
