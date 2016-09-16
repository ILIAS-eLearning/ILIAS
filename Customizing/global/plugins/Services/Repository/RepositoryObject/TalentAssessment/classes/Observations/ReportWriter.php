<?php
namespace CaT\Plugins\TalentAssessment\Observations;

interface ReportWriter {
	

	/**
	 * A short string, no line break. Absolute positioned set at x,y
	 */
	public function stringPositionedAbsolute($x, $y, $text, $text_allign,
		$w, $h, $font, $size, $text_options,
		 array $font_color);

	/**
	 * A short string, no line break. Realtively positioned at y
	 * after previous element.
	 */
	public function stringPositionedRelativeY($y, $text, $text_allign,
		$w, $h, $font, $size, $text_options,
		 array $font_color);

	/**
	 * A text with possible line breaks. Absolute positioned set at x,y
	 */
	public function textPositionedAbsolute($x, $y, $text, $text_allign,
		$w, $h, $font, $size, $text_options,
		 array $font_color);

	/**
	 * A text with possible line breaks. Realtively positioned at y
	 * after previous element.
	 */
	public function textPositionedRelativeY($y, $text, $text_allign,
		$w, $h, $font, $size, $text_options,
		 array $font_color);

	public function backgroundImage($image_file_location);

	public function textPositionedNextline($text, $text_allign,
		$w, $h, $font, $size, $text_options,
		array $font_color);

	public function stringPositionedNextline($text, $text_allign,
		$w, $h, $font, $size, $text_options,
		array $font_color);

	public function imagePositionAbsolute($x,$y,$w,$h,$image_file_location);
	
	public function imagePositionRelativeY($y,$w,$h,$image_file_location);
}