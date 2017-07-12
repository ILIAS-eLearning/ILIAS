/*
 '{"page_size":"A4","zoom":"1","orientation":"Portrait","margin":"1cm","delay":"200","viewport":null,"header":{"text":"Phantomjs","height":"2cm","show_pages":true},"footer":{"text":false,"height":false,"show_pages":false}}'
 */
describe("Rasterize.js Testsuite", function() {
	beforeEach(function () {
		ilPhantomJsWrapper.exitPhantom = function(value){return value;};
		ilPhantomJsWrapper.initWebPageObject = function(){return true};
		ilPhantomJsWrapper.renderPageObject = function(){return true};
		ilPhantomJsWrapper.configureViewport = function(){return true};
		configurePageOrg = ilPhantomJsHelper.protect.configurePage;
		ilPhantomJsHelper.protect.configurePage = function(){};
		renderPageOrg = ilPhantomJsHelper.protect.renderPage;
		ilPhantomJsHelper.protect.renderPage = function(){};
	});
	
	describe("Simple Existing Tests", function() {
		it("ilPhantomJsHelper exists", function () {
			expect(ilPhantomJsHelper).toBeDefined();
		});

		it("Init exists", function () {
			expect(ilPhantomJsHelper.Init).toBeDefined();
		});

		it("protect exists", function () {
			expect(ilPhantomJsHelper.protect).toBeDefined();
		});

		it("pub does not exists", function () {
			expect(ilPhantomJsHelper.pub).not.toBeDefined();
		});
	});

	describe("Init Function Tests", function() {

		it("Init Function test with empty json", function () {
			ilPhantomJsHelper.Init('test.html', 'test.pdf', '{}');
			expect(ilPhantomJsHelper.protect.json).toEqual({ });
		});

		it("Init Function test without empty json", function () {
			ilPhantomJsHelper.Init('test.html', 'test.pdf', '{"page_size":"A3","zoom":"1"}');
			expect(ilPhantomJsHelper.protect.json).toEqual({"page_size":"A3","zoom":"1"});
		});

		it("Init Function test with no json", function () {
			spyOn(ilPhantomJsWrapper, 'exitPhantom');
			ilPhantomJsHelper.Init('test.html', 'test.pdf','');
			expect(ilPhantomJsWrapper.exitPhantom).toHaveBeenCalled();
		});

	});

	describe("appendHeaderCallback Function Test", function() {
		beforeEach(function () {
			addHeaderCallbackFunctionOrg =  ilPhantomJsHelper.protect.addHeaderCallbackFunction;
			ilPhantomJsHelper.protect.addHeaderCallbackFunction = function(){};
		});

		it("Call appendHeaderCallback function without header", function () {
			ilPhantomJsHelper.Init('test.html', 'test.pdf',  '{"page_size":"A4","viewport":null,"header":null}');
			ilPhantomJsHelper.protect.configurePage();
			var value = ilPhantomJsHelper.protect.appendHeaderCallback();
			expect(value).not.toBeDefined();
		});

		it("Call appendHeaderCallback function with header", function () {
			ilPhantomJsHelper.Init('test.html', 'test.pdf',  '{"page_size":"A4","viewport":null,"header":{"text":"Phantomjs","height":"2cm","show_pages":true}}');
			ilPhantomJsHelper.protect.configurePage();
			var value = ilPhantomJsHelper.protect.appendHeaderCallback();
			expect(value).toEqual({ height: '2cm', contents: undefined });
		});
		afterEach(function () {
			ilPhantomJsHelper.protect.addHeaderCallbackFunction = addHeaderCallbackFunctionOrg;
		});
	});

	describe("shouldHeaderAndFooterBePrintedForFirstPage Function Test", function() {

		it("shouldHeaderAndFooterBePrintedForFirstPage function will return true", function () {
			var value = ilPhantomJsHelper.protect.shouldHeaderAndFooterBePrintedForFirstPage(0);
			expect(value).toEqual(true);
		});

		it("shouldHeaderAndFooterBePrintedForFirstPage function will return false", function () {
			ilPhantomJsHelper.protect.no_footer_header_on_first_page = true;
			var value = ilPhantomJsHelper.protect.shouldHeaderAndFooterBePrintedForFirstPage(1);
			expect(value).toEqual(false);
		});
	});

	describe("appendFooterCallback Function Test", function() {
		beforeEach(function () {
			addFooterCallbackFunctionnOrg =  ilPhantomJsHelper.protect.addFooterCallbackFunction;
			ilPhantomJsHelper.protect.addFooterCallbackFunction = function(){};
		});

		it("Call appendFooterCallback function without header", function () {
			ilPhantomJsHelper.Init('test.html', 'test.pdf',  '{"page_size":"A4","viewport":null,"footer":null}');
			ilPhantomJsHelper.protect.configurePage();
			var value = ilPhantomJsHelper.protect.appendFooterCallback();
			expect(value).not.toBeDefined();
		});

		it("Call appendFooterCallback function with header", function () {
			ilPhantomJsHelper.Init('test.html', 'test.pdf',  '{"page_size":"A4","viewport":null,"footer":{"text":"Phantomjs","height":"3cm","show_pages":true}}');
			ilPhantomJsHelper.protect.configurePage();
			var value = ilPhantomJsHelper.protect.appendFooterCallback();
			expect(value).toEqual({ height: '3cm', contents: undefined });
		});
		afterEach(function () {
			ilPhantomJsHelper.protect.addFooterCallbackFunction = addFooterCallbackFunctionnOrg;
		});
	});

	describe("addHeaderFooterSpan Function Test", function() {

		it("Call addHeaderFooterSpan without show pages", function () {
			var value = ilPhantomJsHelper.protect.addHeaderFooterSpan(1, 11, false);
			expect(value).toEqual('<span></span>');
		});

		it("Call addHeaderFooterSpan function show pages", function () {
			var value = ilPhantomJsHelper.protect.addHeaderFooterSpan(1, 11, true);
			expect(value).toEqual('<span style="float: right;">1 / 11</span>');
		});
	});

	describe("checkForViewportSetting Function Test", function() {
		beforeEach(function () {
			
		});

		it("Call checkForViewportSetting function without vieport", function () {
			ilPhantomJsHelper.Init('test.html', 'test.pdf',  '{"viewport":null}');
			var value = ilPhantomJsHelper.protect.checkForViewportSetting();
			expect(value).toEqual(false);
		});

		it("Call checkForViewportSetting function with vieport", function () {
			ilPhantomJsHelper.Init('test.html', 'test.pdf',  '{"viewport":"800*600"}');
			var value = ilPhantomJsHelper.protect.checkForViewportSetting();
			expect(value).toEqual(true);
		});

		it("Call checkForViewportSetting function with wrong vieport", function () {
			ilPhantomJsHelper.Init('test.html', 'test.pdf',  '{"viewport":"8*00*600"}');
			var value = ilPhantomJsHelper.protect.checkForViewportSetting();
			expect(value).toEqual(false);
		});
	});

	afterEach(function () {
		ilPhantomJsHelper.protect.configurePage = configurePageOrg;
		ilPhantomJsHelper.protect.renderPage = renderPageOrg;
	});
});

describe("Rasterize.js Testsuite configurePage", function() {

	describe("configurePage Function Test", function() {

		it("Call configurePage should call configurePageObject with page format", function () {
			spyOn(ilPhantomJsHelper.protect, "checkForViewportSetting").and.callFake(function() {
				return false;
			});
			ilPhantomJsHelper.protect.appendHeaderCallback = (function(){});
			ilPhantomJsHelper.protect.appendFooterCallback = (function(){});
			ilPhantomJsHelper.Init('test.html', 'test.pdf',  '{"delay":"200","page_size":"A4","viewport":"800*600", "margin" : 0}');
			obj = ilPhantomJsHelper.protect.configurePage();
			expect(obj).toEqual({ format: [ 'A4' ], orientation: undefined, margin: 0, header: undefined, footer: undefined });
		});

		it("Call configurePage should call configurePageObject with page size", function () {
			spyOn(ilPhantomJsHelper.protect, "checkForViewportSetting").and.callFake(function() {
				return false;
			});
			ilPhantomJsHelper.protect.appendHeaderCallback = (function(){});
			ilPhantomJsHelper.protect.appendFooterCallback = (function(){});
			ilPhantomJsHelper.Init('test.html', 'test.pdf',  '{"delay":"200","page_size":"1000*2500","viewport":"800*600", "margin" : 0}');
			obj = ilPhantomJsHelper.protect.configurePage();
			expect(obj).toEqual({ width: '1000', height: '2500', margin: 0, header: undefined, footer: undefined });
		});

		it("Call configurePage should call configurePageObject with set viewport", function () {
			spyOn(ilPhantomJsHelper.protect, "checkForViewportSetting").and.callFake(function() {
				return true;
			});
			ilPhantomJsHelper.protect.appendHeaderCallback = (function(){});
			ilPhantomJsHelper.protect.appendFooterCallback = (function(){});
			ilPhantomJsHelper.Init('test.html', 'test.pdf',  '{"delay":"200","page_size":"1000*2500","viewport":"800*600", "margin" : 0}');
			obj = ilPhantomJsHelper.protect.configurePage();
			expect(obj).not.toBeDefined();
		});
	});
});

describe("Rasterize.js Testsuite renderPage", function() {

	describe("renderPage Function Test", function() {

		it("Call configurePage should call configurePageObject with page format", function () {
			spyOn(ilPhantomJsWrapper, "renderPageObject").and.callFake(function() {
				return true;
			});
			obj = ilPhantomJsHelper.protect.renderPage();
			expect(ilPhantomJsWrapper.renderPageObject).toHaveBeenCalled();
		});
		
	});
});

describe("Rasterize.js Testsuite callbackPhantomAndReturnValue", function() {

	beforeEach(function () {
		spyOn(ilPhantomJsWrapper, "callbackPhantomAndReturnValue").and.callFake(function(callback) {
			callback();
		});
	});
	it("Call addHeaderCallbackFunction should call callbackPhantomAndReturnValue", function () {
		ilPhantomJsHelper.Init('test.html', 'test.pdf',  '{"page_size":"A4","viewport":null,"header":{"text":"Phantomjs","height":"2cm","show_pages":true}}');
		obj = ilPhantomJsHelper.protect.addHeaderCallbackFunction();
		expect(ilPhantomJsWrapper.callbackPhantomAndReturnValue).toHaveBeenCalled();
	});

	it("Call addFooterCallbackFunction should call callbackPhantomAndReturnValue", function () {
		ilPhantomJsHelper.Init('test.html', 'test.pdf',  '{"page_size":"A4","zoom":"1","orientation":"Portrait","margin":"1cm","delay":"200","viewport":null,"header":{"text":"Phantomjs","height":"2cm","show_pages":true},"footer":{"text":false,"height":false,"show_pages":false}}');
		obj = ilPhantomJsHelper.protect.addFooterCallbackFunction();
		expect(ilPhantomJsWrapper.callbackPhantomAndReturnValue).toHaveBeenCalled();
	});
});
