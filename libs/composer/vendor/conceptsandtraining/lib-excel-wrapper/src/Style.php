<?php

namespace CaT\Libs\ExcelWrapper;

/**
 * Immutable class for styling a column.
 * This describes functions to style a column in a sheet.
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class Style {
	const ORIENTATION_LEFT = "left";
	const ORIENTATION_RIGHT = "right";
	const ORIENTATION_CENTER = "center";
	const ORIENTATION_BLOCK = "block";

	const COLOR_REG_EXP = "/^[A-Fa-f0-9]{6}$/i";

	/**
	 * @var string
	 */
	protected $font_family;

	/**
	 * @var int
	 */
	protected $font_size;

	/**
	 * @var bool
	 */
	protected $bold;

	/**
	 * @var bool
	 */
	protected $italic;

	/**
	 * @var bool
	 */
	protected $underlined;

	/**
	 * @var string
	 */
	protected $text_color;

	/**
	 * @var string
	 */
	protected $background_color;

	/**
	 * @var bool
	 */
	protected $horizontal_line;

	/**
	 * @var string
	 */
	protected $orientation;

	public function __construct($font_family = "Arial",
								$font_size = 10,
								$bold = false,
								$italic = false,
								$underlined = false,
								$text_color = "000000",
								$background_color = "ffffff",
								$horizontal_line = false,
								$orientation = self::ORIENTATION_LEFT
	) {
		assert('is_string($font_family)');
		assert('is_int($font_size)');
		assert('is_bool($bold)');
		assert('is_bool($italic)');
		assert('is_bool($underlined)');
		assert('is_string($text_color) && $this->validateColor($text_color)');
		assert('is_string($background_color) && $this->validateColor($background_color)');
		assert('is_bool($horizontal_line)');
		assert('is_string($orientation) && $this->validateOrientation($orientation)');

		$this->font_family = $font_family;
		$this->font_size = $font_size;
		$this->bold = $bold;
		$this->italic = $italic;
		$this->underlined = $underlined;
		$this->text_color = $text_color;
		$this->background_color = $background_color;
		$this->horizontal_line = $horizontal_line;
		$this->orientation = $orientation;
	}

	/**
	 * Get the font family
	 *
	 * @return string
	 */
	public function getFontFamily() {
		return $this->font_family;
	}

	/**
	 * Get the font size
	 *
	 * @return int
	 */
	public function getFontSize() {
		return $this->font_size;
	}

	/**
	 * Get the text bold
	 *
	 * @return bool
	 */
	public function getBold() {
		return $this->bold;
	}

	/**
	 * Get the text italic
	 *
	 * @return bool
	 */
	public function getItalic() {
		return $this->italic;
	}

	/**
	 * Set the text underlined
	 *
	 * @return bool
	 */
	public function getUnderline() {
		return $this->underline;
	}

	/**
	 * Get the color of text
	 *
	 * @return string
	 */
	public function getTextColor() {
		return $this->text_color;
	}

	/**
	 * Get the color of background
	 *
	 * @return string
	 */
	public function getBackgroundColor() {
		return $this->background_color;
	}

	/**
	 * Get vertical line on each column cell
	 *
	 * @return bool
	 */
	public function getVerticalLine() {
		return $this->vertical_line;
	}

	/**
	 * Get the text orientation
	 *
	 * @return string
	 */
	public function getOrientation() {
		return $this->orientation;
	}

	/**
	 * Set the font family
	 *
	 * @param string 	$font_family
	 *
	 * @return Style
	 */
	public function withFontFamily($font_family) {
		assert('is_string($font_family)');
		$clone = clone $this;
		$clone->font_family = $font_family;
		return $clone;
	}

	/**
	 * Set the font size
	 *
	 * @param int 		$font_size
	 *
	 * @return Style
	 */
	public function withFontSize($font_size) {
		assert('is_int($font_size)');
		$clone = clone $this;
		$clone->font_size = $font_size;
		return $clone;
	}

	/**
	 * Set the text bold
	 *
	 * @param bool 		$bold
	 *
	 * @return Style
	 */
	public function withBold($bold) {
		assert('is_bool($bold)');
		$clone = clone $this;
		$clone->bold = $bold;
		return $clone;
	}

	/**
	 * Set the text italic
	 *
	 * @param bool 		$italic
	 *
	 * @return Style
	 */
	public function withItalic($italic) {
		assert('is_bool($italic)');
		$clone = clone $this;
		$clone->italic = $italic;
		return $clone;
	}

	/**
	 * Set the text underlined
	 *
	 * @param bool 		$underline
	 *
	 * @return Style
	 */
	public function withUnderline($underline) {
		assert('is_bool($underline)');
		$clone = clone $this;
		$clone->underline = $underline;
		return $clone;
	}

	/**
	 * Set the color of text
	 *
	 * @param string 	$text_color 	RGB Code
	 *
	 * @return Style
	 */
	public function withTextColor($text_color) {
		assert('is_string($text_color) && $this->validateColor($text_color)');
		$clone = clone $this;
		$clone->text_color = $text_color;
		return $clone;
	}

	/**
	 * Set the color of background
	 *
	 * @param string 	$background_color 	RGB code
	 *
	 * @return Style
	 */
	public function withBackgroundColor($background_color) {
		assert('is_string($background_color) && $this->validateColor($background_color)');
		$clone = clone $this;
		$clone->background_color = $background_color;
		return $clone;
	}

	/**
	 * Set vertical line on the right side of each column cell
	 *
	 * @param bool 		$horizontal_line
	 *
	 * @return Style
	 */
	public function withVerticalLine($vertical_line) {
		assert('is_bool($vertical_line)');
		$clone = clone $this;
		$clone->vertical_line = $vertical_line;
		return $clone;
	}

	/**
	 * Set the text orientation
	 *
	 * @param string 	$orientation (@see class contanst)
	 *
	 * @return Style
	 */
	public function withOrientation($orientation) {
		assert('is_string($orientation) && $this->validateOrientation($orientation)');
		$clone = clone $this;
		$clone->orientation = $orientation;
		return $clone;
	}

	/**
	 * Check rgb color is valid
	 *
	 * @param string 	$color_code
	 *
	 * @return bool
	 */
	protected function validateColor($color_code) {
		return (bool)preg_match(self::COLOR_REG_EXP, $color_code);
	}

	/**
	 * Check the orientation is valid
	 *
	 * @param string 	$orientation
	 *
	 * @return bool
	 */
	protected function validateOrientation($orientation) {
		return in_array($orientation, array(self::ORIENTATION_LEFT, self::ORIENTATION_RIGHT, self::ORIENTATION_CENTER, self::ORIENTATION_BLOCK));
	}
}