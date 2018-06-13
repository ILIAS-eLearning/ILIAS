<?php

namespace CaT\Libs\ExcelWrapper\Spout;

use \CaT\Plugins\MateriaList\ilActions;
use \CaT\Libs\ExcelWrapper\Writer;

use Box\Spout\Common\Type;
use Box\Spout\Writer\WriterFactory;

use Box\Spout\Writer\Style\Style;
use Box\Spout\Writer\Style\BorderBuilder;
use Box\Spout\Writer\Style\StyleBuilder;

/**
 * Export a single material list
 */
class SpoutWriter implements Writer {
	public function __construct() {
		$this->writer = WriterFactory::create(Type::XLSX);
	}

	/**
	 * Open file for spout
	 *
	 * @throws \LogicException if path or file name is not set.
	 *
	 * @return null
	 */
	public function openFile() {
		if($this->file_path === null || $this->file_name === null) {
			throw new \LogicException(__METHOD__." path or filename is not set.");
		}

		$this->writer->openToFile($this->getFilePath());
	}

	/**
	 * Set the number of columns the sheet will be filled in
	 *
	 * @param int 	$max_column_count
	 */
	public function setMaximumColumnCount($max_column_count) {
		assert('is_int($max_column_count)');
		$this->max_column_count = $max_column_count;
	}

	/**
	 * Get a values array according to max column count
	 *
	 * @param bool 		$with_spaces
	 *
	 * @return string[]
	 */
	public function getEmptyValueArray($with_spaces = false) {
		$ret = array();
		for ($i=0; $i < $this->max_column_count; $i++) {
			if($with_spaces) {
				$ret[] = " ";
			} else {
				$ret[] = "";
			}
		}

		return $ret;
	}
	/**
	 * @inheritdoc
	 */
	public function setFileName($file_name) {
		$this->file_name = $file_name;
	}

	/**
	 * @inheritdoc
	 */
	public function setPath($file_path) {
		$this->file_path = $file_path;
	}

	/**
	 * @inheritdoc
	 */
	public function createSheet($sheet_name) {
		$new_sheet = $this->writer->addNewSheetAndMakeItCurrent();
		$new_sheet->setName($sheet_name);
	}

	/**
	 * @inheritdoc
	 */
	public function selectSheet($sheet_name) {
		return;
	}

	/**
	 * @inheritdoc
	 */
	public function setColumnStyle($column, $style) {
		//assert('$style instanceof Style::class');
		$this->style = $style;
	}

	/**
	 * @inheritdoc
	 */
	public function addRow(array $values) {
		$this->writer->addRowWithStyle($values, $this->style);
	}

	/**
	 * @inheritdoc
	 */
	public function addSeperatorRow() {
		$spout_border = (new BorderBuilder())
						->setBorderTop();
		$border = $spout_border->build();

		$spout_style = (new StyleBuilder())
						->setBorder($border);

		$style = $spout_style->build();

		$this->writer->addRowWithStyle($this->getEmptyValueArray(true), $style);
	}

	/**
	 * Add new empty row
	 *
	 * @return null
	 */
	public function addEmptyRow() {
		$this->writer->addRow(array(""));
	}

	/**
	 * @inheritdoc
	 */
	public function saveFile() {
		return;
	}

	/**
	 * @inheritdoc
	 */
	public function close() {
		$this->writer->close();
	}

	/**
	 * Get the full path to file
	 *
	 * @return string
	 */
	protected function getFilePath() {
		return $this->file_path.$this->file_name;
	}
}