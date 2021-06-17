(function($){
	$.fn.validationEngineLanguage = function(){
	};
	$.validationEngineLanguage = {
		newLang: function(){
			$.validationEngineLanguage.allRules = {
				"required": { // Add your regex rules here, you can take telephone as an example
					"regex": "none",
					"alertText": "* Kötelező mező",
					"alertTextCheckboxMultiple": "* Legalább egyet válasszon",
					"alertTextCheckboxe": "* Kötelező",
					"alertTextDateRange": "* Az időintervallum mindkét vége kötelező"
				},
				"requiredInFunction": {
					"func": function(field, rules, i, options){
						return (field.val() == "teszt") ? true : false;
					},
					"alertText": "* A mezőbe ennek kell lennie: teszt"
				},
				"dateRange": {
					"regex": "none",
					"alertText": "* Érvénytelen ",
					"alertText2": "Dátumintervallum"
				},
				"dateTimeRange": {
					"regex": "none",
					"alertText": "* Érvénytelen ",
					"alertText2": "Dátumidő-intervallum"
				},
				"minSize": {
					"regex": "none",
					"alertText": "* Minimum ",
					"alertText2": " karakter engedélyezett"
				},
				"maxSize": {
					"regex": "none",
					"alertText": "* Maximum ",
					"alertText2": " karakter engedélyezett"
				},
				"groupRequired": {
					"regex": "none",
					"alertText": "* Legalább egy mezőt ki kell töltenie"
				},
				"min": {
					"regex": "none",
					"alertText": "* A minimum érték "
				},
				"max": {
					"regex": "none",
					"alertText": "* A maximum érték "
				},
				"past": {
					"regex": "none",
					"alertText": "* Korábbi dátum "
				},
				"future": {
					"regex": "none",
					"alertText": "* Utáni dátum "
				},
				"maxCheckbox": {
					"regex": "none",
					"alertText": "* Maximum ",
					"alertText2": " megengedett lehetőség"
				},
				"minCheckbox": {
					"regex": "none",
					"alertText": "* Válasszon ",
					"alertText2": " lehetőségek"
				},
				"equals": {
					"regex": "none",
					"alertText": "* Mezők nem azonosak"
				},
				"creditCard": {
					"regex": "none",
					"alertText": "* Érvénytelen bankkártyaszám"
				},
				"phone": {
					// credit: jquery.h5validate.js / orefalo
					"regex": /^([\+][0-9]{1,3}[\ \.\-])?([\(]{1}[0-9]{2,6}[\)])?([0-9\ \.\-\/]{3,20})((x|ext|extension)[\ ]?[0-9]{1,4})?$/,
					"alertText": "* Érvénytelen telefonszám"
				},
				"email": {
					// HTML5 compatible email regex ( http://www.whatwg.org/specs/web-apps/current-work/multipage/states-of-the-type-attribute.html#    e-mail-state-%28type=email%29 )
					"regex": /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/,
					"alertText": "* Érvénytelen e-mail cím"
				},
				"integer": {
					"regex": /^[\-\+]?\d+$/,
					"alertText": "* Nem egész szám"
				},
				"number": {
					// Number, including positive, negative, and floating decimal. credit: orefalo
					"regex": /^[\-\+]?((([0-9]{1,3})([,][0-9]{3})*)|([0-9]+))?([\.]([0-9]+))?$/,
					"alertText": "* Érvénytelen floating decimal number"
				},
				"date": {
					"regex": /^\d{4}[\/\-](0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])$/,
					"alertText": "* Érvénytelen date, must be in YYYY-MM-DD format"
				},
				"ipv4": {
					"regex": /^((([01]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))[.]){3}(([0-1]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))$/,
					"alertText": "* Érvénytelen IP address"
				},
				"url": {
					"regex": /^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i,
					"alertText": "* Érvénytelen URL"
				},
				"onlyNumberSp": {
					"regex": /^[0-9\ ]+$/,
					"alertText": "* Csak számok"
				},
				"onlyLetterSp": {
					"regex": /^[a-zA-Z\ \']+$/,
					"alertText": "* Csak betűk"
				},
				"onlyLetterNumber": {
					"regex": /^[0-9a-zA-Z]+$/,
					"alertText": "* No special characters allowed"
				},
				// --- CUSTOM RULES -- Those are specific to the demos, they can be removed or changed to your likings
				"ajaxUserCall": {
					"url": "ajaxValidateFieldUser",
					// you may want to pass extra data on the ajax call
					"extraData": "name=eric",
					"alertText": "* Ez a felhasználónév már létezik",
					"alertTextLoad": "* Ellenőrzés, kérem várjon"
				},
				"ajaxUserCallPhp": {
					"url": "phpajax/ajaxValidateFieldUser.php",
					// you may want to pass extra data on the ajax call
					"extraData": "name=eric",
					// if you provide an "alertTextOk", it will show as a green prompt when the field validates
					"alertTextOk": "* Ez a felhasználónév még szabad",
					"alertText": "* Ez a felhasználónév már létezik",
					"alertTextLoad": "* Ellenőrzés, kérem várjon"
				},
				"ajaxNameCall": {
					// remote json service location
					"url": "ajaxValidateFieldName",
					// error
					"alertText": "* Ez a név már létezik",
					// if you provide an "alertTextOk", it will show as a green prompt when the field validates
					"alertTextOk": "* A név elérhető",
					// speaks by itself
					"alertTextLoad": "* Ellenőrzés, kérem várjon"
				},
				"ajaxNameCallPhp": {
					// remote json service location
					"url": "phpajax/ajaxValidateFieldName.php",
					// error
					"alertText": "* Ez a név már létezik",
					// speaks by itself
					"alertTextLoad": "* Ellenőrzés, kérem várjon"
				},
				"validate2fields": {
					"alertText": "* Üsse be HELLO"
				},
				//tls warning:homegrown not fielded 
				"dateFormat":{
					"regex": /^\d{4}[\/\-](0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])$|^(?:(?:(?:0?[13578]|1[02])(\/|-)31)|(?:(?:0?[1,3-9]|1[0-2])(\/|-)(?:29|30)))(\/|-)(?:[1-9]\d\d\d|\d[1-9]\d\d|\d\d[1-9]\d|\d\d\d[1-9])$|^(?:(?:0?[1-9]|1[0-2])(\/|-)(?:0?[1-9]|1\d|2[0-8]))(\/|-)(?:[1-9]\d\d\d|\d[1-9]\d\d|\d\d[1-9]\d|\d\d\d[1-9])$|^(0?2(\/|-)29)(\/|-)(?:(?:0[48]00|[13579][26]00|[2468][048]00)|(?:\d\d)?(?:0[48]|[2468][048]|[13579][26]))$/,
					"alertText": "* Érvénytelen dátum"
				},
				//tls warning:homegrown not fielded 
				"dateTimeFormat": {
					"regex": /^\d{4}[\/\-](0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])\s+(1[012]|0?[1-9]){1}:(0?[1-5]|[0-6][0-9]){1}:(0?[0-6]|[0-6][0-9]){1}\s+(am|pm|AM|PM){1}$|^(?:(?:(?:0?[13578]|1[02])(\/|-)31)|(?:(?:0?[1,3-9]|1[0-2])(\/|-)(?:29|30)))(\/|-)(?:[1-9]\d\d\d|\d[1-9]\d\d|\d\d[1-9]\d|\d\d\d[1-9])$|^((1[012]|0?[1-9]){1}\/(0?[1-9]|[12][0-9]|3[01]){1}\/\d{2,4}\s+(1[012]|0?[1-9]){1}:(0?[1-5]|[0-6][0-9]){1}:(0?[0-6]|[0-6][0-9]){1}\s+(am|pm|AM|PM){1})$/,
					"alertText": "* Érvénytelen dátum vagy dátumformátum",
					"alertText2": "Elvárt formátum: ",
					"alertText3": "mm/dd/yyyy hh:mm:ss AM|PM or ",
					"alertText4": "yyyy-mm-dd hh:mm:ss AM|PM"
				}
			};

		}
	};

	$.validationEngineLanguage.newLang();

})(jQuery);