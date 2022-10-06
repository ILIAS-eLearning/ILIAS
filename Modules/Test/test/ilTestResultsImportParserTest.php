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

/**
 * Class ilTestResultsImportParserTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestResultsImportParserTest extends ilTestBaseTestCase
{
    private ilTestResultsImportParser $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $testObject = $this->createMock(ilObjTest::class);
        $this->testObj = new ilTestResultsImportParser("", $testObject);
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestResultsImportParser::class, $this->testObj);
    }

    public function testQuestionIdMapping(): void
    {
        $expected = [
            12 => 17,
            124 => 19
        ];
        $this->testObj->setQuestionIdMapping($expected);
        $this->assertEquals($expected, $this->testObj->getQuestionIdMapping());
    }

    public function testSrcPoolDefIdMapping(): void
    {
        $expected = [
            12 => 17,
            124 => 19
        ];
        $this->testObj->setSrcPoolDefIdMapping($expected);
        $this->assertEquals($expected, $this->testObj->getSrcPoolDefIdMapping());
    }
}
