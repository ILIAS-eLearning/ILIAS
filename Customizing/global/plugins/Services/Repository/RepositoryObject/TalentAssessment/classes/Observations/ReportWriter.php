<?php
namespace CaT\Plugins\TalentAssessment\Observations;

interface ReportWriter {
	

	/**
	 * A short string, no line break. Absolute positioned set at x,y
	 */
	public function StringPositionedAbsolute($x, $y, $text, $text_allign,
		$w, $h, $font, $size, $text_options,
		 array $font_color);

	/**
	 * A short string, no line break. Realtively positioned at y
	 * after previous element.
	 */
	public function StringPositionedRelativeY($y, $text, $text_allign,
		$w, $h, $font, $size, $text_options,
		 array $font_color);

	/**
	 * A text with possible line breaks. Absolute positioned set at x,y
	 */
	public function TextPositionedAbsolute($x, $y, $text, $text_allign,
		$w, $h, $font, $size, $text_options,
		 array $font_color);

	/**
	 * A text with possible line breaks. Realtively positioned at y
	 * after previous element.
	 */
	public function TextPositionedRelativeY($y, $text, $text_allign,
		$w, $h, $font, $size, $text_options,
		 array $font_color);

	public function BackgroundImage($image_file_location);

	public function Image($image_file_location, $x, $y, $w, $h);

	public function TextPositionedNextline($text, $text_allign,
		$w, $h, $font, $size, $text_options,
		array $font_color);

	public function StringPositionedNextline($text, $text_allign,
		$w, $h, $font, $size, $text_options,
		array $font_color);

	public function ImagePositionAbsolute($x,$y,$w,$h,$image_file_location);
	
	public function ImagePositionRelativeY($y,$w,$h,$image_file_location);
}