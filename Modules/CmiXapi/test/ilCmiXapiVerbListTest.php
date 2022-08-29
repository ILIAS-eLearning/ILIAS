<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use PHPUnit\Framework\TestCase;

/**
 * Class ilObjSCORMValidatorTest
 *
 * @author Uwe Kohnle <support@internetlehrer-gmbh.de>
 */
class ilCmiXapiVerbListTest extends TestCase
{
    public function testVerbList(): void
    {
        $verbList = ilCmiXapiVerbList::getInstance();
        $this->assertEquals(
            'http://adlnet.gov/expapi/verbs/answered',
            $verbList->getVerbUri('answered')
        );
    }

    public function testVerbTranslation(): void
    {
        $lng = $this->getMockBuilder(ilLanguage::class)
                    ->disableOriginalConstructor()
                    ->getMock();

        $lng->expects($this->exactly(2))
            ->method('txt')
            ->willReturnOnConsecutiveCalls(
                '-cmix_answered',
                'answered'
            );

        $verbList = ilCmiXapiVerbList::getInstance();
        $this->assertEquals(
            'answered',
            $verbList->getVerbTranslation(
                $lng,
                'http://adlnet.gov/expapi/verbs/answered'
            )
        );
        $this->assertEquals(
            'answered',
            $verbList->getVerbTranslation(
                $lng,
                'http://adlnet.gov/expapi/verbs/answered'
            )
        );
    }
}
