<?php

namespace CaT\Libs\ExcelWrapper;

/**
 * Interface for a excel stream writer.
 * Defines recommended and expected functions.
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
interface Writer {
	/**
	 * Set name of the created xlsx file
	 *
	 * @param string 	$file_name
	 *
	 * @return null
	 */
	public function setFileName($file_name);

	/**
	 * Set path to save the file
	 *
	 * @param string 	$file_path
	 *
	 * @return null
	 */
	public function setPath($file_path);

	/**
	 * Creates a new sheet in the workbook
	 *
	 * @param string 	$sheet_name
	 *
	 * @return null
	 */
	public function createSheet($sheet_name);

	/**
	 * Switch the current sheet of workbook
	 *
	 * @param string 	$sheet_name
	 *
	 * @return null
	 */
	public function selectSheet($sheet_name);

	/**
	 * Set the style for a single column
	 *
	 * @param string 	$column
	 * @param object 	$style
	 *
	 * @return null
	 */
	public function setColumnStyle($column, $style);

	/**
	 * Add a new row to the current sheet.
	 *
	 * @param mixed[]	$values
	 *
	 * @return null
	 */
	public function addRow(array $values);

	/**
	 * Add a new empty row with border top
	 *
	 * @return null
	 */
	public function addSeperatorRow();

	/**
	 * Add new empty row
	 *
	 * @return null
	 */
	public function addEmptyRow();

	/**
	 * Save the created file
	 *
	 * @return null
	 */
	public function saveFile();

	/**
	 * Close the stream writer
	 *
	 * @return null
	 */
	public function close();
}