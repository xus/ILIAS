var system = require('system');
if(system.args.length < 7)
{   
    phantom.exit();
}
else
{		
	// auth
	phantom.addCookie({
		'name'     : 'PHPSESSID',  
		'value'    : system.args[1],
		'domain'   : system.args[2],
		'path'     : system.args[3]              
	});
	phantom.addCookie({
		'name'     : 'ilClientId',  
		'value'    : system.args[4],
		'domain'   : system.args[2],
		'path'     : system.args[3]              
	});
	
	var page = require('webpage').create();	
		
	// :TODO:	
	if(system.args[6].indexOf(".png") > -1)
	{
		page.viewportSize = {
			width: 1170,
			height: 500
		};	
	}
   
	page.open(system.args[5], function() {
	  page.render(system.args[6]);
	  phantom.exit();
	});
}