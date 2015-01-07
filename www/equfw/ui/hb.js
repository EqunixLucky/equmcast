// hearbeat
var _hb = new function()
{
        var payload = {};

        var lastbeat = new Date();
        var requestTimer = null;
	var running = false;
	var stopped = true;
	
	this.stop = function()
	{
		stopped = true;	
	}
	
        var beatExpired = function()
        {
		return false;
		
                var appConfig = CACHE.get('appconfig');
                var max = 3; // the max number of "missed" heartbeat calls
                var interval = appConfig.periode_webrefresh || 5;
                interval *= 1000; // millisecond to second

                var now = new Date();
                if(now.getTime() - lastbeat.getTime() < (max * interval))
                        return false;

                // ok, it's more than tollerable interval,
                console.log('HEARTBEAT EXPIRED, shutting down application');

                // block the screen
                //shutdownApp();
                clearInterval(requestTimer);

                return true;
        }

        this.addPayload = function(method, url, param, callback)
	{
                payload[method] = { url: url, param: param, callback: callback }
		
		if(!running)
		{
			console.log('run hb after adding payload', method);
			//this.run();
		}
        };
	
	this.resetPayload = function()
	{
                payload = {};
		running = false;
        };

        this.deletePayload = function(method)
        {
                delete payload[method];
        };
	
	var beats = function(method)
	{
		if(running) return;
		if(stopped) return;
		
                var load = [];
                for(var i in payload)
		{
                        var pload = {};
                        pload[i] = payload[i].param;
                        load.push(pload);
                }

                var params = { load: load }

		running = true;
		
		sendRequest(
			'ui/'+payload['heartbeat'].url, 
			payload['heartbeat'].param, 
			function(result)
			{
				running = false;
				
				lastbeat = new Date();
				var callbackFx = payload['heartbeat'].callback;
				if(typeof callbackFx != 'function') return;
	
				callbackFx(result);
			}
		);
	}

        this.run = function()
	{
		if(running) return;
		
                console.log('heartbeat runs...');
                if(requestTimer != null) clearInterval(requestTimer);
		
		stopped = false;
                beats();
		
                requestTimer = setInterval(beats, 2 * 1000);
        };
};
