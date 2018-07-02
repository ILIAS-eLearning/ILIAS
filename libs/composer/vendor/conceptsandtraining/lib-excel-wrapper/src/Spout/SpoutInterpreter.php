<?php

namespace CaT\Libs\ExcelWrapper\Spout;

use \CaT\Libs\ExcelWrapper\Style;

use Box\Spout\Writer\Style\BorderBuilder;
use Box\Spout\Writer\Style\StyleBuilder;

class SpoutInterpreter {
	/**
	 * @var Style
	 */
	protected $style;

	/**
	 * Intepret a style object
	 *
	 * @return Box\Spout\Writer\Style\Style
	 */
	public function interpret(Style $style) {
		$this->style = $style;

		$spout_style = new StyleBuilder();
		$spout_border = new BorderBuilder();

		$this->setFontFamily($spout_style);
		$this->setFontSize($spout_style);
		$this->setBold($spout_style);
		$this->setItalic($spout_style);
		$this->setUnderline($spout_style);
		$this->setTextColor($spout_style);
		$this->setBackgroundColor($spout_style);
		$this->setOrientation($spout_style);

		$this->setVerticalLine($spout_border);

		$spout_style->setBorder($spout_border->build());

		return $spout_style->build();
	}

	/**
	 * Get the lib style object
	 *
	 * @throws LogicException 	if no style is set
	 *
	 * @return Style
	 */
	protected function getStyle() {
		if($this->style === null) {
			throw new LogicException(__METHOD__." no style found");
		}

		return $this->style;
	}

	protected function setFontFamily(StyleBuilder $spout_style) {
		$font_family = $this->style->getFontFamily();
		if($font_family && $font_family != "") {
			$spout_style->setFontName($font_family);
		}
	}

	protected function setFontSize(StyleBuilder $spout_style) {
		$font_size = $this->style->getFontSize();
		if($font_size) {
			$spout_style->setFontSize($font_size);
		}
	}

	protected function setBold(StyleBuilder $spout_style) {
		if($this->style->getBold()) {
			$spout_style->setFontBold();
		}
	}

	protected function setItalic(StyleBuilder $spout_style) {
		if($this->style->getItalic()) {
			$spout_style->setFontItalic();
		}
	}

	protected function setUnderline(StyleBuilder $spout_style) {
		if($this->style->getUnderline()) {
			$spout_style->setFontUnderline();
		}
	}

	protected function setTextColor(StyleBuilder $spout_style) {
		$font_family = $this->style->getFontFamily();
		if($font_family && $font_family != "") {
			$spout_style->setFontName($font_family);
		}
	}

	protected function setBackgroundColor(StyleBuilder $spout_style) {
		$font_size = $this->style->getFontSize();
		if($font_size) {
			$spout_style->setFontSize($font_size);
		}
	}

	protected function setOrientation(StyleBuilder $spout_style) {
		$font_family = $this->style->getFontFamily();
		if($font_family && $font_family != "") {
			$spout_style->setFontName($font_family);
		}
	}

	protected function setVerticalLine(BorderBuilder $spout_border) {
		if($this->style->getVerticalLine()) {
			$spout_style->setBorderRight();
		}
	}
}