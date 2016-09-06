<?php
namespace CaT\Plugins\TalentAssessment\Observations;

interface ReportWriter {
	

	/**
	 * A short string, no line break. Absolute positioned set at x,y
	 */
	public function StringPositionedAbsolute($x, $y, $text, $text_allign,
		$dx, $dy, $font, $size, $text_options,
		 array $fill);

	/**
	 * A short string, no line break. Realtively positioned at y
	 * after previous element.
	 */
	public function StringPositionedRelativeY($y, $text, $text_allign,
		$dx, $dy, $font, $size, $text_options,
		 array $fill);

	/**
	 * A text with possible line breaks. Absolute positioned set at x,y
	 */
	public function TextPositionedAbsolute($x, $y, $text, $text_allign,
		$dx, $dy, $font, $size, $text_options,
		 array $fill);

	/**
	 * A text with possible line breaks. Realtively positioned at y
	 * after previous element.
	 */
	public function TextPositionedRelativeY($y, $text, $text_allign,
		$dx, $dy, $font, $size, $text_options,
		 array $fill);

	public function BackgroundImage($image_file_location);

	public function Image($image_file_location,$x,$y,$w,$h);
}