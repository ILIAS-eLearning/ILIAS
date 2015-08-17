
var languageAdded = 0;

function fireEventCC(element,event){
	try {
		if (document.createEventObject){
			var evt = document.createEventObject();
			element.fireEvent('on'+event,evt);
		} else {
			var evt = document.createEvent("HTMLEvents");
			evt.initEvent(event, true, true );
			element.dispatchEvent(evt);
		}
	} catch (e) {
	}
}

function changeLanguage(lang) {
	if (jqcc('#google_translate_element').length == 0 || jqcc('#google_translate_element').html() == '' || jqcc('.goog-te-combo').length == 0  || jqcc('.goog-te-combo').html() == '') {
		setTimeout(function() { changeLanguage(lang); }, 500);
	} else {
		jqcc('.goog-te-combo').val(lang);
		jqcc('.goog-te-combo').attr('id','cclanguagebutton');
		fireEventCC(document.getElementById('cclanguagebutton'),'change');
	}
}

function addLanguageCode() {
	if (!languageAdded) {
		jqcc.getScript('//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit',function(data) {
			jqcc("body").append('<div id="google_translate_element"></div><style>#google_translate_element {display:none!important;}</style>');
			languageAdded++;
		});
	}
}

jqcc(function() {
	if (jqcc.cookie('googtrans')) {
		addLanguageCode();
	}
});

function googleTranslateElementInit() {
  new google.translate.TranslateElement({
	  pageLanguage: 'en',
	  autoDisplay: false
  }, 'google_translate_element');
}