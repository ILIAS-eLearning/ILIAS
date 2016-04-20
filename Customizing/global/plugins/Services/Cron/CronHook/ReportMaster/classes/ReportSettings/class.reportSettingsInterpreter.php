<?php


class reportSettingsInterpreter {
	public static $valid_formats =
		array( 'integer', 'text', 'float', 'date', 'time');

	abstract protected function formatValue($value, $format, $id, $name = '');
}