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
			
	if(system.args[6].indexOf(".png") > -1)
	{
		page.viewportSize = {
			width: 1170,
			height: 500
		};	
		page.clipRect = { 
			top: 75, 
			left: 30,
			width: 1100, 
			height: 430 
		};
	}
	else
	{	
		page.paperSize = { 
			width: 1170,
			height: 830,
			format: 'A4', 
			orientation: 'landscape', // landscape does not really work
			margin: '1cm' 
		};
	}
	
	page.open(system.args[5], function() {
	  page.render(system.args[6]);
	  phantom.exit();
	});
}