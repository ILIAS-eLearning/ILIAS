
var languageAdded = 0;

function changeLanguage(lang) {
	jqcc('#MSTWMenu').val(lang);
	jqcc('#MSTWMenu').change();
	jqcc('#MSTWGoButton').click();
}

function addLanguageCode() {
	if (!languageAdded) {
		jqcc("body").append('<div id="MicrosoftTranslatorWidget" style="display:none"></div>');
		setTimeout(function() { var s = document.createElement("script"); s.type = "text/javascript"; s.charset = "UTF-8"; s.src = ((location && location.href && location.href.indexOf('https') == 0) ? "https://ssl.microsofttranslator.com" : "http://www.microsofttranslator.com" ) + "/ajax/v2/widget.aspx?mode=manual&from=en&layout=ts"; var p = document.getElementsByTagName('head')[0] || document.documentElement; p.insertBefore(s, p.firstChild); }, 0);
		jqcc('#MSTTExitLink').click();
	}
}

jqcc(function() {
	if (jqcc.cookie('mstto')) {
		addLanguageCode();
	}
});