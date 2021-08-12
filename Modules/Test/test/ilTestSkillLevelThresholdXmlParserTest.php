<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestSkillLevelThresholdXmlParserTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestSkillLevelThresholdXmlParserTest extends ilTestBaseTestCase
{
    private ilTestSkillLevelThresholdXmlParser $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestSkillLevelThresholdXmlParser();
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestSkillLevelThresholdXmlParser::class, $this->testObj);
    }
}