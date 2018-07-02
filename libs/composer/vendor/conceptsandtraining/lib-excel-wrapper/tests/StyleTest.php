<?php

require_once(__DIR__."/../src/Style.php");
use \CaT\Libs\ExcelWrapper\Style;
use PHPUnit\Framework\TestCase;

class StyleTest  extends TestCase {
	public function setUp() {
		$this->style = new Style();
	}

	public function test_withFontFamily() {
		$style = $this->style->withFontFamily("Font");
		$this->assertEquals("Font", $style->getFontFamily());

		return array($style);
	}

	/**
	 * @depends test_withFontFamily
	 */
	public function test_withFontSize($style) {
		$style = $style[0];
		$style = $style->withFontSize(20);
		$this->assertEquals("Font", $style->getFontFamily());
		$this->assertEquals(20, $style->getFontSize());

		return array($style);
	}

	/**
	 * @depends test_withFontSize
	 */
	public function test_withBold($style) {
		$style = $style[0];
		$style = $style->withBold(false);
		$this->assertEquals("Font", $style->getFontFamily());
		$this->assertEquals(20, $style->getFontSize());
		$this->assertFalse($style->getBold());

		return array($style);
	}

	/**
	 * @depends test_withBold
	 */
	public function test_withItalic($style) {
		$style = $style[0];
		$style = $style->withItalic(true);
		$this->assertEquals("Font", $style->getFontFamily());
		$this->assertEquals(20, $style->getFontSize());
		$this->assertFalse($style->getBold());
		$this->assertTrue($style->getItalic());

		return array($style);
	}

	/**
	 * @depends test_withItalic
	 */
	public function test_withUnderline($style) {
		$style = $style[0];
		$style = $style->withUnderline(true);
		$this->assertEquals("Font", $style->getFontFamily());
		$this->assertEquals(20, $style->getFontSize());
		$this->assertFalse($style->getBold());
		$this->assertTrue($style->getItalic());
		$this->assertTrue($style->getUnderline());

		return array($style);
	}

	/**
	 * @depends test_withUnderline
	 */
	public function test_withTextColor($style) {
		$style = $style[0];
		$style = $style->withTextColor("11EE22");
		$this->assertEquals("Font", $style->getFontFamily());
		$this->assertEquals(20, $style->getFontSize());
		$this->assertFalse($style->getBold());
		$this->assertTrue($style->getItalic());
		$this->assertTrue($style->getUnderline());
		$this->assertEquals("11EE22", $style->getTextColor());

		return array($style);
	}

	/**
	 * @depends test_withTextColor
	 */
	public function test_withBackgroundColor($style) {
		$style = $style[0];
		$style = $style->withBackgroundColor("EEAACC");
		$this->assertEquals("Font", $style->getFontFamily());
		$this->assertEquals(20, $style->getFontSize());
		$this->assertFalse($style->getBold());
		$this->assertTrue($style->getItalic());
		$this->assertTrue($style->getUnderline());
		$this->assertEquals("11EE22", $style->getTextColor());
		$this->assertEquals("EEAACC", $style->getBackgroundColor());

		return array($style);
	}

	/**
	 * @depends test_withBackgroundColor
	 */
	public function test_withOrientation($style) {
		$style = $style[0];
		$style = $style->withOrientation(Style::ORIENTATION_LEFT);
		$this->assertEquals("Font", $style->getFontFamily());
		$this->assertEquals(20, $style->getFontSize());
		$this->assertFalse($style->getBold());
		$this->assertTrue($style->getItalic());
		$this->assertTrue($style->getUnderline());
		$this->assertEquals("11EE22", $style->getTextColor());
		$this->assertEquals("EEAACC", $style->getBackgroundColor());
		$this->assertEquals(Style::ORIENTATION_LEFT, $style->getOrientation());
	}

	public function test_NoValidColor() {
		try {
			$this->style = $this->style->withBackgroundColor("11");
			$this->assertFalse("Should have raised.");
		}
		catch (Exception $e) {}
	}

	public function test_noValidOrientation() {
		try {
			$this->style = $this->style->withOrientation("untenlinks");
			$this->assertFalse("Should have raised.");
		}
		catch (Exception $e) {}
	}
}