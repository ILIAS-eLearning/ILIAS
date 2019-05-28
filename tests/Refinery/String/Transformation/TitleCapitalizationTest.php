<?php

use ILIAS\Data;
use ILIAS\Refinery;
use ILIAS\Refinery\String\TitleCapitalization;
use PHPUnit\Framework\TestCase;

/**
 * Class TitleCapitalizationTest
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class TitleCapitalizationTest extends TestCase {

	const TEST_STRING = "I am a test string for the title capitalization and I hope that works even if it is complicated :)";
	const EXPECTED_RESULT_TEST_STRING = "I Am a Test String for the Title Capitalization and I Hope that Works even if It Is Complicated :)";
	/**
	 * @var TitleCapitalization
	 */
	private $title_capitalization;
	/**
	 * @var Refinery\Factory
	 */
	protected $f;


	protected function setUp(): void {
		$dataFactory = new Data\Factory();

		$language = $this->createMock('\\' . ilLanguage::class);

		$this->f = new Refinery\Factory($dataFactory, $language);
		$this->title_capitalization = $this->f->string()->titleCapitalization();
	}


	protected function tearDown(): void {
		$this->f = null;
		$this->title_capitalization = null;
	}


	public function testTransform(): void {
		$str = $this->title_capitalization->transform(self::TEST_STRING);

		$this->assertEquals(self::EXPECTED_RESULT_TEST_STRING, $str);
	}


	public function testTransformFails(): void {
		$raised = false;
		try {
			$arr = [];
			$next_str = $this->title_capitalization->transform($arr);
		} catch (InvalidArgumentException $e) {
			$raised = true;
		}
		$this->assertTrue($raised);

		$raised = false;
		try {
			$int = 1001;
			$next_str = $this->title_capitalization->transform($int);
		} catch (InvalidArgumentException $e) {
			$raised = true;
		}
		$this->assertTrue($raised);

		$raised = false;
		try {
			$std_class = new stdClass();
			$next_str = $this->title_capitalization->transform($std_class);
		} catch (InvalidArgumentException $e) {
			$raised = true;
		}
		$this->assertTrue($raised);
	}


	public function testInvoke(): void {
		$title_capitalization = $this->f->string()->titleCapitalization();

		$str = $title_capitalization(self::TEST_STRING);

		$this->assertEquals(self::EXPECTED_RESULT_TEST_STRING, $str);
	}


	public function testInvokeFails(): void {
		$title_capitalization = $this->f->string()->titleCapitalization();

		$raised = false;
		try {
			$arr = [];
			$next_str = $title_capitalization($arr);
		} catch (InvalidArgumentException $e) {
			$raised = true;
		}
		$this->assertTrue($raised);

		$raised = false;
		try {
			$int = 1001;
			$next_str = $title_capitalization($int);
		} catch (InvalidArgumentException $e) {
			$raised = true;
		}
		$this->assertTrue($raised);

		$raised = false;
		try {
			$std_class = new stdClass();
			$next_str = $title_capitalization($std_class);
		} catch (InvalidArgumentException $e) {
			$raised = true;
		}
		$this->assertTrue($raised);
	}


	public function testApplyToWithValidValueReturnsAnOkResult(): void {
		$factory = new Data\Factory();

		$valueObject = $factory->ok(self::TEST_STRING);

		$resultObject = $this->title_capitalization->applyTo($valueObject);

		$this->assertEquals(self::EXPECTED_RESULT_TEST_STRING, $resultObject->value());
		$this->assertFalse($resultObject->isError());
	}


	public function testApplyToWithInvalidValueWillLeadToErrorResult(): void {
		$factory = new Data\Factory();

		$valueObject = $factory->ok(42);

		$resultObject = $this->title_capitalization->applyTo($valueObject);

		$this->assertTrue($resultObject->isError());
	}
}
