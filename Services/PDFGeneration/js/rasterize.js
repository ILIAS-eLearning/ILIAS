var ilPhantomJsWrapper =  (function () {
	'use strict';

	var pub = {},
		pro = {
			phantom_page	: {},
			status			: '',
			out_file		: '',
			delay			: 500
		};

	pub.exitPhantom = function(exit_code)
	{
		phantom.exit(exit_code);
	};

	pub.callbackPhantomAndReturnValue = function(callback_function)
	{
		return phantom.callback(callback_function);
	};

	pub.initWebPageObject = function()
	{
		pro.phantom_page = require('webpage').create();
	};

	pub.configurePageObject = function(object)
	{
		pro.phantom_page.paperSize = object;
	};

	pub.configureViewport = function(viewport)
	{
		pro.phantom_page.viewportSize =
			{
				width:  viewport[0],
				height: viewport[1]
			};
		pro.phantom_page.clipRect = {top: 0, left: 0, width: viewport[0], height: viewport[1]};
	};

	pub.renderPageObject = function(src_file, out_file, delay, zoom)
	{
		pro.out_file	= out_file;
		pro.delay		= delay;

		pro.phantom_page.open(src_file, function (status)
		{
			pro.phantom_page.evaluate(function(zoom) {
				document.querySelector('body').style.zoom = zoom;
			}, 0.85);
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
				}, pro.delay);
			}
		});

	};
	pub.protect = pro;
	return pub;
}());

var ilPhantomJsHelper =  (function () {
	'use strict';

	var pub = {
		version 			: '0.0.2'
	}, pro = {
		json							: {},
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
		return ilPhantomJsWrapper.callbackPhantomAndReturnValue(function (pageNum, numPages) {
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
		return ilPhantomJsWrapper.callbackPhantomAndReturnValue(function (pageNum, numPages) {
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
		if (show_pages === true || show_pages === '1')
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
				ilPhantomJsWrapper.configureViewport(viewport);
				return true;
			}
		}
		return false;
	};

	pro.configurePage = function() {
		var size = pro.json.page_size.split('*');
		var page_object = {};
		if (!pro.checkForViewportSetting()) {
			if (size.length === 2) {
				page_object = {
					width:  size[0],
					height: size[1],
					margin: pro.json.margin,
					header: pro.appendHeaderCallback(),
					footer: pro.appendFooterCallback(),
					zoom  : pro.json.zoom
				};
			}
			else {
				page_object = {
					format:      size,
					orientation: pro.json.orientation,
					margin:      pro.json.margin,
					header:      pro.appendHeaderCallback(),
					footer:      pro.appendFooterCallback(),
					zoom  :      pro.json.zoom
				};
			}
			ilPhantomJsWrapper.configurePageObject(page_object);
			return page_object;
		}
	};

	pro.renderPage = function()
	{
		ilPhantomJsWrapper.renderPageObject(pro.src_file, pro.out_file, pro.json.delay, pro.json.zoom);
	};

	pub.Init = function(src_file, out_file, json)
	{
		if (json == 'defaults') {
			pro.json = {
				page_size: "A4",
				orientation: "Portrait",
				margin: "1cm",
				delay: "200",
				viewport: "",
				header: null,
				footer: null,
				page_type: "0"
			}
		} else {
			try {
				pro.json = JSON.parse(json);
			}
			catch(error){
				console.log('Config error no valid JSON given: ' + error);
				ilPhantomJsWrapper.exitPhantom(1);
			}
		}

		pro.src_file = src_file;
		pro.out_file = out_file;
		ilPhantomJsWrapper.initWebPageObject();
		pro.configurePage();
		pro.renderPage();
	};

	pub.protect = pro;
	return pub;
}());

var ilPhantomJsRun =  (function () {
	'use strict';

	if (typeof window.__karma__ === 'undefined' && typeof window.jasmine === 'undefined') {

		var system = require('system');

		if(system.args.length === 4)
		{
			ilPhantomJsHelper.Init(system.args[1], system.args[2], system.args[3]);
		}
		else if(system.args.length === 2 && system.args[1] === 'version')
		{
			console.log(ilPhantomJsHelper.version);
			ilPhantomJsWrapper.exitPhantom(0);
		}
		else
		{
			console.log('Wrong number of arguments!');
			ilPhantomJsWrapper.exitPhantom(1);
		}
	}
}());
