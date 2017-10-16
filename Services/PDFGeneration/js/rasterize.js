var PhantomJsHelper =  (function () {
	'use strict';

	var pub = {
		version 			: '0.0.1'
	}, pro = {
		json							: {},
		phantom_page					: {},
		src_file						: '',
		out_file						: '',
		element_type					: 'h4',
		page_separator					: ' / ',
		no_footer_header_on_first_page	: false
	};

	pro.appendHeaderCallback = function() {
		if (pro.json.header !== null) {
			return {
				height:   pro.json.header.height,
				contents: pro.addHeaderCallbackFunction()
			};
		}
	};

	pro.addHeaderCallbackFunction = function () {
		return	phantom.callback(function (pageNum, numPages) {
			var element = document.createElement(pro.element_type);
			if(pro.shouldHeaderAndFooterBePrintedForFirstPage(pageNum))
			{
				element.innerHTML = pro.json.header.text + pro.addHeaderFooterSpan(pageNum, numPages, pro.json.header.show_pages);
				return element.outerHTML;
			}
		});
	};

	pro.shouldHeaderAndFooterBePrintedForFirstPage = function(pageNum)
	{
		return ! (pro.no_footer_header_on_first_page === true && parseInt(pageNum, 10) === 1);
	};

	pro.appendFooterCallback = function()
	{
		if(pro.json.footer !== null ) {
			return {
				height	:	pro.json.footer.height,
				contents:	pro.addFooterCallbackFunction()
			};
		}
	};

	pro.addFooterCallbackFunction = function () {
		return	phantom.callback(function (pageNum, numPages) {
			var element = document.createElement(pro.element_type);
			if(pro.shouldHeaderAndFooterBePrintedForFirstPage(pageNum))
			{
				element.innerHTML = pro.json.footer.text + pro.addHeaderFooterSpan(pageNum, numPages, pro.json.footer.show_pages);
				return element.outerHTML;
			}
		});
	};

	pro.addHeaderFooterSpan = function(pageNum, numPages, show_pages) {
		var span = document.createElement('span');
		if (show_pages === true || show_pages == 1)
		{
			span.style.float	= 'right';
			span.innerHTML		= pageNum + pro.page_separator + numPages;
		}
		return span.outerHTML;
	};

	pro.checkForViewportSetting = function()
	{
		var viewport;
		if (pro.json.viewport !== null)
		{
			viewport = pro.json.viewport.split('*');
			if(viewport.length === 2)
			{
				pro.phantom_page.viewportSize =
					{
						width:  viewport[0],
						height: viewport[1]
					};
				pro.phantom_page.clipRect = {top: 0, left: 0, width: viewport[0], height: viewport[1]};
				return true;
			}
		}
		return false;
	};

	pro.initPhantomWebPageObject = function()
	{
		pro.phantom_page = require('webpage').create();
	};

	pro.configurePhantomWebPage = function()
	{
		var size = pro.json.page_size.split('*');
		if( ! pro.checkForViewportSetting())
		{
			if(size.length === 2)
			{
				pro.phantom_page.paperSize = {
					width	:	size[0],
					height	:	size[1],
					margin	:	pro.json.margin,
					header	:	pro.appendHeaderCallback(),
					footer	:	pro.appendFooterCallback()
				};
			}
			else
			{
				pro.phantom_page.paperSize = {
					format		:	size,
					orientation	:	pro.json.orientation,
					margin		:	pro.json.margin,
					header		:	pro.appendHeaderCallback(),
					footer		:	pro.appendFooterCallback()
				};
			}
		}
	};

	pro.renderPhantomPage = function()
	{
		pro.phantom_page.open(pro.src_file, function (status)
		{
			if (status !== 'success')
			{
				console.log('Unable to load the address!');
				pub.exitPhantom(1);
			}
			else
			{
				window.setTimeout(function () {
					pro.phantom_page.render(pro.out_file);
					pub.exitPhantom(0);
				}, pro.json.delay);
			}
		});
	};

	pub.exitPhantom = function(exit_code)
	{
		phantom.exit(exit_code);
	};

	pub.Init = function(src_file, out_file, json)
	{
		try{
			pro.json = JSON.parse(json);
		}
		catch(error){
			console.log('Config error no valid JSON given: ' + error);
			pub.exitPhantom(1);
		}
		pro.src_file = src_file;
		pro.out_file = out_file;
		pro.initPhantomWebPageObject();
		pro.configurePhantomWebPage();
		pro.renderPhantomPage();
	};

	pub.protect = pro;
	return pub;
}());

system = require('system');

if(system.args.length === 4)
{
	PhantomJsHelper.Init(system.args[1], system.args[2], system.args[3]);
}
else if(system.args.length === 2 && system.args[1] === 'version')
{
	console.log(PhantomJsHelper.version)
	PhantomJsHelper.exitPhantom(0);
}
else
{
	console.log('Wrong number of arguments!');
	PhantomJsHelper.exitPhantom(1);
}
