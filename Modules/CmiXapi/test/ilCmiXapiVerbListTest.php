<?php declare(strict_types=1);

/* Copyright (c) 1998-2022 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;

/**
 * Class ilObjSCORMValidatorTest
 *
 * @author Uwe Kohnle <support@internetlehrer-gmbh.de>
 */
class ilCmiXapiVerbListTest extends TestCase
{
    public function testVerbList() : void
    {
        $verbList = ilCmiXapiVerbList::getInstance();
        $this->assertEquals(
            'http://adlnet.gov/expapi/verbs/answered',
            $verbList->getVerbUri('answered')
        );
    }
    
    public function testVerbTranslation() : void
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
