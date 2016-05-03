<?php

interface ExcelWriter {

	/**
	 * sets row formats for upcoming rows: bold
	 * @return ExcelWriter $this to ensure fluent interface
	 */
	public function setRowFormatBold();

	/**
	 * sets row formats for upcoming rows: wrap
	 * @return ExcelWriter $this to ensure fluent interface
	 */
	public function setRowFormatWrap();

	/**
	 * adds sheets with name and sets them current 
	 * @var string $name
	 * @throws \InvalidArgumentException
	 * @return ExcelWriter $this to ensure fluent interface
	 */
	public function addSheet($name);

	/**
	 * turn to shee having name
	 * @var string $name
	 * @throws \InvalidArgumentException
	 * @return ExcelWriter $this to ensure fluent interface
	 */
	public function setSheet($name);

	/**
	 * write to current sheet 
	 * @var array $row_data
	 * @return ExcelWriter $this to ensure fluent interface
	 */
	public function writeRow(array $row_data);

	/**
	 * deliver file
	 * @throws InvalidArgumentException
	 * @var string $filename
	 */
	public function offerDownload($filename);
}