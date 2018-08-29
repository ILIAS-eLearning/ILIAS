<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceTrimmedDocumentPurifierTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceDocumentsContainsHtmlValidatorTest extends \ilTermsOfServiceCriterionBaseTest
{
	/**
	 * @return array
	 */
	public function textProvider(): array
	{
		return [
			['phpunit', false, ],
			['php<b>unit</b>', true, ],
			['<html><body>php<b>unit</b></body></html>', true, ],
		];
	}

	/**
	 * @dataProvider textProvider
	 * @param string $text
	 * @param bool $result
	 */
	public function testSim(string $text, bool $result)
	{
		$validator = new \ilTermsOfServiceDocumentsContainsHtmlValidator($text);
		$this->assertEquals($result, $validator->isValid());
	}
}