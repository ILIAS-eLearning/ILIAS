<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateValueReplacementTest extends ilCertificateBaseTestCase
{
	public function testReplace()
	{
		$replacement = new ilCertificateValueReplacement();

		$placeholderValues = array('NAME' => 'Peter', 'PRIZE' => 'a fantastic prize');

		$certificateContent = '<xml> 
[BACKGROUND_IMAGE]
Hurray [NAME] you have received [PRIZE]
</xml>';

		$backgroundPath = '/some/where/path/background.jpg';

		$replacedContent = $replacement->replace($placeholderValues, $certificateContent, $backgroundPath);

		$expected = '<xml> 
/some/where/path/background.jpg
Hurray Peter you have received a fantastic prize
</xml>';

		$this->assertEquals($expected, $replacedContent);
	}
}
