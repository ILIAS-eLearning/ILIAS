<?php

/**
 * Class ilRis
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilRis {

	/**
	 * @var array
	 *
	 * @source https://en.wikipedia.org/wiki/RIS_(file_format)
	 */
	protected static $standard_fields = array(
		'A2',
		// Secondary Author (each author on its own line preceded by the tag)
		'A3',
		// Tertiary Author (each author on its own line preceded by the tag)
		'A4',
		// Subsidiary Author (each author on its own line preceded by the tag)
		'AB',
		// Abstract
		'AD',
		// Author Address
		'AN',
		// Accession Number
		'AU',
		// Author (each author on its own line preceded by the tag)
		'C1',
		// Custom 1
		'C2',
		// Custom 2
		'C3',
		// Custom 3
		'C4',
		// Custom 4
		'C5',
		// Custom 5
		'C6',
		// Custom 6
		'C7',
		// Custom 7
		'C8',
		// Custom 8
		'CA',
		// Caption
		'CN',
		// Call Number
		'CY',
		// Place Published
		'DA',
		// Date
		'DB',
		// Name of Database
		'DO',
		// DOI
		'DP',
		// Database Provider
		'EP',
		// End Page
		'ET',
		// Edition
		'IS',
		// Number
		'J2',
		// Alternate Title (this field is used for the abbreviated title of a book or journal name)
		'KW',
		// Keywords (keywords should be entered each on its own line preceded by the tag)
		'L1',
		// File Attachments (this is a link to a local file on the users system not a URL link)
		'L4',
		// Figure (this is also meant to be a link to a local file on the users's system and not a URL link)
		'LA',
		// Language
		'LB',
		// Label
		'M1',
		// Number
		'M3',
		// Type of Work
		'N1',
		// Notes
		'NV',
		// Number of Volumes
		'OP',
		// Original Publication
		'PB',
		// Publisher
		'PY',
		// Year
		'RI',
		// Reviewed Item
		'RN',
		// Research Notes
		'RP',
		// Reprint Edition
		'SE',
		// Section
		'SN',
		// ISBN/ISSN
		'SP',
		// Start Page
		'ST',
		// Short Title
		'T1',
		// Primary Title
		'T2',
		// Secondary Title
		'T3',
		// Tertiary Title
		'TA',
		// Translated Author
		'TI',
		// Title
		'TT',
		// Translated Title
		'UR',
		// URL
		'VL',
		// Volume
		'Y2',
		// Access Date
	);
	/**
	 * @var array
	 */
	protected static $entry_types = array(
		'ABST',
		'ADVS',
		'ART',
		'BILL',
		'BOOK',
		'CASE',
		'CHAP',
		'COMP',
		'CONF',
		'CTLG',
		'DATA',
		'ELEC',
		'GEN',
		'HEAR',
		'ICOMM',
		'INPR',
		'JFULL',
		'JOUR',
		'MAP',
		'MGZN',
		'MPCT',
		'MUSIC',
		'NEWS',
		'PAMP',
		'PAT',
		'PCOMM',
		'RPRT',
		'SER',
		'SLIDE',
		'SOUND',
		'STAT',
		'THES',
		'UNBILl',
		'UNPB',
		'VIDEO',
	);


	/**
	 * @param $field_name
	 *
	 * @return bool
	 */
	public static function isStandardField($field_name) {
		return in_array(strtoupper($field_name), self::$standard_fields);
	}


	/**
	 * @param $entry_ype
	 *
	 * @return bool
	 */
	public static function isEntryType($entry_ype) {
		return in_array(strtoupper($entry_ype), self::$entry_types);
	}
}

?>
