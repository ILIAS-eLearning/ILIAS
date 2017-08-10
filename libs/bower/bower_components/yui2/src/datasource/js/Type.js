/****************************************************************************/
/****************************************************************************/
/****************************************************************************/

/**
 * The static Number class provides helper functions to deal with data of type
 * Number.
 *
 * @namespace YAHOO.util
 * @requires yahoo
 * @class Number
 * @static
 */
 YAHOO.util.Number = {
 
     /**
     * Takes a native JavaScript Number and formats to a string for display.
     *
     * @method format
     * @param nData {Number} Number.
     * @param oConfig {Object} (Optional) Optional configuration values:
     *  <dl>
     *   <dt>format</dt>
     *   <dd>String used as a template for formatting positive numbers.
     *   {placeholders} in the string are applied from the values in this
     *   config object. {number} is used to indicate where the numeric portion
     *   of the output goes.  For example &quot;{prefix}{number} per item&quot;
     *   might yield &quot;$5.25 per item&quot;.  The only required
     *   {placeholder} is {number}.</dd>
     *
     *   <dt>negativeFormat</dt>
     *   <dd>Like format, but applied to negative numbers.  If set to null,
     *   defaults from the configured format, prefixed with -.  This is
     *   separate from format to support formats like &quot;($12,345.67)&quot;.
     *
     *   <dt>prefix {String} (deprecated, use format/negativeFormat)</dt>
     *   <dd>String prepended before each number, like a currency designator "$"</dd>
     *   <dt>decimalPlaces {Number}</dt>
     *   <dd>Number of decimal places to round.</dd>
     *
     *   <dt>decimalSeparator {String}</dt>
     *   <dd>Decimal separator</dd>
     *
     *   <dt>thousandsSeparator {String}</dt>
     *   <dd>Thousands separator</dd>
     *
     *   <dt>suffix {String} (deprecated, use format/negativeFormat)</dt>
     *   <dd>String appended after each number, like " items" (note the space)</dd>
     *  </dl>
     * @return {String} Formatted number for display. Note, the following values
     * return as "": null, undefined, NaN, "".
     */
    format : function(n, cfg) {
        if (n === '' || n === null || !isFinite(n)) {
            return '';
        }

        n   = +n;
        cfg = YAHOO.lang.merge(YAHOO.util.Number.format.defaults, (cfg || {}));

        var stringN = n+'',
            absN   = Math.abs(n),
            places = cfg.decimalPlaces || 0,
            sep    = cfg.thousandsSeparator,
            negFmt = cfg.negativeFormat || ('-' + cfg.format),
            s, bits, i, precision;

        if (negFmt.indexOf('#') > -1) {
            // for backward compatibility of negativeFormat supporting '-#'
            negFmt = negFmt.replace(/#/, cfg.format);
        }

        if (places < 0) {
            // Get rid of the decimal info
            s = absN - (absN % 1) + '';
            i = s.length + places;

            // avoid 123 vs decimalPlaces -4 (should return "0")
            if (i > 0) {
                // leverage toFixed by making 123 => 0.123 for the rounding
                // operation, then add the appropriate number of zeros back on
                s = Number('.' + s).toFixed(i).slice(2) +
                    new Array(s.length - i + 1).join('0');
            } else {
                s = "0";
            }
        } else {
            // Avoid toFixed on floats:
            // Bug 2528976
            // Bug 2528977
            var unfloatedN = absN+'';
            if(places > 0 || unfloatedN.indexOf('.') > 0) {
                var power = Math.pow(10, places);
                s = Math.round(absN * power) / power + '';
                var dot = s.indexOf('.'),
                    padding, zeroes;
                
                // Add padding
                if(dot < 0) {
                    padding = places;
                    zeroes = (Math.pow(10, padding) + '').substring(1);
                    if(places > 0) {
                        s = s + '.' + zeroes;
                    }
                }
                else {
                    padding = places - (s.length - dot - 1);
                    zeroes = (Math.pow(10, padding) + '').substring(1);
                    s = s + zeroes;
                }
            }
            else {
                s = absN.toFixed(places)+'';
            }
        }

        bits  = s.split(/\D/);

        if (absN >= 1000) {
            i  = bits[0].length % 3 || 3;

            bits[0] = bits[0].slice(0,i) +
                      bits[0].slice(i).replace(/(\d{3})/g, sep + '$1');

        }

        return YAHOO.util.Number.format._applyFormat(
            (n < 0 ? negFmt : cfg.format),
            bits.join(cfg.decimalSeparator),
            cfg);
    }
};

/**
 * <p>Default values for Number.format behavior.  Override properties of this
 * object if you want every call to Number.format in your system to use
 * specific presets.</p>
 *
 * <p>Available keys include:</p>
 * <ul>
 *   <li>format</li>
 *   <li>negativeFormat</li>
 *   <li>decimalSeparator</li>
 *   <li>decimalPlaces</li>
 *   <li>thousandsSeparator</li>
 *   <li>prefix/suffix or any other token you want to use in the format templates</li>
 * </ul>
 *
 * @property Number.format.defaults
 * @type {Object}
 * @static
 */
YAHOO.util.Number.format.defaults = {
    format : '{prefix}{number}{suffix}',
    negativeFormat : null, // defaults to -(format)
    decimalSeparator : '.',
    decimalPlaces    : null,
    thousandsSeparator : ''
};

/**
 * Apply any special formatting to the "d,ddd.dd" string.  Takes either the
 * cfg.format or cfg.negativeFormat template and replaces any {placeholders}
 * with either the number or a value from a so-named property of the config
 * object.
 *
 * @method Number.format._applyFormat
 * @static
 * @param tmpl {String} the cfg.format or cfg.numberFormat template string
 * @param num {String} the number with separators and decimalPlaces applied
 * @param data {Object} the config object, used here to populate {placeholder}s
 * @return {String} the number with any decorators added
 */
YAHOO.util.Number.format._applyFormat = function (tmpl, num, data) {
    return tmpl.replace(/\{(\w+)\}/g, function (_, token) {
        return token === 'number' ? num :
               token in data ? data[token] : '';
    });
};


/****************************************************************************/
/****************************************************************************/
/****************************************************************************/

(function () {

var xPad=function (x, pad, r)
{
    if(typeof r === 'undefined')
    {
        r=10;
    }
    for( ; parseInt(x, 10)<r && r>1; r/=10) {
        x = pad.toString() + x;
    }
    return x.toString();
};


/**
 * The static Date class provides helper functions to deal with data of type Date.
 *
 * @namespace YAHOO.util
 * @requires yahoo
 * @class Date
 * @static
 */
 var Dt = {
    formats: {
        a: function (d, l) { return l.a[d.getDay()]; },
        A: function (d, l) { return l.A[d.getDay()]; },
        b: function (d, l) { return l.b[d.getMonth()]; },
        B: function (d, l) { return l.B[d.getMonth()]; },
        C: function (d) { return xPad(parseInt(d.getFullYear()/100, 10), 0); },
        d: ['getDate', '0'],
        e: ['getDate', ' '],
        g: function (d) { return xPad(parseInt(Dt.formats.G(d)%100, 10), 0); },
        G: function (d) {
                var y = d.getFullYear();
                var V = parseInt(Dt.formats.V(d), 10);
                var W = parseInt(Dt.formats.W(d), 10);
    
                if(W > V) {
                    y++;
                } else if(W===0 && V>=52) {
                    y--;
                }
    
                return y;
            },
        H: ['getHours', '0'],
        I: function (d) { var I=d.getHours()%12; return xPad(I===0?12:I, 0); },
        j: function (d) {
                var gmd_1 = new Date('' + d.getFullYear() + '/1/1 GMT');
                var gmdate = new Date('' + d.getFullYear() + '/' + (d.getMonth()+1) + '/' + d.getDate() + ' GMT');
                var ms = gmdate - gmd_1;
                var doy = parseInt(ms/60000/60/24, 10)+1;
                return xPad(doy, 0, 100);
            },
        k: ['getHours', ' '],
        l: function (d) { var I=d.getHours()%12; return xPad(I===0?12:I, ' '); },
        m: function (d) { return xPad(d.getMonth()+1, 0); },
        M: ['getMinutes', '0'],
        p: function (d, l) { return l.p[d.getHours() >= 12 ? 1 : 0 ]; },
        P: function (d, l) { return l.P[d.getHours() >= 12 ? 1 : 0 ]; },
        s: function (d, l) { return parseInt(d.getTime()/1000, 10); },
        S: ['getSeconds', '0'],
        u: function (d) { var dow = d.getDay(); return dow===0?7:dow; },
        U: function (d) {
                var doy = parseInt(Dt.formats.j(d), 10);
                var rdow = 6-d.getDay();
                var woy = parseInt((doy+rdow)/7, 10);
                return xPad(woy, 0);
            },
        V: function (d) {
                var woy = parseInt(Dt.formats.W(d), 10);
                var dow1_1 = (new Date('' + d.getFullYear() + '/1/1')).getDay();
                // First week is 01 and not 00 as in the case of %U and %W,
                // so we add 1 to the final result except if day 1 of the year
                // is a Monday (then %W returns 01).
                // We also need to subtract 1 if the day 1 of the year is 
                // Friday-Sunday, so the resulting equation becomes:
                var idow = woy + (dow1_1 > 4 || dow1_1 <= 1 ? 0 : 1);
                if(idow === 53 && (new Date('' + d.getFullYear() + '/12/31')).getDay() < 4)
                {
                    idow = 1;
                }
                else if(idow === 0)
                {
                    idow = Dt.formats.V(new Date('' + (d.getFullYear()-1) + '/12/31'));
                }
    
                return xPad(idow, 0);
            },
        w: 'getDay',
        W: function (d) {
                var doy = parseInt(Dt.formats.j(d), 10);
                var rdow = 7-Dt.formats.u(d);
                var woy = parseInt((doy+rdow)/7, 10);
                return xPad(woy, 0, 10);
            },
        y: function (d) { return xPad(d.getFullYear()%100, 0); },
        Y: 'getFullYear',
        z: function (d) {
                var o = d.getTimezoneOffset();
                var H = xPad(parseInt(Math.abs(o/60), 10), 0);
                var M = xPad(Math.abs(o%60), 0);
                return (o>0?'-':'+') + H + M;
            },
        Z: function (d) {
		var tz = d.toString().replace(/^.*:\d\d( GMT[+-]\d+)? \(?([A-Za-z ]+)\)?\d*$/, '$2').replace(/[a-z ]/g, '');
		if(tz.length > 4) {
			tz = Dt.formats.z(d);
		}
		return tz;
	},
        '%': function (d) { return '%'; }
    },

    aggregates: {
        c: 'locale',
        D: '%m/%d/%y',
        F: '%Y-%m-%d',
        h: '%b',
        n: '\n',
        r: 'locale',
        R: '%H:%M',
        t: '\t',
        T: '%H:%M:%S',
        x: 'locale',
        X: 'locale'
        //'+': '%a %b %e %T %Z %Y'
    },

     /**
     * Takes a native JavaScript Date and formats to string for display to user.
     *
     * @method format
     * @param oDate {Date} Date.
     * @param oConfig {Object} (Optional) Object literal of configuration values:
     *  <dl>
     *   <dt>format &lt;String&gt;</dt>
     *   <dd>
     *   <p>
     *   Any strftime string is supported, such as "%I:%M:%S %p". strftime has several format specifiers defined by the Open group at 
     *   <a href="http://www.opengroup.org/onlinepubs/007908799/xsh/strftime.html">http://www.opengroup.org/onlinepubs/007908799/xsh/strftime.html</a>
     *   </p>
     *   <p>   
     *   PHP added a few of its own, defined at <a href="http://www.php.net/strftime">http://www.php.net/strftime</a>
     *   </p>
     *   <p>
     *   This javascript implementation supports all the PHP specifiers and a few more.  The full list is below:
     *   </p>
     *   <dl>
     *    <dt>%a</dt> <dd>abbreviated weekday name according to the current locale</dd>
     *    <dt>%A</dt> <dd>full weekday name according to the current locale</dd>
     *    <dt>%b</dt> <dd>abbreviated month name according to the current locale</dd>
     *    <dt>%B</dt> <dd>full month name according to the current locale</dd>
     *    <dt>%c</dt> <dd>preferred date and time representation for the current locale</dd>
     *    <dt>%C</dt> <dd>century number (the year divided by 100 and truncated to an integer, range 00 to 99)</dd>
     *    <dt>%d</dt> <dd>day of the month as a decimal number (range 01 to 31)</dd>
     *    <dt>%D</dt> <dd>same as %m/%d/%y</dd>
     *    <dt>%e</dt> <dd>day of the month as a decimal number, a single digit is preceded by a space (range ' 1' to '31')</dd>
     *    <dt>%F</dt> <dd>same as %Y-%m-%d (ISO 8601 date format)</dd>
     *    <dt>%g</dt> <dd>like %G, but without the century</dd>
     *    <dt>%G</dt> <dd>The 4-digit year corresponding to the ISO week number</dd>
     *    <dt>%h</dt> <dd>same as %b</dd>
     *    <dt>%H</dt> <dd>hour as a decimal number using a 24-hour clock (range 00 to 23)</dd>
     *    <dt>%I</dt> <dd>hour as a decimal number using a 12-hour clock (range 01 to 12)</dd>
     *    <dt>%j</dt> <dd>day of the year as a decimal number (range 001 to 366)</dd>
     *    <dt>%k</dt> <dd>hour as a decimal number using a 24-hour clock (range 0 to 23); single digits are preceded by a blank. (See also %H.)</dd>
     *    <dt>%l</dt> <dd>hour as a decimal number using a 12-hour clock (range 1 to 12); single digits are preceded by a blank. (See also %I.) </dd>
     *    <dt>%m</dt> <dd>month as a decimal number (range 01 to 12)</dd>
     *    <dt>%M</dt> <dd>minute as a decimal number</dd>
     *    <dt>%n</dt> <dd>newline character</dd>
     *    <dt>%p</dt> <dd>either `AM' or `PM' according to the given time value, or the corresponding strings for the current locale</dd>
     *    <dt>%P</dt> <dd>like %p, but lower case</dd>
     *    <dt>%r</dt> <dd>time in a.m. and p.m. notation equal to %I:%M:%S %p</dd>
     *    <dt>%R</dt> <dd>time in 24 hour notation equal to %H:%M</dd>
     *    <dt>%s</dt> <dd>number of seconds since the Epoch, ie, since 1970-01-01 00:00:00 UTC</dd>
     *    <dt>%S</dt> <dd>second as a decimal number</dd>
     *    <dt>%t</dt> <dd>tab character</dd>
     *    <dt>%T</dt> <dd>current time, equal to %H:%M:%S</dd>
     *    <dt>%u</dt> <dd>weekday as a decimal number [1,7], with 1 representing Monday</dd>
     *    <dt>%U</dt> <dd>week number of the current year as a decimal number, starting with the
     *            first Sunday as the first day of the first week</dd>
     *    <dt>%V</dt> <dd>The ISO 8601:1988 week number of the current year as a decimal number,
     *            range 01 to 53, where week 1 is the first week that has at least 4 days
     *            in the current year, and with Monday as the first day of the week.</dd>
     *    <dt>%w</dt> <dd>day of the week as a decimal, Sunday being 0</dd>
     *    <dt>%W</dt> <dd>week number of the current year as a decimal number, starting with the
     *            first Monday as the first day of the first week</dd>
     *    <dt>%x</dt> <dd>preferred date representation for the current locale without the time</dd>
     *    <dt>%X</dt> <dd>preferred time representation for the current locale without the date</dd>
     *    <dt>%y</dt> <dd>year as a decimal number without a century (range 00 to 99)</dd>
     *    <dt>%Y</dt> <dd>year as a decimal number including the century</dd>
     *    <dt>%z</dt> <dd>numerical time zone representation</dd>
     *    <dt>%Z</dt> <dd>time zone name or abbreviation</dd>
     *    <dt>%%</dt> <dd>a literal `%' character</dd>
     *   </dl>
     *  </dd>
     * </dl>
     * @param sLocale {String} (Optional) The locale to use when displaying days of week,
     *  months of the year, and other locale specific strings.  The following locales are
     *  built in:
     *  <dl>
     *   <dt>en</dt>
     *   <dd>English</dd>
     *   <dt>en-US</dt>
     *   <dd>US English</dd>
     *   <dt>en-GB</dt>
     *   <dd>British English</dd>
     *   <dt>en-AU</dt>
     *   <dd>Australian English (identical to British English)</dd>
     *  </dl>
     *  More locales may be added by subclassing of YAHOO.util.DateLocale.
     *  See YAHOO.util.DateLocale for more information.
     * @return {HTML} Formatted date for display. Non-date values are passed
     * through as-is.
     * @sa YAHOO.util.DateLocale
     */
    format : function (oDate, oConfig, sLocale) {
        oConfig = oConfig || {};
        
        if(!(oDate instanceof Date)) {
            return YAHOO.lang.isValue(oDate) ? oDate : "";
        }

        var format = oConfig.format || "%m/%d/%Y";

        // Be backwards compatible, support strings that are
        // exactly equal to YYYY/MM/DD, DD/MM/YYYY and MM/DD/YYYY
        if(format === 'YYYY/MM/DD') {
            format = '%Y/%m/%d';
        } else if(format === 'DD/MM/YYYY') {
            format = '%d/%m/%Y';
        } else if(format === 'MM/DD/YYYY') {
            format = '%m/%d/%Y';
        }
        // end backwards compatibility block
 
        sLocale = sLocale || "en";

        // Make sure we have a definition for the requested locale, or default to en.
        if(!(sLocale in YAHOO.util.DateLocale)) {
            if(sLocale.replace(/-[a-zA-Z]+$/, '') in YAHOO.util.DateLocale) {
                sLocale = sLocale.replace(/-[a-zA-Z]+$/, '');
            } else {
                sLocale = "en";
            }
        }

        var aLocale = YAHOO.util.DateLocale[sLocale];

        var replace_aggs = function (m0, m1) {
            var f = Dt.aggregates[m1];
            return (f === 'locale' ? aLocale[m1] : f);
        };

        var replace_formats = function (m0, m1) {
            var f = Dt.formats[m1];
            if(typeof f === 'string') {             // string => built in date function
                return oDate[f]();
            } else if(typeof f === 'function') {    // function => our own function
                return f.call(oDate, oDate, aLocale);
            } else if(typeof f === 'object' && typeof f[0] === 'string') {  // built in function with padding
                return xPad(oDate[f[0]](), f[1]);
            } else {
                return m1;
            }
        };

        // First replace aggregates (run in a loop because an agg may be made up of other aggs)
        while(format.match(/%[cDFhnrRtTxX]/)) {
            format = format.replace(/%([cDFhnrRtTxX])/g, replace_aggs);
        }

        // Now replace formats (do not run in a loop otherwise %%a will be replace with the value of %a)
        var str = format.replace(/%([aAbBCdegGHIjklmMpPsSuUVwWyYzZ%])/g, replace_formats);

        replace_aggs = replace_formats = undefined;

        return str;
    }
 };
 
 YAHOO.namespace("YAHOO.util");
 YAHOO.util.Date = Dt;

/**
 * The DateLocale class is a container and base class for all
 * localised date strings used by YAHOO.util.Date. It is used
 * internally, but may be extended to provide new date localisations.
 *
 * To create your own DateLocale, follow these steps:
 * <ol>
 *  <li>Find an existing locale that matches closely with your needs</li>
 *  <li>Use this as your base class.  Use YAHOO.util.DateLocale if nothing
 *   matches.</li>
 *  <li>Create your own class as an extension of the base class using
 *   YAHOO.lang.merge, and add your own localisations where needed.</li>
 * </ol>
 * See the YAHOO.util.DateLocale['en-US'] and YAHOO.util.DateLocale['en-GB']
 * classes which extend YAHOO.util.DateLocale['en'].
 *
 * For example, to implement locales for French french and Canadian french,
 * we would do the following:
 * <ol>
 *  <li>For French french, we have no existing similar locale, so use
 *   YAHOO.util.DateLocale as the base, and extend it:
 *   <pre>
 *      YAHOO.util.DateLocale['fr'] = YAHOO.lang.merge(YAHOO.util.DateLocale, {
 *          a: ['dim', 'lun', 'mar', 'mer', 'jeu', 'ven', 'sam'],
 *          A: ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'],
 *          b: ['jan', 'f&eacute;v', 'mar', 'avr', 'mai', 'jun', 'jui', 'ao&ucirc;', 'sep', 'oct', 'nov', 'd&eacute;c'],
 *          B: ['janvier', 'f&eacute;vrier', 'mars', 'avril', 'mai', 'juin', 'juillet', 'ao&ucirc;t', 'septembre', 'octobre', 'novembre', 'd&eacute;cembre'],
 *          c: '%a %d %b %Y %T %Z',
 *          p: ['', ''],
 *          P: ['', ''],
 *          x: '%d.%m.%Y',
 *          X: '%T'
 *      });
 *   </pre>
 *  </li>
 *  <li>For Canadian french, we start with French french and change the meaning of \%x:
 *   <pre>
 *      YAHOO.util.DateLocale['fr-CA'] = YAHOO.lang.merge(YAHOO.util.DateLocale['fr'], {
 *          x: '%Y-%m-%d'
 *      });
 *   </pre>
 *  </li>
 * </ol>
 *
 * With that, you can use your new locales:
 * <pre>
 *    var d = new Date("2008/04/22");
 *    YAHOO.util.Date.format(d, {format: "%A, %d %B == %x"}, "fr");
 * </pre>
 * will return:
 * <pre>
 *    mardi, 22 avril == 22.04.2008
 * </pre>
 * And
 * <pre>
 *    YAHOO.util.Date.format(d, {format: "%A, %d %B == %x"}, "fr-CA");
 * </pre>
 * Will return:
 * <pre>
 *   mardi, 22 avril == 2008-04-22
 * </pre>
 * @namespace YAHOO.util
 * @requires yahoo
 * @class DateLocale
 */
 YAHOO.util.DateLocale = {
        a: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
        A: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
        b: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        B: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
        c: '%a %d %b %Y %T %Z',
        p: ['AM', 'PM'],
        P: ['am', 'pm'],
        r: '%I:%M:%S %p',
        x: '%d/%m/%y',
        X: '%T'
 };

 YAHOO.util.DateLocale['en'] = YAHOO.lang.merge(YAHOO.util.DateLocale, {});

 YAHOO.util.DateLocale['en-US'] = YAHOO.lang.merge(YAHOO.util.DateLocale['en'], {
        c: '%a %d %b %Y %I:%M:%S %p %Z',
        x: '%m/%d/%Y',
        X: '%I:%M:%S %p'
 });

 YAHOO.util.DateLocale['en-GB'] = YAHOO.lang.merge(YAHOO.util.DateLocale['en'], {
        r: '%l:%M:%S %P %Z'
 });
 YAHOO.util.DateLocale['en-AU'] = YAHOO.lang.merge(YAHOO.util.DateLocale['en']);

})();
