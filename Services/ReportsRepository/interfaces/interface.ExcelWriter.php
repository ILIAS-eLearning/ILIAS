<?php

interface ExcelWriter {

	/**
	 * sets row formats for upcoming rows: bold
	 */
	public function setRowFormatBold();

	/**
	 * sets row formats for upcoming rows: wrap
	 */
	public function setRowFormatWrap();

	/**
	 * adds sheets with name and sets them current 
	 * @var string $name
	 * @throws \InvalidArgumentException
	 */
	public function addSheet($name);

	/**
	 * turn to shee having name
	 * @var string $name
	 * @throws \InvalidArgumentException
	 */
	public function setSheet($name);

	/**
	 * write to current sheet 
	 * @var array $row_data
	 */
	public function writeRow(array $row_data);

	/**
	 * deliver file
	 * @var string $filename
	 */
	public function offerDownload($filename);
}