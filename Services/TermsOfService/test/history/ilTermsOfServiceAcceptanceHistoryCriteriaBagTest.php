<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceAcceptanceHistoryCriteriaBagTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceAcceptanceHistoryCriteriaBagTest extends \ilTermsOfServiceBaseTest
{
    /**
     *
     */
    public function testCriteriaCanBePassedAsArray()
    {
        $configCrit1 =  $this->getMockBuilder(\ilTermsOfServiceCriterionConfig::class)->getMock();

        $configCrit1
            ->expects($this->any())
            ->method('jsonSerialize')
            ->willReturn([
                'usr_language' => 'de'
            ]);
        
        $configCrit2 =  $this->getMockBuilder(\ilTermsOfServiceCriterionConfig::class)->getMock();

        $configCrit2
            ->expects($this->any())
            ->method('jsonSerialize')
            ->willReturn([
                'usr_global_role' => 4
            ]);

        $criterion1 = $this->getMockBuilder(\ilTermsOfServiceEvaluableCriterion::class)->getMock();

        $criterion1
            ->expects($this->any())
            ->method('getCriterionId')
            ->willReturn('crit1');

        $criterion1
            ->expects($this->any())
            ->method('getCriterionValue')
            ->willReturn($configCrit1);

        $criterion2 = $this->getMockBuilder(\ilTermsOfServiceEvaluableCriterion::class)->getMock();

        $criterion2
            ->expects($this->any())
            ->method('getCriterionId')
            ->willReturn('crit2');

        $criterion2
            ->expects($this->any())
            ->method('getCriterionValue')
            ->willReturn($configCrit2);

        $data = [
            $criterion1, $criterion2
        ];

        $bag = new \ilTermsOfServiceAcceptanceHistoryCriteriaBag($data);

        $this->assertCount(count($data), $bag);
        $this->assertArrayHasKey(0, $bag);
        $this->assertArrayHasKey(1, $bag);
        $this->assertArrayHasKey('id', $bag[0]);
        $this->assertArrayHasKey('value', $bag[0]);
        $this->assertArrayHasKey('id', $bag[1]);
        $this->assertArrayHasKey('value', $bag[1]);
        $this->assertEquals(
            '[{"id":"crit1","value":{"usr_language":"de"}},{"id":"crit2","value":{"usr_global_role":4}}]',
            $bag->toJson()
        );
    }

    /**
     * @expectedException \ilTermsOfServiceUnexpectedCriteriaBagContentException
     */
    public function testExceptionIsRaisedWhenAtLeastOneNonCriterionIsPassedInArrayOnCreation()
    {
        $configCrit1 =  $this->getMockBuilder(\ilTermsOfServiceCriterionConfig::class)->getMock();

        $criterion1 = $this->getMockBuilder(\ilTermsOfServiceEvaluableCriterion::class)->getMock();

        $criterion1
            ->expects($this->any())
            ->method('getCriterionId')
            ->willReturn('crit1');

        $criterion1
            ->expects($this->any())
            ->method('getCriterionValue')
            ->willReturn($configCrit1);

        $this->assertException(\ilTermsOfServiceUnexpectedCriteriaBagContentException::class);

        new \ilTermsOfServiceAcceptanceHistoryCriteriaBag([
            $criterion1, 5
        ]);
    }

    /**
     * @expectedException \ilTermsOfServiceUnexpectedCriteriaBagContentException
     */
    public function testExceptionIsRaisedWhenInvalidJsonDataIsPassedOnImport()
    {
        $configCrit1 =  $this->getMockBuilder(\ilTermsOfServiceCriterionConfig::class)->getMock();

        $criterion1 = $this->getMockBuilder(\ilTermsOfServiceEvaluableCriterion::class)->getMock();

        $criterion1
            ->expects($this->any())
            ->method('getCriterionId')
            ->willReturn('crit1');

        $criterion1
            ->expects($this->any())
            ->method('getCriterionValue')
            ->willReturn($configCrit1);

        $this->assertException(\ilTermsOfServiceUnexpectedCriteriaBagContentException::class);

        $bag = new \ilTermsOfServiceAcceptanceHistoryCriteriaBag();
        $bag->fromJson('5');
    }

    /**
     * @expectedException \ilTermsOfServiceUnexpectedCriteriaBagContentException
     */
    public function testExceptionIsRaisedWhenAtLeastOneInvalidElementIsPassedOnJsonStringImport()
    {
        $configCrit1 =  $this->getMockBuilder(\ilTermsOfServiceCriterionConfig::class)->getMock();

        $criterion1 = $this->getMockBuilder(\ilTermsOfServiceEvaluableCriterion::class)->getMock();

        $criterion1
            ->expects($this->any())
            ->method('getCriterionId')
            ->willReturn('crit1');

        $criterion1
            ->expects($this->any())
            ->method('getCriterionValue')
            ->willReturn($configCrit1);

        $this->assertException(\ilTermsOfServiceUnexpectedCriteriaBagContentException::class);

        $bag = new \ilTermsOfServiceAcceptanceHistoryCriteriaBag();
        $bag->fromJson('[{"invalid":"crit1","value":{"usr_language":"de"}},{"id":"crit2","value":{"usr_global_role":4}}]');
    }

    /**
     *
     */
    public function testCriteriaImportFromJsonStringWorksAsExpected()
    {
        $bag = new \ilTermsOfServiceAcceptanceHistoryCriteriaBag();
        $bag->fromJson('[{"id":"crit1","value":{"usr_language":"de"}},{"id":"crit2","value":{"usr_global_role":4}}]');

        $this->assertCount(count($bag), $bag);
        $this->assertArrayHasKey(0, $bag);
        $this->assertArrayHasKey(1, $bag);
        $this->assertArrayHasKey('id', $bag[0]);
        $this->assertArrayHasKey('value', $bag[0]);
        $this->assertArrayHasKey('id', $bag[1]);
        $this->assertArrayHasKey('value', $bag[1]);
        $this->assertEquals(
            '[{"id":"crit1","value":{"usr_language":"de"}},{"id":"crit2","value":{"usr_global_role":4}}]',
            $bag->toJson()
        );
    }
}
