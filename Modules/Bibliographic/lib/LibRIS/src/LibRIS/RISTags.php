<?php

namespace LibRIS;

class RISTags {

	public static function getTags() {
		return array_keys(self::$tagMap);
	}


	public static function getTypes() {
		return array_keys(self::$typeMap);
	}


	public static function describeTag($tag) {
		return self::$tagMap[$tag];
	}


	public static function describeType($type) {
		return self::$typeMap[$type];
	}


	/**
	 * The definitive list of all fields.
	 *
	 * @var array
	 * @see http://en.wikipedia.org/wiki/RIS_%28file_format%29
	 * @see http://www.refman.com/support/risformat_intro.asp
	 */
	public static $tagMap = array(
		'TY' => 'Type',
		'ID' => 'Reference ID',
		'T1' => 'Title',
		'TI' => 'Book title',
		'CT' => 'Title of unpublished reference',
		'A1' => 'Primary author',
		'A2' => 'Secondary author',
		'AU' => 'Author',
		'Y1' => 'Primary date',
		'PY' => 'Publication year',
		'N1' => 'Notes',
		'KW' => 'Keywords',
		'RP' => 'Reprint status',
		'SP' => 'Start page',
		'EP' => 'Ending page',
		'JF' => 'Periodical full name',
		'JO' => 'Periodical standard abbreviation',
		'JA' => 'Periodical in which article was published',
		'J1' => 'Periodical name - User abbreviation 1',
		'J2' => 'Periodical name - User abbreviation 2',
		'VL' => 'Volume',
		'IS' => 'Issue',
		'T2' => 'Title secondary',
		'CY' => 'City of Publication',
		'PB' => 'Publisher',
		'U1' => 'User 1',
		'U2' => 'User 2',
		'U3' => 'User 3',
		'U4' => 'User 4',
		'U5' => 'User 5',
		'T3' => 'Title series',
		'N2' => 'Abstract',
		'SN' => 'ISSN/ISBN/ASIN',
		'AV' => 'Availability',
		'M1' => 'Misc. 1',
		'M2' => 'Misc. 2',
		'M3' => 'Misc. 3',
		'AD' => 'Address',
		'UR' => 'URL',
		'L1' => 'Link to PDF',
		'L2' => 'Link to Full-text',
		'L3' => 'Related records',
		'L4' => 'Images',
		'ER' => 'End of Reference',
		// Unsure about the origin of these
		'Y2' => 'Primary date 2',
		'BT' => 'Institution [?]',
	);
	public static $tagDescriptions = array(
		'TY' => 'Type of reference (must be the first tag)',
		'ID' => 'Reference ID (not imported to reference software)',
		'T1' => 'Primary title',
		'TI' => 'Book title',
		'CT' => 'Title of unpublished reference',
		'A1' => 'Primary author',
		'A2' => 'Secondary author (each name on separate line)',
		'AU' => 'Author (syntax. Last name, First name, Suffix)',
		'Y1' => 'Primary date',
		'PY' => 'Publication year (YYYY/MM/DD)',
		'N1' => 'Notes ',
		'KW' => 'Keywords (each keyword must be on separate line preceded KW -)',
		'RP' => 'Reprint status (IN FILE, NOT IN FILE, ON REQUEST (MM/DD/YY))',
		'SP' => 'Start page number',
		'EP' => 'Ending page number',
		'JF' => 'Periodical full name',
		'JO' => 'Periodical standard abbreviation',
		'JA' => 'Periodical in which article was published',
		'J1' => 'Periodical name - User abbreviation 1',
		'J2' => 'Periodical name - User abbreviation 2',
		'VL' => 'Volume number',
		'IS' => 'Issue number',
		'T2' => 'Title secondary',
		'CY' => 'City of Publication',
		'PB' => 'Publisher',
		'U1' => 'User definable 1',
		'U2' => 'User definable 2',
		'U3' => 'User definable 3',
		'U4' => 'User definable 4',
		'U5' => 'User definable 5',
		'T3' => 'Title series',
		'N2' => 'Abstract',
		'SN' => 'ISSN/ISBN (e.g. ISSN XXXX-XXXX)',
		'AV' => 'Availability',
		'M1' => 'Misc. 1',
		'M2' => 'Misc. 2',
		'M3' => 'Misc. 3',
		'AD' => 'Address',
		'UR' => 'Web/URL',
		'L1' => 'Link to PDF',
		'L2' => 'Link to Full-text',
		'L3' => 'Related records',
		'L4' => 'Images',
		'ER' => 'End of Reference (must be the last tag)',
	);
	/**
	 * Map of all types (tag TY) defined for RIS.
	 *
	 * @var array
	 * @see http://en.wikipedia.org/wiki/RIS_%28file_format%29
	 * @see http://www.refman.com/support/risformat_intro.asp
	 */
	public static $typeMap = array(
		'ABST' => 'Abstract',
		'ADVS' => 'Audiovisual material',
		'ART' => 'Art Work',
		'BOOK' => 'Whole book',
		'CASE' => 'Case',
		'CHAP' => 'Book chapter',
		'COMP' => 'Computer program',
		'CONF' => 'Conference proceeding',
		'CTLG' => 'Catalog',
		'DATA' => 'Data file',
		'ELEC' => 'Electronic Citation',
		'GEN' => 'Generic',
		'HEAR' => 'Hearing',
		'ICOMM' => 'Internet Communication',
		'INPR' => 'In Press',
		'JFULL' => 'Journal (full)',
		'JOUR' => 'Journal',
		'MAP' => 'Map',
		'MGZN' => 'Magazine article',
		'MPCT' => 'Motion picture',
		'MUSIC' => 'Music score',
		'NEWS' => 'Newspaper',
		'PAMP' => 'Pamphlet',
		'PAT' => 'Patent',
		'PCOMM' => 'Personal communication',
		'RPRT' => 'Report',
		'SER' => 'Serial publication',
		'SLIDE' => 'Slide',
		'SOUND' => 'Sound recording',
		'STAT' => 'Statute',
		'THES' => 'Thesis/Dissertation',
		'UNPB' => 'Unpublished work',
		'VIDEO' => 'Video recording',
	);
}
