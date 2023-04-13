<?php
/**
 * XHTML sanitizer for MediaWiki
 *
 * Copyright (C) 2002-2005 Brion Vibber <brion@pobox.com> et al
 * http://www.mediawiki.org/
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @addtogroup Parser
 */

/**
 * Regular expression to match various types of character references in
 * Sanitizer::normalizeCharReferences and Sanitizer::decodeCharReferences
 */
define(
    'MW_CHAR_REFS_REGEX',
    '/&([A-Za-z0-9\x80-\xff]+);
	 |&\#([0-9]+);
	 |&\#x([0-9A-Za-z]+);
	 |&\#X([0-9A-Za-z]+);
	 |(&)/x'
);

/**
 * Regular expression to match HTML/XML attribute pairs within a tag.
 * Allows some... latitude.
 * Used in Sanitizer::fixTagAttributes and Sanitizer::decodeTagAttributes
 */
$attrib = '[A-Za-z0-9]';
$space = '[\x09\x0a\x0d\x20]';
define(
    'MW_ATTRIBS_REGEX',
    "/(?:^|$space)($attrib+)
	  ($space*=$space*
		(?:
		 # The attribute value: quoted or alone
		  \"([^<\"]*)\"
		 | '([^<']*)'
		 |  ([a-zA-Z0-9!#$%&()*,\\-.\\/:;<>?@[\\]^_`{|}~]+)
		 |  (\#[0-9a-fA-F]+) # Technically wrong, but lots of
							 # colors are specified like this.
							 # We'll be normalizing it.
		)
	   )?(?=$space|\$)/sx"
);

/**
 * List of all named character entities defined in HTML 4.01
 * http://www.w3.org/TR/html4/sgml/entities.html
 * @private
 */
global $wgHtmlEntities;
$wgHtmlEntities = array(
    'Aacute' => 193,
    'aacute' => 225,
    'Acirc' => 194,
    'acirc' => 226,
    'acute' => 180,
    'AElig' => 198,
    'aelig' => 230,
    'Agrave' => 192,
    'agrave' => 224,
    'alefsym' => 8501,
    'Alpha' => 913,
    'alpha' => 945,
    'amp' => 38,
    'and' => 8743,
    'ang' => 8736,
    'Aring' => 197,
    'aring' => 229,
    'asymp' => 8776,
    'Atilde' => 195,
    'atilde' => 227,
    'Auml' => 196,
    'auml' => 228,
    'bdquo' => 8222,
    'Beta' => 914,
    'beta' => 946,
    'brvbar' => 166,
    'bull' => 8226,
    'cap' => 8745,
    'Ccedil' => 199,
    'ccedil' => 231,
    'cedil' => 184,
    'cent' => 162,
    'Chi' => 935,
    'chi' => 967,
    'circ' => 710,
    'clubs' => 9827,
    'cong' => 8773,
    'copy' => 169,
    'crarr' => 8629,
    'cup' => 8746,
    'curren' => 164,
    'dagger' => 8224,
    'Dagger' => 8225,
    'darr' => 8595,
    'dArr' => 8659,
    'deg' => 176,
    'Delta' => 916,
    'delta' => 948,
    'diams' => 9830,
    'divide' => 247,
    'Eacute' => 201,
    'eacute' => 233,
    'Ecirc' => 202,
    'ecirc' => 234,
    'Egrave' => 200,
    'egrave' => 232,
    'empty' => 8709,
    'emsp' => 8195,
    'ensp' => 8194,
    'Epsilon' => 917,
    'epsilon' => 949,
    'equiv' => 8801,
    'Eta' => 919,
    'eta' => 951,
    'ETH' => 208,
    'eth' => 240,
    'Euml' => 203,
    'euml' => 235,
    'euro' => 8364,
    'exist' => 8707,
    'fnof' => 402,
    'forall' => 8704,
    'frac12' => 189,
    'frac14' => 188,
    'frac34' => 190,
    'frasl' => 8260,
    'Gamma' => 915,
    'gamma' => 947,
    'ge' => 8805,
    'gt' => 62,
    'harr' => 8596,
    'hArr' => 8660,
    'hearts' => 9829,
    'hellip' => 8230,
    'Iacute' => 205,
    'iacute' => 237,
    'Icirc' => 206,
    'icirc' => 238,
    'iexcl' => 161,
    'Igrave' => 204,
    'igrave' => 236,
    'image' => 8465,
    'infin' => 8734,
    'int' => 8747,
    'Iota' => 921,
    'iota' => 953,
    'iquest' => 191,
    'isin' => 8712,
    'Iuml' => 207,
    'iuml' => 239,
    'Kappa' => 922,
    'kappa' => 954,
    'Lambda' => 923,
    'lambda' => 955,
    'lang' => 9001,
    'laquo' => 171,
    'larr' => 8592,
    'lArr' => 8656,
    'lceil' => 8968,
    'ldquo' => 8220,
    'le' => 8804,
    'lfloor' => 8970,
    'lowast' => 8727,
    'loz' => 9674,
    'lrm' => 8206,
    'lsaquo' => 8249,
    'lsquo' => 8216,
    'lt' => 60,
    'macr' => 175,
    'mdash' => 8212,
    'micro' => 181,
    'middot' => 183,
    'minus' => 8722,
    'Mu' => 924,
    'mu' => 956,
    'nabla' => 8711,
    'nbsp' => 160,
    'ndash' => 8211,
    'ne' => 8800,
    'ni' => 8715,
    'not' => 172,
    'notin' => 8713,
    'nsub' => 8836,
    'Ntilde' => 209,
    'ntilde' => 241,
    'Nu' => 925,
    'nu' => 957,
    'Oacute' => 211,
    'oacute' => 243,
    'Ocirc' => 212,
    'ocirc' => 244,
    'OElig' => 338,
    'oelig' => 339,
    'Ograve' => 210,
    'ograve' => 242,
    'oline' => 8254,
    'Omega' => 937,
    'omega' => 969,
    'Omicron' => 927,
    'omicron' => 959,
    'oplus' => 8853,
    'or' => 8744,
    'ordf' => 170,
    'ordm' => 186,
    'Oslash' => 216,
    'oslash' => 248,
    'Otilde' => 213,
    'otilde' => 245,
    'otimes' => 8855,
    'Ouml' => 214,
    'ouml' => 246,
    'para' => 182,
    'part' => 8706,
    'permil' => 8240,
    'perp' => 8869,
    'Phi' => 934,
    'phi' => 966,
    'Pi' => 928,
    'pi' => 960,
    'piv' => 982,
    'plusmn' => 177,
    'pound' => 163,
    'prime' => 8242,
    'Prime' => 8243,
    'prod' => 8719,
    'prop' => 8733,
    'Psi' => 936,
    'psi' => 968,
    'quot' => 34,
    'radic' => 8730,
    'rang' => 9002,
    'raquo' => 187,
    'rarr' => 8594,
    'rArr' => 8658,
    'rceil' => 8969,
    'rdquo' => 8221,
    'real' => 8476,
    'reg' => 174,
    'rfloor' => 8971,
    'Rho' => 929,
    'rho' => 961,
    'rlm' => 8207,
    'rsaquo' => 8250,
    'rsquo' => 8217,
    'sbquo' => 8218,
    'Scaron' => 352,
    'scaron' => 353,
    'sdot' => 8901,
    'sect' => 167,
    'shy' => 173,
    'Sigma' => 931,
    'sigma' => 963,
    'sigmaf' => 962,
    'sim' => 8764,
    'spades' => 9824,
    'sub' => 8834,
    'sube' => 8838,
    'sum' => 8721,
    'sup' => 8835,
    'sup1' => 185,
    'sup2' => 178,
    'sup3' => 179,
    'supe' => 8839,
    'szlig' => 223,
    'Tau' => 932,
    'tau' => 964,
    'there4' => 8756,
    'Theta' => 920,
    'theta' => 952,
    'thetasym' => 977,
    'thinsp' => 8201,
    'THORN' => 222,
    'thorn' => 254,
    'tilde' => 732,
    'times' => 215,
    'trade' => 8482,
    'Uacute' => 218,
    'uacute' => 250,
    'uarr' => 8593,
    'uArr' => 8657,
    'Ucirc' => 219,
    'ucirc' => 251,
    'Ugrave' => 217,
    'ugrave' => 249,
    'uml' => 168,
    'upsih' => 978,
    'Upsilon' => 933,
    'upsilon' => 965,
    'Uuml' => 220,
    'uuml' => 252,
    'weierp' => 8472,
    'Xi' => 926,
    'xi' => 958,
    'Yacute' => 221,
    'yacute' => 253,
    'yen' => 165,
    'Yuml' => 376,
    'yuml' => 255,
    'Zeta' => 918,
    'zeta' => 950,
    'zwj' => 8205,
    'zwnj' => 8204 );

/**
 * Character entity aliases accepted by MediaWiki
 */
global $wgHtmlEntityAliases;
$wgHtmlEntityAliases = array(
    'רלמ' => 'rlm',
    'رلم' => 'rlm',
);


/**
 * XHTML sanitizer for MediaWiki
 * @addtogroup Parser
 */
class Sanitizer
{
    /**
     * Returns true if a given Unicode codepoint is a valid character in XML.
     * @param int $codepoint
     * @return bool
     */
    private static function validateCodepoint($codepoint)
    {
        return ($codepoint == 0x09)
            || ($codepoint == 0x0a)
            || ($codepoint == 0x0d)
            || ($codepoint >= 0x20 && $codepoint <= 0xd7ff)
            || ($codepoint >= 0xe000 && $codepoint <= 0xfffd)
            || ($codepoint >= 0x10000 && $codepoint <= 0x10ffff);
    }

    /**
     * Decode any character references, numeric or named entities,
     * in the text and return a UTF-8 string.
     *
     * @param string $text
     * @return string
     * @public
     * @static
     */
    public static function decodeCharReferences($text)
    {
        return preg_replace_callback(
            MW_CHAR_REFS_REGEX,
            array( 'Sanitizer', 'decodeCharReferencesCallback' ),
            $text
        );
    }

    /**
     * @param string $matches
     * @return string
     */
    public static function decodeCharReferencesCallback($matches)
    {
        if ($matches[1] != '') {
            return Sanitizer::decodeEntity($matches[1]);
        } elseif ($matches[2] != '') {
            return  Sanitizer::decodeChar(intval($matches[2]));
        } elseif ($matches[3] != '') {
            return  Sanitizer::decodeChar(hexdec($matches[3]));
        } elseif ($matches[4] != '') {
            return  Sanitizer::decodeChar(hexdec($matches[4]));
        }
        # Last case should be an ampersand by itself
        return $matches[0];
    }

    /**
     * Return UTF-8 string for a codepoint if that is a valid
     * character reference, otherwise U+FFFD REPLACEMENT CHARACTER.
     * @param int $codepoint
     * @return string
     * @private
     */
    public static function decodeChar($codepoint)
    {
        if (Sanitizer::validateCodepoint($codepoint)) {
            return "";
        //return codepointToUtf8($codepoint);
        } else {
            return UTF8_REPLACEMENT;
        }
    }

    /**
     * If the named entity is defined in the HTML 4.0/XHTML 1.0 DTD,
     * return the UTF-8 encoding of that character. Otherwise, returns
     * pseudo-entity source (eg &foo;)
     *
     * @param string $name
     * @return string
     */
    public static function decodeEntity($name)
    {
        global $wgHtmlEntities, $wgHtmlEntityAliases;

        if (isset($wgHtmlEntityAliases[$name])) {
            $name = $wgHtmlEntityAliases[$name];
        }
        if (isset($wgHtmlEntities[$name])) {
            return "";
        //return codepointToUtf8($wgHtmlEntities[$name]);
        } else {
            return "&$name;";
        }
    }
}
