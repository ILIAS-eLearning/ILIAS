<?php

/**
 * Class ilBibTex
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilBibTex {

	/**
	 * @var array
	 */
	protected static $standard_fields = array(
		'address',
		'annote',
		'author',
		'booktitle',
		'chapter',
		'crossref',
		'edition',
		'editor',
		'eprint',
		'howpublished',
		'institution',
		'journal',
		'key',
		'month',
		'note',
		'number',
		'organization',
		'pages',
		'publisher',
		'school',
		'series',
		'title',
		'type',
		'url',
		'volume',
		'year',
	);
	/**
	 * @var array
	 */
	protected static $entry_types = array(
		'article',
		'book',
		'booklet',
		'conference',
		'inbook',
		'incollection',
		'inproceedings',
		'manual',
		'mastersthesis',
		'misc',
		'phdthesis',
		'proceedings',
		'techreport',
		'unpublished',
	);


	/**
	 * @param $field_name
	 *
	 * @return bool
	 */
	public static function isStandardField($field_name) {
		return in_array($field_name, self::$standard_fields);
	}


	/**
	 * @param $entry_ype
	 *
	 * @return bool
	 */
	public static function isEntryType($entry_ype) {
		return in_array($entry_ype, self::$entry_types);
	}
}

?>
