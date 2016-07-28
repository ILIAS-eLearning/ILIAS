<?php

interface AbstractDerivedField {

	/**
	 * Get all fields from which this field is derived.
	 *
	 * @return	AbstractTableField[]
	 */
	public function derivedFrom();

	/**
	 * Get the postprocessing-function to be used by interpreter.
	 *
	 * @return	closure 
	 */
	public function postprocess();

	/**
	 * Get the name associated with the field to be used by interpreter.
	 *
	 * @return	string
	 */
	public function name();
}