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
			height: 410
		};			
	}
	else
	{	
		var pw = 1500;
		var ph = pw*0.70707070;
		
		page.paperSize = { 
			width: pw,
			height: ph,
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