<?php

/**
 * Interface ilQueryUtilsInterface
 */
interface ilQueryUtilsInterface {

	/**
	 * @param $field       string
	 * @param $values      string[]
	 * @param bool $negate boolean
	 * @param string $type string
	 * @return string
	 */
	public function in($field, $values, $negate = false, $type = "");


	/**
	 * @param $query mixed
	 * @param $type  string
	 * @return string
	 */
	public function quote($query, $type);
}