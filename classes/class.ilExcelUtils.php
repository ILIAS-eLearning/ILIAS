<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/*
* Utilities for Microsoft Excel Import/Export
*
* @author Helmut Schottmüller <hschottm@gmx.de>
* @version $Id$
*
*/

define ("CHARSET_LATIN1", "latin1");
define ("CHARSET_MACOS", "macos");

class ilExcelUtils
{
	function _convert_text($a_text, $a_target = CHARSET_LATIN1)
	{
		return utf8_decode($a_text);
		switch ($a_target)
		{
			case CHARSET_LATIN1:
				$charmap_latin1 = array(
					utf8_decode("ß") => chr(0xDF),
					utf8_decode("à") => chr(0xE0),
					utf8_decode("á") => chr(0xE1),
					utf8_decode("â") => chr(0xE2),
					utf8_decode("ã") => chr(0xE3),
					utf8_decode("ä") => chr(0xE4),
					utf8_decode("å") => chr(0xE5),
					utf8_decode("æ") => chr(0xE6),
					utf8_decode("À") => chr(0xC0),
					utf8_decode("Á") => chr(0xC1),
					utf8_decode("Â") => chr(0xC2),
					utf8_decode("Ã") => chr(0xC3),
					utf8_decode("Ä") => chr(0xC4),
					utf8_decode("Å") => chr(0xC5),
					utf8_decode("Æ") => chr(0xC6),
					utf8_decode("ç") => chr(0xE7),
					utf8_decode("Ç") => chr(0xC7),
					utf8_decode("ð") => chr(0xF0),
					utf8_decode("è") => chr(0xE8),
					utf8_decode("é") => chr(0xE9),
					utf8_decode("ê") => chr(0xEA),
					utf8_decode("ë") => chr(0xEB),
					utf8_decode("È") => chr(0xC8),
					utf8_decode("É") => chr(0xC9),
					utf8_decode("Ê") => chr(0xCA),
					utf8_decode("Ë") => chr(0xCB),
					utf8_decode("ì") => chr(0xEC),
					utf8_decode("í") => chr(0xED),
					utf8_decode("î") => chr(0xEE),
					utf8_decode("ï") => chr(0xEF),
					utf8_decode("Ì") => chr(0xCC),
					utf8_decode("Í") => chr(0xCD),
					utf8_decode("Î") => chr(0xCE),
					utf8_decode("Ï") => chr(0xCF),
					utf8_decode("ñ") => chr(0xF1),
					utf8_decode("Ñ") => chr(0xD1),
					utf8_decode("ò") => chr(0xF2),
					utf8_decode("ó") => chr(0xF3),
					utf8_decode("ô") => chr(0xF4),
					utf8_decode("õ") => chr(0xF5),
					utf8_decode("ö") => chr(0xF6),
					utf8_decode("ø") => chr(0xF8),
					utf8_decode("Ò") => chr(0xD2),
					utf8_decode("Ó") => chr(0xD3),
					utf8_decode("Ô") => chr(0xD4),
					utf8_decode("Õ") => chr(0xD5),
					utf8_decode("Ö") => chr(0xD6),
					utf8_decode("Ø") => chr(0xD8),
					utf8_decode("ù") => chr(0xF9),
					utf8_decode("ú") => chr(0xFA),
					utf8_decode("û") => chr(0xFB),
					utf8_decode("ü") => chr(0xFC),
					utf8_decode("Ù") => chr(0xD9),
					utf8_decode("Ú") => chr(0xDA),
					utf8_decode("Û") => chr(0xDB),
					utf8_decode("Ü") => chr(0xDC),
					utf8_decode("ý") => chr(0xFD),
					utf8_decode("ÿ") => chr(0xFF),
					utf8_decode("Ý") => chr(0xDD),
					utf8_decode("þ") => chr(0xFE),
					utf8_decode("Þ") => chr(0xDE)
				);
				return strtr(str_replace("<br />", " - ", utf8_decode($a_text)), $charmap_latin1);
				break;
			case CHARSET_MACOS:
				$charmap_macos = array(
					utf8_decode("ß") => chr(0xA7),
					utf8_decode("à") => chr(0x88),
					utf8_decode("á") => chr(0x87),
					utf8_decode("â") => chr(0x89),
					utf8_decode("ã") => chr(0x8B),
					utf8_decode("ä") => chr(0x8A),
					utf8_decode("å") => chr(0x8C),
					utf8_decode("æ") => chr(0xBE),
					utf8_decode("À") => chr(0xCB),
					utf8_decode("Á") => chr(0xE7),
					utf8_decode("Â") => chr(0xE5),
					utf8_decode("Ã") => chr(0xCC),
					utf8_decode("Ä") => chr(0x80),
					utf8_decode("Å") => chr(0x81),
					utf8_decode("Æ") => chr(0xAE),
					utf8_decode("ç") => chr(0x8D),
					utf8_decode("Ç") => chr(0x82),
					utf8_decode("ð") => chr(0xB6),
					utf8_decode("è") => chr(0x8F),
					utf8_decode("é") => chr(0x8E),
					utf8_decode("ê") => chr(0x90),
					utf8_decode("ë") => chr(0x91),
					utf8_decode("È") => chr(0xE9),
					utf8_decode("É") => chr(0x83),
					utf8_decode("Ê") => chr(0xE6),
					utf8_decode("Ë") => chr(0xE8),
					utf8_decode("ì") => chr(0x93),
					utf8_decode("í") => chr(0x92),
					utf8_decode("î") => chr(0x94),
					utf8_decode("ï") => chr(0x95),
					utf8_decode("Ì") => chr(0xED),
					utf8_decode("Í") => chr(0xEA),
					utf8_decode("Î") => chr(0xEB),
					utf8_decode("Ï") => chr(0xEC),
					utf8_decode("ñ") => chr(0x96),
					utf8_decode("Ñ") => chr(0x84),
					utf8_decode("ò") => chr(0x98),
					utf8_decode("ó") => chr(0x97),
					utf8_decode("ô") => chr(0x99),
					utf8_decode("õ") => chr(0x9B),
					utf8_decode("ö") => chr(0x9A),
					utf8_decode("ø") => chr(0xBF),
					utf8_decode("Ò") => chr(0xF1),
					utf8_decode("Ó") => chr(0xEE),
					utf8_decode("Ô") => chr(0xEF),
					utf8_decode("Õ") => chr(0xCD),
					utf8_decode("Ö") => chr(0x85),
					utf8_decode("Ø") => chr(0xAF),
					utf8_decode("ù") => chr(0x9D),
					utf8_decode("ú") => chr(0x9C),
					utf8_decode("û") => chr(0x9E),
					utf8_decode("ü") => chr(0x9F),
					utf8_decode("Ù") => chr(0xF4),
					utf8_decode("Ú") => chr(0xF2),
					utf8_decode("Û") => chr(0xF3),
					utf8_decode("Ü") => chr(0x86),
					utf8_decode("ý") => chr(0x79),
					utf8_decode("ÿ") => chr(0xD8),
					utf8_decode("Ý") => chr(0x59),
					utf8_decode("þ") => chr(0x20),
					utf8_decode("Þ") => chr(0x20)
				);
				return strtr(str_replace("<br />", " - ", utf8_decode($a_text)), $charmap_macos);
				break;
			case "unknown":
			default:
				return $a_text;
		}
	}

} // END class.ilExcelUtils.php
?>
