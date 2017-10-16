(function() {
/**
 * Add style management functionality to DOM.
 * @module dom
 * @class Color
 * @namespace YAHOO.util.Dom
 */

var TO_STRING = 'toString',
    PARSE_INT = parseInt,
    RE = RegExp,
    Y = YAHOO.util;

Y.Dom.Color = {
    /**
    * @property KEYWORDS
    * @type Object
    * @description Color keywords used when converting to Hex
    */
    KEYWORDS: {
        black: '000',
        silver: 'c0c0c0',
        gray: '808080',
        white: 'fff',
        maroon: '800000',
        red: 'f00',
        purple: '800080',
        fuchsia: 'f0f',
        green: '008000',
        lime: '0f0',
        olive: '808000',
        yellow: 'ff0',
        navy: '000080',
        blue: '00f',
        teal: '008080',
        aqua: '0ff'
    },
    /**
    * @property re_RGB
    * @private
    * @type Regex
    * @description Regex to parse rgb(0,0,0) formatted strings
    */
    re_RGB: /^rgb\(([0-9]+)\s*,\s*([0-9]+)\s*,\s*([0-9]+)\)$/i,
    /**
    * @property re_hex
    * @private
    * @type Regex
    * @description Regex to parse #123456 formatted strings
    */
    re_hex: /^#?([0-9A-F]{2})([0-9A-F]{2})([0-9A-F]{2})$/i,
    /**
    * @property re_hex3
    * @private
    * @type Regex
    * @description Regex to parse #123 formatted strings
    */
    re_hex3: /([0-9A-F])/gi,
    /**
    * @method toRGB
    * @description Converts a hex or color string to an rgb string: rgb(0,0,0)
    * @param {String} val The string to convert to RGB notation.
    * @returns {String} The converted string
    */
    toRGB: function(val) {
        if (!Y.Dom.Color.re_RGB.test(val)) {
            val = Y.Dom.Color.toHex(val);
        }

        if(Y.Dom.Color.re_hex.exec(val)) {
            val = 'rgb(' + [
                PARSE_INT(RE.$1, 16),
                PARSE_INT(RE.$2, 16),
                PARSE_INT(RE.$3, 16)
            ].join(', ') + ')';
        }
        return val;
    },
    /**
    * @method toHex
    * @description Converts an rgb or color string to a hex string: #123456
    * @param {String} val The string to convert to hex notation.
    * @returns {String} The converted string
    */
    toHex: function(val) {
        val = Y.Dom.Color.KEYWORDS[val] || val;
        if (Y.Dom.Color.re_RGB.exec(val)) {
            val = [
                Number(RE.$1).toString(16),
                Number(RE.$2).toString(16),
                Number(RE.$3).toString(16)
            ];

            for (var i = 0; i < val.length; i++) {
                if (val[i].length < 2) {
                    val[i] = '0' + val[i];
                }
            }

            val = val.join('');
        }

        if (val.length < 6) {
            val = val.replace(Y.Dom.Color.re_hex3, '$1$1');
        }

        if (val !== 'transparent' && val.indexOf('#') < 0) {
            val = '#' + val;
        }

        return val.toUpperCase();
    }
};
}());
