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
 * Class ilObjTestDynamicQuestionSetConfigTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilObjTestDynamicQuestionSetConfigTest extends ilTestBaseTestCase
{
    private ilObjTestDynamicQuestionSetConfig $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilObjTestDynamicQuestionSetConfig(
            $this->getMockBuilder(ilTree::class)->disableOriginalConstructor()->getMock(),
            $this->createMock(ilDBInterface::class),
            $this->createMock(ilComponentRepository::class),
            $this->getMockBuilder(ilObjTest::class)->disableOriginalConstructor()->getMock()
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilObjTestDynamicQuestionSetConfig::class, $this->testObj);
    }

    public function testSourceQuestionPoolId(): void
    {
        $this->testObj->setSourceQuestionPoolId(125);
        $this->assertEquals(125, $this->testObj->getSourceQuestionPoolId());
    }

    public function testSourceQuestionPoolTitle(): void
    {
        $this->testObj->setSourceQuestionPoolTitle("testString");
        $this->assertEquals("testString", $this->testObj->getSourceQuestionPoolTitle());
    }

    public function testAnswerStatusFilterEnabled(): void
    {
        $this->testObj->setAnswerStatusFilterEnabled(false);
        $this->assertFalse($this->testObj->isAnswerStatusFilterEnabled());

        $this->testObj->setAnswerStatusFilterEnabled(true);
        $this->assertTrue($this->testObj->isAnswerStatusFilterEnabled());
    }

    public function testTaxonomyFilterEnabled(): void
    {
        $this->testObj->setTaxonomyFilterEnabled(false);
        $this->assertFalse($this->testObj->isTaxonomyFilterEnabled());

        $this->testObj->setTaxonomyFilterEnabled(true);
        $this->assertTrue($this->testObj->isTaxonomyFilterEnabled());
    }

    public function testOrderingTaxonomyId(): void
    {
        $this->testObj->setOrderingTaxonomyId(1231);
        $this->assertEquals(1231, $this->testObj->getOrderingTaxonomyId());
    }

    public function testInitFromArray(): void
    {
        $expected = [
            "source_qpl_fi" => 126,
            "source_qpl_title" => "testString",
            "answer_filter_enabled" => true,
            "tax_filter_enabled" => false,
            "order_tax" => 1369,
        ];

        $this->testObj->initFromArray($expected);

        $this->assertEquals($expected["source_qpl_fi"], $this->testObj->getSourceQuestionPoolId());
        $this->assertEquals($expected["source_qpl_title"], $this->testObj->getSourceQuestionPoolTitle());
        $this->assertEquals($expected["answer_filter_enabled"], $this->testObj->isAnswerStatusFilterEnabled());
        $this->assertEquals($expected["tax_filter_enabled"], $this->testObj->isTaxonomyFilterEnabled());
        $this->assertEquals($expected["order_tax"], $this->testObj->getOrderingTaxonomyId());
    }

    public function testIsQuestionSetConfigured(): void
    {
        $this->assertFalse($this->testObj->isQuestionSetConfigured());

        $this->testObj->setSourceQuestionPoolId(168);
        $this->assertTrue($this->testObj->isQuestionSetConfigured());
    }

    public function testDoesQuestionSetRelatedDataExist(): void
    {
        $this->assertFalse($this->testObj->isQuestionSetConfigured());
        $this->assertFalse($this->testObj->doesQuestionSetRelatedDataExist());

        $this->testObj->setSourceQuestionPoolId(168);
        $this->assertTrue($this->testObj->isQuestionSetConfigured());
        $this->assertTrue($this->testObj->doesQuestionSetRelatedDataExist());
    }

    public function testGetSourceQuestionPoolSummaryString(): void
    {
        $this->addGlobal_ilDB();

        $this->testObj->setSourceQuestionPoolId(16);
        $this->testObj->setSourceQuestionPoolTitle("testTitle");

        $lng_mock = $this->createMock(ilLanguage::class);
        $lng_mock->expects($this->any())
                 ->method("txt")
                 ->with("tst_dyn_quest_set_src_qpl_summary_string_deleted")
                 ->willReturn("testString");

        $result = $this->testObj->getSourceQuestionPoolSummaryString($lng_mock);

        $this->assertEquals("testString", $result);
    }

    public function testGetDepenciesInVulnerableStateMessage(): void
    {
        $this->addGlobal_ilDB();

        $this->testObj->setSourceQuestionPoolId(16);
        $this->testObj->setSourceQuestionPoolTitle("testTitle");

        $lng_mock = $this->createMock(ilLanguage::class);
        $lng_mock->expects($this->any())
                 ->method("txt")
                 ->with("tst_dyn_quest_set_pool_trashed")
                 ->willReturn("testString");

        $result = $this->testObj->getDepenciesInVulnerableStateMessage($lng_mock);

        $this->assertEquals("testString", $result);
    }

    public function testAreDepenciesBroken(): void
    {
        $this->addGlobal_ilDB();

        $this->assertFalse($this->testObj->areDepenciesBroken());

        $this->testObj->setSourceQuestionPoolId(16);
        $this->assertTrue($this->testObj->areDepenciesBroken());
    }

    public function testGetDepenciesBrokenMessage(): void
    {
        $this->addGlobal_ilDB();

        $this->testObj->setSourceQuestionPoolId(16);
        $this->testObj->setSourceQuestionPoolTitle("testTitle");

        $lng_mock = $this->createMock(ilLanguage::class);
        $lng_mock->expects($this->any())
                 ->method("txt")
                 ->with("tst_dyn_quest_set_pool_deleted")
                 ->willReturn("testString");

        $result = $this->testObj->getDepenciesBrokenMessage($lng_mock);

        $this->assertEquals("testString", $result);
    }

    public function testGetHiddenTabsOnBrokenDepencies(): void
    {
        $expected = [
            'settings',
            'manscoring',
            'scoringadjust',
            'statistics',
            'history',
            'export'
        ];

        $result = $this->testObj->getHiddenTabsOnBrokenDepencies();
        $this->assertEquals($expected, $result);
    }

    public function testIsResultTaxonomyFilterSupported(): void
    {
        $this->assertFalse($this->testObj->isResultTaxonomyFilterSupported());
    }

    public function testIsAnyQuestionFilterEnabled(): void
    {
        $this->assertFalse($this->testObj->isAnyQuestionFilterEnabled());

        $this->testObj->setTaxonomyFilterEnabled(false);
        $this->assertFalse($this->testObj->isAnyQuestionFilterEnabled());

        $this->testObj->setTaxonomyFilterEnabled(true);
        $this->assertTrue($this->testObj->isAnyQuestionFilterEnabled());
    }

    /* ak: please do not make wrong assumptions on other components, aka testing unspecified
           behaviour of other components. ilLink::_getLink needs a ref id, please provide one
    public function testGetSourceQuestionPoolLink() : void
    {
        $this->addGlobal_ilDB();
        $this->addGlobal_ilObjDataCache();


        $this->testObj->setSourceQuestionPoolTitle("testTitle");
        $this->testObj->setSourceQuestionPoolId(125);

        $result = $this->testObj->getSourceQuestionPoolLink();
        $this->assertEquals(
            '<a href="' . ILIAS_HTTP_PATH . '/goto.php?target=qpl_&client_id=' . CLIENT_ID . '" alt="' . $this->testObj->getSourceQuestionPoolTitle() . '">' . $this->testObj->getSourceQuestionPoolTitle() . '</a>',
            $result
        );
    }*/
}
