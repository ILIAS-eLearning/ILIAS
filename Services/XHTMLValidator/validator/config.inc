<?php
/* 
   +----------------------------------------------------------------------+
   | HTML/XML Validator                                                   |
   +----------------------------------------------------------------------+
   | Copyright (c) 2004-2005 Nuno Lopes                                   |
   +----------------------------------------------------------------------+
   | This program is free software; you can redistribute it and/or        |
   | modify it under the terms of the GNU Lesser General Public           |
   | License as published by the Free Software Foundation; either         |
   | version 2.1 of the License, or (at your option) any later version.   |
   |                                                                      |
   | This program is distributed in the hope that it will be useful,      |
   | but WITHOUT ANY WARRANTY; without even the implied warranty of       |
   | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU    |
   | Lesser General Public License for more details.                      |
   |                                                                      |
   | You should have received a copy of the GNU Lesser General Public     |
   | License along with this library; if not, write to the Free Software  |
   | Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA             |
   | 02111-1307  USA.                                                     |
   +----------------------------------------------------------------------+
   |                     http://validator.aborla.net/                     |
   +----------------------------------------------------------------------+

     $Id$
*/

//error_reporting(E_ALL | E_STRICT);

ini_set('arg_separator.output', '&amp;');
ini_set('default_socket_timeout', 10);
ini_set('default_charset', 'UTF-8');
ini_set('user_agent', 'HTML/XML Validator (http://validator.aborla.net)');


$valid_langs = array('en' => 'English',
		'ca' => 'Català',
		'da' => 'Dansk',
		'es' => 'Español',
		'eu' => 'Euskara',
		'it' => 'Italiano',
		'nl' => 'Nederlands',
		'nb' => 'Norsk (Bokmål)',
		'pl' => 'Polski',
		'pt' => 'Português',
		'sv' => 'Svenska',
		'tr' => 'Türkçe'
		);


/* oficial IANA assignments/aliases
list at: http://www.iana.org/assignments/character-sets */

$charset_aliases = array(
			/* Big5 */
			'csbig5'          => 'big5',

			/* ISO-2022-X */
			'iso-2022-cn'     => 'iso2022cn',
			'iso-2022-cn-ext' => 'iso2022cnext',
			'iso-2022-jp'     => 'iso2022jp',
			'csISO2022jp'     => 'iso2022jp',
			'iso-2022-jp-2'   => 'iso2022jp2',
			'csISO2022jp2'    => 'iso2022jp2',
			'iso-2022-kr'     => 'iso2022kr',
			'csISO2022kr'     => 'iso2022kr',

			/* ISO-8859-1 */
			'cp819'           => 'latin1',
			'csisolatin1'     => 'latin1',
			'ibm819'          => 'latin1',
			'iso-8859-1'      => 'latin1',
			'iso_8859-1'      => 'latin1',
			'iso-ir-100'      => 'latin1',
			'l1'              => 'latin1',

			/* iso-8859-15 */
			'iso-8859-15'     => 'latin0',
			'iso_8859-15'     => 'latin0',
			'latin-9'         => 'latin0',

			/* Mac */
			'macintosh'       => 'mac',
			'csmacintosh'     => 'mac',

			/* Shift_JIS */
			'csshiftjis'      => 'shiftjis',
			'ms_kanji'        => 'shiftjis',
			'shift_jis'       => 'shiftjis',

			/* UTF-8 */
			'utf-8'           => 'utf8',

			/* windows-1252 */
			'cp1252'          => 'win1252',
			'windows-1252'    => 'win1252'
			);


/* Array to convert to readable/iconv format */
$charsets_readable = array(
		'big5'     => 'Big5',
		'latin0'   => 'ISO-8859-15',
		'latin1'   => 'ISO-8859-1',
		'shiftjis' => 'Shift_JIS'
		);

/* Array to convert charsers to tidy naming */
$charsets_tidy = array(
		'iso2022cn'    => 'iso2022',
		'iso2022cnext' => 'iso2022',
		'iso2022jp'    => 'iso2022',
		'iso2022jp2'   => 'iso2022',
		'iso2022kr'    => 'iso2022'
		);



/* ========= List of Tidy options ===========

$opts['name_of_option'] =
type => select|bool|number|string
default => default value

select type only:
values => array(opt_value => friendly name)

number type only:
min/max => min/max values for input

string type only:
length => max length for input

*/

$opts['output'] = array('type' => 'select',
			'default' => 'xhtml',
			'values' => array(
				'html'  => 'HTML',
				'xhtml' => 'XHTML',
				'xml'   => 'XML'
				)
			);

$opts['charset'] = array('type' => 'select',
			'default' => 'auto',
			'values' => array(
				'auto'         => 'auto',
				'big5'         => 'Big5 (Chinese Traditional)',
				'iso2022cn'    => 'ISO-2022-CN',
				'iso2022cnext' => 'ISO-2022-CN-EXT',
				'iso2022jp'    => 'ISO-2022-JP',
				'iso2022jp2'   => 'ISO-2022-JP-2',
				'iso2022kr'    => 'ISO-2022-KR',
				'latin1'       => 'ISO-8859-1 [default]',
				'latin0'       => 'ISO-8859-15',
				'mac'          => 'Macintosh',
				'shiftjis'     => 'Shift_JIS (Japanese)',
				'win1252'      => 'windows-1252',
				'utf8'         => 'UTF-8'
				)
			);

$opts['accessibility-check'] = array('type' => 'select',
				'default'   => 0,
				'values'    => array(0, 1, 2, 3)
				);

$opts['alt-text'] = array('type' => 'string',
			'default' => '',
			'length'  => 100
			);

$opts['indent'] = array('type' => 'bool',
			'default' => 0);

$opts['indent-spaces'] = array('type' => 'number',
			'default' => 2,
			'min'     => 0,
			'max'     => 10);

$opts['hide-comments'] = array('type' => 'bool',
				'default' => 0); 

$opts['spellcheck'] = array('type' => 'select',
			'default'   => 'no',
			'values'    => array(
				'no'    => '$message[25]',
				'en_CA' => '$langs["en_CA"]',
				'en_GB' => '$langs["en_GB"]',
				'en_US' => '$langs["en_US"]',
				'ca'    => '$langs["ca"]',
				'da'    => '$langs["da"]',
				'es'    => '$langs["es"]',
				'it'    => '$langs["it"]',
				'nb'    => '$langs["nb"]',
				'nl'    => '$langs["nl"]',
				'pt'    => '$langs["pt"]',
				'pl'    => '$langs["pl"]',
				'sv'    => '$langs["sv"]',
				'tr'    => '$langs["tr"]'
				)
			);

$opts['wrap'] = array('type' => 'number',
			'default' => 200,
			'min'     => 0,
			'max'     => 500);
?>
