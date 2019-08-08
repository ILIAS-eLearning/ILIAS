<?php

/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Refinery;
use PHPUnit\Framework\TestCase;

/**
 * TestCase for SplitString transformations
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class StripTagsTest extends TestCase {
	const STRING_TO_STRIP = "I <script>contain</a> tags.";
	const EXPECTED_RESULT = "I contain tags.";

	protected function setUp() : void	{
		$this->f = new \ILIAS\Refinery\Factory(
			$this->createMock(\ILIAS\Data\Factory::class),
			$language = $this->createMock('\ilLanguage')
		);
		$this->strip_tags = $this->f->string()->stripTags();
	}

	public function testTransform() {
		$res = $this->strip_tags->transform(self::STRING_TO_STRIP);
		$this->assertEquals(self::EXPECTED_RESULT, $res);
	}

	public function testNoString() {
		$this->expectException(\InvalidArgumentException::class);
		$this->strip_tags->transform(0);
	}
}
