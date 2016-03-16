<?php

interface ExcelWriter {
	public function setRowFormat(array $row_format);
	public function addSheet($name);
	public function writeRow(array $row_data, $sheet);
	public function deliverFile($filename);
}