<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestExportFactoryTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestExportFactoryTest extends ilTestBaseTestCase
{
    private ilTestExportFactory $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestExportFactory($this->createMock(ilObjTest::class));
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestExportFactory::class, $this->testObj);
    }

    public function testGetExporter() : void
    {
        $this->addGlobal_ilUser();
        $this->addGlobal_lng();
        $this->addGlobal_ilias();
        $this->addGlobal_ilDB();
        $this->addGlobal_ilLog();
        $this->addGlobal_ilErr();
        $this->addGlobal_tree();
        $this->addGlobal_ilAppEventHandler();
        $this->addGlobal_objDefinition();

        $objTest = new ilObjTest();

        $objTest->setQuestionSetType(ilObjTest::QUESTION_SET_TYPE_FIXED);
        $testObj = new ilTestExportFactory($objTest);
        $this->assertInstanceOf(ilTestExportFixedQuestionSet::class, $testObj->getExporter());

        $objTest->setQuestionSetType(ilObjTest::QUESTION_SET_TYPE_RANDOM);
        $testObj = new ilTestExportFactory($objTest);
        $this->assertInstanceOf(ilTestExportRandomQuestionSet::class, $testObj->getExporter());
    }
}
