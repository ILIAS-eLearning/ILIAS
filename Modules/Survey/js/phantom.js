var system = require('system');
if(system.args.length < 7)
{
    system.console.log("Number of arguments is: " + system.args.length + " expecting 7.");
    phantom.exit(1);
}

/*
console.log("PHPSESSID value:" + system.args[1]);
console.log("PHPSESSID domain:" + system.args[2]);
console.log("PHPSESSID path:" + system.args[3]);
console.log("ilClientId value:" + system.args[4]);
console.log("ilClientId domain:" + system.args[2]);
console.log("ilClientId path:" + system.args[3]);*/

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
	// :TODO: should be 1170 but somehow the test server scales differently
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

	// clip chart to minimum dimensions
	if(system.args[6].indexOf(".png") > -1)
	{
		var height = page.evaluate(function(){
			return document.getElementById('ilChartWrapper').offsetHeight;
		}); 
		var width = page.evaluate(function(){
			return document.getElementById('ilChartWrapper').offsetWidth;
		}); 	

		page.clipRect = { top: 0, left: 0, width: width, height: height };	
	}

  page.render(system.args[6]);
  
  phantom.exit();
});
