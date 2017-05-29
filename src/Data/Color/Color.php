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
	 * @var string
	 */
	protected $value;

	public function __construct($value) {
		$this->value = $this->normalize($value);
	}

	/**
	 * Return the hex-value of color
	 *
	 * @return string
	 */
	public function value() {
		return $this->value;
	}

	/**
	 * Return string with rgb-notation
	 *
	 * @return string
	 */
	public function rgbstring() {
		return 'rgb('
			.implode($this->rgb(), ', ')
			.')';
	}

	/**
	 * Return a list with rgb-values of color
	 *
	 * @return <int>
	 */
	public function rgb() {
		$col = $this->trimhash($this->value);
		$chunks = str_split($col, 2);
		return array_map('hexdec', $chunks);
	}


	/**
	 * A value is valid, if it
	 * -starts with '#'
	 * -is of length 3(4) or 6(7)
	 * OR
	 * -is a list of exactly three values
	 * -each value is between 0 and 255
	 *
	 * The datatype stores the value in hex longhand notation.
	 * Normalization also includes validation.
	 *
	 * @throws Exception
	 * @return string
	 */
	private function normalize($value) {

		switch(gettype($value)) {

			case 'array':
				if(count($value) !== 3) {
					throw new \UnexpectedValueException("RGB must contain three values", 1);
				}
				// build constraints
				$f = new VAL\Factory(new D\Factory());
				$constraints = $f->parallel([
						$f->greaterThan(-1),
						$f->lessThan(256)
					],
					new D\Factory()
				);

				foreach ($value as $key => $val) {
					if(! is_integer($val)) {
						throw new \UnexpectedValueException("RGB must contain integer values only", 1);
					}
					$constraints->check($val);
				}

				$value = $this->toHex($value[0], $value[1],	$value[2]);
				break;

			case 'string':
				if(substr($value, 0, 1) !== '#') {
					throw new \UnexpectedValueException("color must start with #", 1);
				}
				if(strlen($value) !== 4 && strlen($value) !== 7) {
					throw new \UnexpectedValueException("length must be 3 or 6", 1);
				}

				if(strlen($value) === 4) {
					$value = $this->unshort($value);
				}

				if(! preg_match('/^#[a-f0-9]{6}$/i', $value)) {
					throw new \UnexpectedValueException("color contains illegal characters", 1);
				}

				break;

			default:
				throw new Exception("invalid data type", 1);

		}
		return $value;
	}




	/**
	 * trims away the leading #
	 *
	 * @param string $hex
	 * @return string
	 */
	private function trimhash($hex) {
		if(substr($hex, 0, 1) === '#') {
			$hex = ltrim($hex, '#');
		}
		return $hex;
	}


	/**
	 * fill up shorthand notation
	 *
	 * @param string $hex
	 * @return string
	 */
	private function unshort($hex) {
		$h = $this->trimhash($hex);
		$hex = '#'.$h[0].$h[0].$h[1].$h[1].$h[2].$h[2];
		return $hex;
	}

	/**
	 * convert the rgb-values to hex
	 *
	 * @return string
	 */
	private function toHex($r, $g, $b) {
		return '#'
			.str_pad(dechex($r), 2, '0', STR_PAD_LEFT)
			.str_pad(dechex($g), 2, '0', STR_PAD_LEFT)
			.str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
	}


}

