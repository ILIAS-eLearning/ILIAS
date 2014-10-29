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
	 */
	protected static $standard_fields = array(
		'TY',
		'ID',
		'T1',
		'TI',
		'CT',
		'A1',
		'A2',
		'AU',
		'Y1',
		'PY',
		'N1',
		'KW',
		'RP',
		'SP',
		'EP',
		'JF',
		'JO',
		'JA',
		'J1',
		'J2',
		'VL',
		'IS',
		'T2',
		'CY',
		'PB',
		'U1',
		'U5',
		'T3',
		'N2',
		'SN',
		'AV',
		'M1',
		'M3',
		'AD',
		'UR',
		'L1',
		'L2',
		'L3',
		'L4',
		'ER',
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
