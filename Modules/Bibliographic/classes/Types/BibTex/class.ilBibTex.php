<?php

/**
 * Class ilBibTexInterface
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilBibTex implements ilBiblTypeInterface {

	/**
	 * @inheritDoc
	 */
	public function getId() {
		return ilBiblTypeFactoryInterface::DATA_TYPE_BIBTEX;
	}


	/**
	 * @inheritDoc
	 */
	public function getStringRepresentation() {
		return "bib";
	}



	/**
	 * should return
	 *
	 * Array
	 * (
	 *      [0] => Array
	 *      (
	 *          [isbn] => 978-0-12-411454-8
	 *          [year] => 2013
	 *          [title] => Mastering cloud computing
	 *          [cite] => Masteringcloudcomputing:2013
	 *          [entryType] => book
	 *      )
	 *
	 *      [...]
	 *
	 * @return array
	 */

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
	 * @inheritdoc
	 */
	public function isStandardField($field_name) {
		return in_array($field_name, self::$standard_fields);
	}


	/**
	 * @inheritdoc
	 */
	public function isEntryType($entry_ype) {
		return in_array($entry_ype, self::$entry_types);
	}
}