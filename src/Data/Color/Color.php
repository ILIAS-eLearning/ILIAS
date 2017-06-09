<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Data\Color;
use ILIAS\Validation as VAL;
use ILIAS\Data as D;
/**
 * Color expresses a certain color by giving the mixing ratio
 * in the RGB color space.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class Color {

	/**
	 * @var integer
	 */
	protected $r;

	/**
	 * @var integer
	 */
	protected $g;

	/**
	 * @var integer
	 */
	protected $b;


	public function __construct($r, $g, $b) {
		assert('is_integer($r)');
		assert('is_integer($g)');
		assert('is_integer($b)');
		$this->r = $r;
		$this->g = $g;
		$this->b = $b;
	}

	/**
	 * Get the valule for red.
	 *
	 * @return integer
	 */
	public function r() {
		return $this->r;
	}
	/**
	 * Get the valule for green.
	 *
	 * @return integer
	 */
	public function g() {
		return $this->g;
	}
	/**
	 * Get the valule for blue.
	 *
	 * @return integer
	 */
	public function b() {
		return $this->b;
	}

	/**
	 * Return array with RGB-values.
	 *
	 * @return int[]
	 */
	public function asArray() {
		return array(
			$this->r,
			$this->g,
			$this->b
		);
	}

	/**
	 * Return color-value in hex-format.
	 *
	 * @return string
	 */
	public function asHex() {
		$hex = '#';
		foreach ($this->asArray() as $value) {
			$hex .= str_pad(dechex($value), 2, '0', STR_PAD_LEFT);
		}
		return $hex;
	}

	/**
	 * Return string with RGB-notation
	 *
	 * @return string
	 */
	public function asRGBString() {
		return 'rgb('
			.implode($this->asArray(), ', ')
			.')';
	}

	/**
	* Based on https://de.wikipedia.org/wiki/Luminanz
	* this function decides if the color can be considered "dark".
	* With a dark background, i.e., a lighter (white) color should be used
	* for the foreground.
	*
	* @return boolean
	*/
	public function isDark() {
		$sum = 0.299 * $this->r + 0.587 * $this->g + 0.114 * $this->b;
		if($sum < 128){
			return true;
		}
		return false;
	}

}
