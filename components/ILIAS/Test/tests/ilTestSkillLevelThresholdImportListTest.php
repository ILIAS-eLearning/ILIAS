<?php

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

declare(strict_types=1);

/**
 * Class ilTestSkillLevelThresholdImportListTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestSkillLevelThresholdImportListTest extends ilTestBaseTestCase
{
    private ilTestSkillLevelThresholdImportList $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestSkillLevelThresholdImportList();
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestSkillLevelThresholdImportList::class, $this->testObj);
    }

    public function testAddOriginalSkillTitle(): void
    {
        $skillBaseId = 17;
        $skillTrefId = 15;
        $originalSkillTitle = 'Test';
        $this->testObj->addOriginalSkillTitle($skillBaseId, $skillTrefId, $originalSkillTitle);

        $reflProp = new ReflectionProperty($this->testObj, 'originalSkillTitles');
        $reflProp->setAccessible(true);

        $this->assertEquals(["$skillBaseId:$skillTrefId" => $originalSkillTitle], $reflProp->getValue($this->testObj));
    }

    public function testAddOriginalSkillPath(): void
    {
        $skillBaseId = 17;
        $skillTrefId = 15;
        $originalSkillPath = 'test/path';
        $this->testObj->addOriginalSkillPath($skillBaseId, $skillTrefId, $originalSkillPath);

        $reflProp = new ReflectionProperty($this->testObj, 'originalSkillPaths');
        $reflProp->setAccessible(true);

        $this->assertEquals(["$skillBaseId:$skillTrefId" => $originalSkillPath], $reflProp->getValue($this->testObj));
    }

    public function testAddSkillLevelThreshold(): void
    {
        $testSkillLevelThresholdImport = new ilTestSkillLevelThresholdImport();
        $this->testObj->addSkillLevelThreshold($testSkillLevelThresholdImport);

        $reflProp = new ReflectionProperty($this->testObj, 'importedSkillLevelThresholds');
        $reflProp->setAccessible(true);

        $this->assertEquals([$testSkillLevelThresholdImport], $reflProp->getValue($this->testObj));
    }

    public function testCurrent(): void
    {
        $testSkillLevelThresholdImport = new ilTestSkillLevelThresholdImport();
        $this->testObj->addSkillLevelThreshold($testSkillLevelThresholdImport);

        $this->assertEquals($testSkillLevelThresholdImport, $this->testObj->current());
    }

    public function testNext(): void
    {
        $testSkillLevelThresholdImport1 = new ilTestSkillLevelThresholdImport();
        $testSkillLevelThresholdImport2 = new ilTestSkillLevelThresholdImport();

        $this->testObj->addSkillLevelThreshold($testSkillLevelThresholdImport1);
        $this->testObj->addSkillLevelThreshold($testSkillLevelThresholdImport2);

        $this->testObj->next();
        $this->assertEquals($testSkillLevelThresholdImport2, $this->testObj->current());
    }

    public function testKey(): void
    {
        $testSkillLevelThresholdImport1 = new ilTestSkillLevelThresholdImport();
        $testSkillLevelThresholdImport2 = new ilTestSkillLevelThresholdImport();

        $this->testObj->addSkillLevelThreshold($testSkillLevelThresholdImport1);
        $this->testObj->addSkillLevelThreshold($testSkillLevelThresholdImport2);

        $this->testObj->next();
        $this->assertEquals(1, $this->testObj->key());
    }

    public function testValid(): void
    {
        $this->assertFalse($this->testObj->valid());
        $testSkillLevelThresholdImport1 = new ilTestSkillLevelThresholdImport();
        $testSkillLevelThresholdImport2 = new ilTestSkillLevelThresholdImport();

        $this->testObj->addSkillLevelThreshold($testSkillLevelThresholdImport1);
        $this->testObj->addSkillLevelThreshold($testSkillLevelThresholdImport2);

        $this->assertTrue($this->testObj->valid());
    }

    public function testRewind(): void
    {
        $testSkillLevelThresholdImport1 = new ilTestSkillLevelThresholdImport();
        $testSkillLevelThresholdImport2 = new ilTestSkillLevelThresholdImport();

        $this->testObj->addSkillLevelThreshold($testSkillLevelThresholdImport1);
        $this->testObj->addSkillLevelThreshold($testSkillLevelThresholdImport2);

        $this->testObj->next();
        $this->testObj->next();
        $this->testObj->rewind();
        $this->assertEquals($testSkillLevelThresholdImport1, $this->testObj->current());
    }
}
