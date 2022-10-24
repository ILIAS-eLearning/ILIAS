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
 * Class ilTestResultsToXMLTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestResultsToXMLTest extends ilTestBaseTestCase
{
    private ilTestResultsToXML $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestResultsToXML(
            0,
            false
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestResultsToXML::class, $this->testObj);
    }

    public function testIncludeRandomTestQuestionsEnabled(): void
    {
        $this->testObj->setIncludeRandomTestQuestionsEnabled(false);
        $this->assertFalse($this->testObj->isIncludeRandomTestQuestionsEnabled());

        $this->testObj->setIncludeRandomTestQuestionsEnabled(true);
        $this->assertTrue($this->testObj->isIncludeRandomTestQuestionsEnabled());
    }
}
