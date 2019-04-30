<?php

namespace ILIAS\Data\Scalar;

class StringHandler implements Scalar {

	/**
	 * @var string
	 */
	private $string;


	/**
	 * StringHandler constructor.
	 *
	 * @param string $string
	 */
	public function __construct(string $string) {
		$this->string = $string;
	}


	public function isString() {
		return true;
	}


	/**
	 * @param string $compare
	 *
	 * @return int|\lt
	 */
	public function caseCompare($compare) {
		return strcasecmp($this->string, $compare);
	}


	public function length() {
		return strlen($this->string);
	}


	/**
	 * @param      $offset
	 * @param null $length
	 *
	 * @return bool|string
	 */
	public function slice($offset, $length = null) {
		$offset = $this->prepareOffset($offset);
		$length = $this->prepareLength($offset, $length);
		if (0 === $length) {
			return '';
		}

		return substr($this->string, $offset, $length);
	}


	/**
	 * @param      $replacement
	 * @param      $offset
	 * @param null $length
	 *
	 * @return mixed
	 */
	public function replaceSlice($replacement, $offset, $length = null) {
		$offset = $this->prepareOffset($offset);
		$length = $this->prepareLength($offset, $length);

		return substr_replace($this->string, $replacement, $offset, $length);
	}


	/**
	 * @param     $string
	 * @param int $offset
	 *
	 * @return bool|int
	 */
	public function indexOf($string, $offset = 0) {
		$offset = $this->prepareOffset($offset);
		if ('' === $string) {
			return $offset;
		}

		return strpos($this->string, $string, $offset);
	}


	/**
	 * @param      $string
	 * @param null $offset
	 *
	 * @return bool|int|null
	 */
	public function lastIndexOf($string, $offset = null) {
		if (null === $offset) {
			$offset = $this->length();
		} else {
			$offset = $this->prepareOffset($offset);
		}
		if ('' === $string) {
			return $offset;
		}

		/* Converts $offset to a negative offset as strrpos has a different
		 * behavior for positive offsets. */

		return strrpos($this->string, $string, $offset - $this->length());
	}


	public function capitalize() {
		return ucwords($this->string);
	}


	public function contains($string) {
		return false !== $this->indexOf($string);
	}


	public function lower() {
		return strtolower($this->string);
	}


	public function count($string, $offset = 0, $length = null) {
		$offset = $this->prepareOffset($offset);
		$length = $this->prepareLength($offset, $length);
		if ('' === $string) {
			return $length + 1;
		}

		return substr_count($this->string, $string, $offset, $length);
	}


	public function split($separator, $limit = PHP_INT_MAX) {
		return explode($separator, $this->string, $limit);
	}


	public function reverse() {
		return strrev($this->string);
	}


	public function trim($characters = " \t\n\r\v\0") {
		return trim($this->string, $characters);
	}


	public function trimLeft($characters = " \t\n\r\v\0") {
		return ltrim($this->string, $characters);
	}


	public function trimRight($characters = " \t\n\r\v\0") {
		return rtrim($this->string, $characters);
	}


	public function padLeft($length, $padString = " ") {
		return str_pad($this->string, $length, $padString, STR_PAD_LEFT);
	}


	public function padRight($length, $padString = " ") {
		return str_pad($this->string, $length, $padString, STR_PAD_RIGHT);
	}


	public function toArray() {
		return [ $this->string ];
	}


	public function toJSON() {
		return json_encode($this->string);
	}


	public function upper() {
		return strtoupper($this->string);
	}


	// Internal methods, not part of public API
	protected function prepareOffset($offset) {
		$len = $this->length();
		if ($offset < - $len || $offset > $len) {
			throw new \InvalidArgumentException('Offset must be in range [-len, len]');
		}
		if ($offset < 0) {
			$offset += $len;
		}

		return $offset;
	}


	protected function prepareLength($offset, $length) {
		if (null === $length) {
			return $this->length() - $offset;
		}
		if ($length < 0) {
			$length += $this->length() - $offset;
			if ($length < 0) {
				throw new \InvalidArgumentException('Length too small');
			}
		} else {
			if ($offset + $length > $this->length()) {
				throw new \InvalidArgumentException('Length too large');
			}
		}

		return $length;
	}
}