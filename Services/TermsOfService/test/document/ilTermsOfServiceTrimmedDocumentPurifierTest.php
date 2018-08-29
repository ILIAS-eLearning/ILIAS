<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceTrimmedDocumentPurifierTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceTrimmedDocumentPurifierTest extends \ilTermsOfServiceCriterionBaseTest
{
	/**
	 * @return string[]
	 */
	public function stringsToTrimProvider(): array
	{
		return [
			[' phpunit ', 'phpunit'],
			["\n\r\tphpunit\n\r\t", 'phpunit'],
		];
	}

	/**
	 * @return array[]
	 */
	public function stringsArraysToTrimProvider(): array
	{
		return [
			[[' phpunit '], ['phpunit']],
			[["\n\r\tphpunit\n\r\t"], ['phpunit']],
		];
	}

	/**
	 * @dataProvider stringsToTrimProvider
	 * @param string $text
	 * @param string $result
	 */
	public function testSingleStringIsTrimmed(string $text, string $result)
	{
		$aggregated = $this
			->getMockBuilder(\ilHtmlPurifierInterface::class)
			->getMock();

		$aggregated
			->expects($this->once())
			->method('purify')
			->with($text)
			->willReturn($text);

		$purifier = new \ilTermsOfServiceTrimmedDocumentPurifier($aggregated);

		$this->assertEquals($result, $purifier->purify($text));
	}

	/**
	 * @dataProvider stringsArraysToTrimProvider
	 * @param string[] $texts
	 * @param string[] $result
	 */
	public function testArrayOfStringIsTrimmed(array $texts, array $result)
	{
		$aggregated = $this
			->getMockBuilder(\ilHtmlPurifierInterface::class)
			->getMock();

		$aggregated
			->expects($this->exactly(count($texts)))
			->method('purify')
			->with($this->isType('string'))
			->willReturnArgument(0);

		$purifier = new \ilTermsOfServiceTrimmedDocumentPurifier($aggregated);

		$this->assertEquals($result, $purifier->purifyArray($texts));
	}
}