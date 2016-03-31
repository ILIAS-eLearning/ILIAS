<?php
require_once('./Modules/Bibliographic/classes/Types/class.ilBibliograficFileReaderBase.php');

/**
 * Class ilBibTex
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilBibTex extends ilBibliograficFileReaderBase implements ilBibliograficFileReader {

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

	public function parseContent() {
		$this->convertBibSpecialChars();
		$this->removeEmtpyLinesAndComments();

		// get entries
		$objects = preg_split("/\\@([\\w]*)/uix", $this->file_content, null, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

		// some files lead to a empty first entry in the array with the fist bib-entry, we have to trow them away...
		if (strlen($objects[0]) <= 3) {
			$objects = array_splice($objects, 1);
		}

		$entries = array();
		foreach ($objects as $key => $object) {
			if ((int)$key % 2 == 0 || (int)$key == 0) {
				$entry = array();
				$entry['entryType'] = strtolower($object);
			} else {
				// Citation
				preg_match("/^{(?<cite>.*),\\n/um", $object, $matches);
				if ($matches['cite']) {
					$entry['cite'] = $matches['cite'];
				}

				// attributes
				// Original: (?<attr>[\w]*)[ ]{0,}=[{ '\"]+(?<content>.*?)[} '\"]{0,}[,]{0,1}\n
				preg_match_all("/(?<attr>[\\w]*)[ ]{0,}=[{ '\\\"]+(?<content>.*?)[} '\\\"]{0,}[,]{0,1}\\n/um", $object, $attr_matches, PREG_SET_ORDER);

				foreach ($attr_matches as $match) {
					$entry[strtolower($match['attr'])] = $match['content'];
				}

				$entries[] = $entry;
			}
		}

		return $entries;
	}


	protected function convertBibSpecialChars() {
		$bibtex_special_chars['ä'] = '{\"a}';
		$bibtex_special_chars['ë'] = '{\"e}';
		$bibtex_special_chars['ï'] = '{\"i}';
		$bibtex_special_chars['ö'] = '{\"o}';
		$bibtex_special_chars['ü'] = '{\"u}';
		$bibtex_special_chars['Ä'] = '{\"A}';
		$bibtex_special_chars['Ë'] = '{\"E}';
		$bibtex_special_chars['Ï'] = '{\"I}';
		$bibtex_special_chars['Ö'] = '{\"O}';
		$bibtex_special_chars['Ü'] = '{\"U}';
		$bibtex_special_chars['â'] = '{\^a}';
		$bibtex_special_chars['ê'] = '{\^e}';
		$bibtex_special_chars['î'] = '{\^i}';
		$bibtex_special_chars['ô'] = '{\^o}';
		$bibtex_special_chars['û'] = '{\^u}';
		$bibtex_special_chars['Â'] = '{\^A}';
		$bibtex_special_chars['Ê'] = '{\^E}';
		$bibtex_special_chars['Î'] = '{\^I}';
		$bibtex_special_chars['Ô'] = '{\^O}';
		$bibtex_special_chars['Û'] = '{\^U}';
		$bibtex_special_chars['à'] = '{\`a}';
		$bibtex_special_chars['è'] = '{\`e}';
		$bibtex_special_chars['ì'] = '{\`i}';
		$bibtex_special_chars['ò'] = '{\`o}';
		$bibtex_special_chars['ù'] = '{\`u}';
		$bibtex_special_chars['À'] = '{\`A}';
		$bibtex_special_chars['È'] = '{\`E}';
		$bibtex_special_chars['Ì'] = '{\`I}';
		$bibtex_special_chars['Ò'] = '{\`O}';
		$bibtex_special_chars['Ù'] = '{\`U}';
		$bibtex_special_chars['á'] = '{\\\'a}';
		$bibtex_special_chars['é'] = '{\\\'e}';
		$bibtex_special_chars['í'] = '{\\\'i}';
		$bibtex_special_chars['ó'] = '{\\\'o}';
		$bibtex_special_chars['ú'] = '{\\\'u}';
		$bibtex_special_chars['Á'] = '{\\\'A}';
		$bibtex_special_chars['É'] = '{\\\'E}';
		$bibtex_special_chars['Í'] = '{\\\'I}';
		$bibtex_special_chars['Ó'] = '{\\\'O}';
		$bibtex_special_chars['Ú'] = '{\\\'U}';
		$bibtex_special_chars['à'] = '{\`a}';
		$bibtex_special_chars['è'] = '{\`e}';
		$bibtex_special_chars['ì'] = '{\`i}';
		$bibtex_special_chars['ò'] = '{\`o}';
		$bibtex_special_chars['ù'] = '{\`u}';
		$bibtex_special_chars['À'] = '{\`A}';
		$bibtex_special_chars['È'] = '{\`E}';
		$bibtex_special_chars['Ì'] = '{\`I}';
		$bibtex_special_chars['Ò'] = '{\`O}';
		$bibtex_special_chars['Ù'] = '{\`U}';
		$bibtex_special_chars['ç'] = '{\c c}';
		$bibtex_special_chars['ß'] = '{\ss}';
		$bibtex_special_chars['ñ'] = '{\~n}';
		$bibtex_special_chars['Ñ'] = '{\~N}';

		$this->file_content = str_replace($bibtex_special_chars, array_keys($bibtex_special_chars), $this->file_content);
	}


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


	protected function removeEmtpyLinesAndComments() {
		// remove emty newlines
		$this->file_content = preg_replace("/^\\n/um", "", $this->file_content);
		// remove comments
		$this->file_content = preg_replace("/^%.*\\n/um", "", $this->file_content);
	}
}

?>
