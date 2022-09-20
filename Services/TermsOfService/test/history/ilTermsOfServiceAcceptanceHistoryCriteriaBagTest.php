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
 * Class ilTermsOfServiceAcceptanceHistoryCriteriaBagTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceAcceptanceHistoryCriteriaBagTest extends ilTermsOfServiceBaseTest
{
    public function testCriteriaCanBePassedAsArray(): ilTermsOfServiceAcceptanceHistoryCriteriaBag
    {
        $configCrit1 = $this->getMockBuilder(ilTermsOfServiceCriterionConfig::class)->getMock();

        $configCrit1
            ->method('jsonSerialize')
            ->willReturn([
                'usr_language' => 'de'
            ]);

        $configCrit2 = $this->getMockBuilder(ilTermsOfServiceCriterionConfig::class)->getMock();

        $configCrit2
            ->method('jsonSerialize')
            ->willReturn([
                'usr_global_role' => 4
            ]);

        $criterion1 = $this->getMockBuilder(ilTermsOfServiceEvaluableCriterion::class)->getMock();

        $criterion1
            ->method('getCriterionId')
            ->willReturn('crit1');

        $criterion1
            ->method('getCriterionValue')
            ->willReturn($configCrit1);

        $criterion2 = $this->getMockBuilder(ilTermsOfServiceEvaluableCriterion::class)->getMock();

        $criterion2
            ->method('getCriterionId')
            ->willReturn('crit2');

        $criterion2
            ->method('getCriterionValue')
            ->willReturn($configCrit2);

        $data = [
            $criterion1,
            $criterion2
        ];

        $bag = new ilTermsOfServiceAcceptanceHistoryCriteriaBag($data);

        $this->assertCount(count($data), $bag);
        $this->assertArrayHasKey(0, $bag);
        $this->assertArrayHasKey(1, $bag);
        $this->assertArrayHasKey('id', $bag[0]);
        $this->assertArrayHasKey('value', $bag[0]);
        $this->assertArrayHasKey('id', $bag[1]);
        $this->assertArrayHasKey('value', $bag[1]);
        $this->assertSame(
            '[{"id":"crit1","value":{"usr_language":"de"}},{"id":"crit2","value":{"usr_global_role":4}}]',
            $bag->toJson()
        );

        return $bag;
    }

    /**
     * @depends testCriteriaCanBePassedAsArray
     */
    public function testCriteriaCanBePassedAsString(ilTermsOfServiceAcceptanceHistoryCriteriaBag $bag): void
    {
        $newBag = new ilTermsOfServiceAcceptanceHistoryCriteriaBag($bag->toJson());
        $this->assertSame($bag->toJson(), $newBag->toJson());
    }

    public function testExceptionIsRaisedWhenAtLeastOneNonCriterionIsPassedInArrayOnCreation(): void
    {
        $configCrit1 = $this->getMockBuilder(ilTermsOfServiceCriterionConfig::class)->getMock();

        $criterion1 = $this->getMockBuilder(ilTermsOfServiceEvaluableCriterion::class)->getMock();

        $criterion1
            ->method('getCriterionId')
            ->willReturn('crit1');

        $criterion1
            ->method('getCriterionValue')
            ->willReturn($configCrit1);

        $this->expectException(ilTermsOfServiceUnexpectedCriteriaBagContentException::class);

        new ilTermsOfServiceAcceptanceHistoryCriteriaBag([
            $criterion1,
            5
        ]);
    }

    public function invalidJsonDataProvider(): array
    {
        $object = new stdClass();
        $object->not_expected = 'phpunit';
        $object->value = ['id' => $object->not_expected, 'value' => ['usr_language' => 'de']];

        $data = [
            'Float' => [json_encode(5.1, JSON_THROW_ON_ERROR)],
            'Integer' => [json_encode(5, JSON_THROW_ON_ERROR)],
            'String' => [json_encode('5', JSON_THROW_ON_ERROR)],
            'Null' => [json_encode(null, JSON_THROW_ON_ERROR)],
            'Object' => [json_encode($object, JSON_THROW_ON_ERROR)],
            'Bool' => [json_encode(true, JSON_THROW_ON_ERROR)],
        ];

        $arrayOfTypes = [];

        foreach ($data as $type => $values) {
            $arrayOfTypes['Array of ' . $type] = [json_encode([json_decode($values[0], false, 512, JSON_THROW_ON_ERROR)], JSON_THROW_ON_ERROR)];
        }

        return $data + $arrayOfTypes;
    }

    /**
     * @dataProvider invalidJsonDataProvider
     */
    public function testExceptionIsRaisedWhenInvalidJsonDataIsPassedOnImport($mixedData): void
    {
        $configCrit1 = $this->getMockBuilder(ilTermsOfServiceCriterionConfig::class)->getMock();

        $criterion1 = $this->getMockBuilder(ilTermsOfServiceEvaluableCriterion::class)->getMock();

        $criterion1
            ->method('getCriterionId')
            ->willReturn('crit1');

        $criterion1
            ->method('getCriterionValue')
            ->willReturn($configCrit1);

        $this->expectException(ilTermsOfServiceUnexpectedCriteriaBagContentException::class);

        $bag = new ilTermsOfServiceAcceptanceHistoryCriteriaBag();
        $bag->fromJson($mixedData);
    }

    public function testExceptionIsRaisedWhenAtLeastOneInvalidElementIsPassedOnJsonStringImport(): void
    {
        $configCrit1 = $this->getMockBuilder(ilTermsOfServiceCriterionConfig::class)->getMock();

        $criterion1 = $this->getMockBuilder(ilTermsOfServiceEvaluableCriterion::class)->getMock();

        $criterion1
            ->method('getCriterionId')
            ->willReturn('crit1');

        $criterion1
            ->method('getCriterionValue')
            ->willReturn($configCrit1);

        $this->expectException(ilTermsOfServiceUnexpectedCriteriaBagContentException::class);

        $bag = new ilTermsOfServiceAcceptanceHistoryCriteriaBag();
        $bag->fromJson('[{"invalid":"crit1","value":{"usr_language":"de"}},{"id":"crit2","value":{"usr_global_role":4}}]');
    }

    public function testCriteriaImportFromJsonStringWorksAsExpected(): void
    {
        $bag = new ilTermsOfServiceAcceptanceHistoryCriteriaBag();
        $bag->fromJson('[{"id":"crit1","value":{"usr_language":"de"}},{"id":"crit2","value":{"usr_global_role":4}}]');

        $this->assertCount(count($bag), $bag);
        $this->assertArrayHasKey(0, $bag);
        $this->assertArrayHasKey(1, $bag);
        $this->assertArrayHasKey('id', $bag[0]);
        $this->assertArrayHasKey('value', $bag[0]);
        $this->assertArrayHasKey('id', $bag[1]);
        $this->assertArrayHasKey('value', $bag[1]);
        $this->assertSame(
            '[{"id":"crit1","value":{"usr_language":"de"}},{"id":"crit2","value":{"usr_global_role":4}}]',
            $bag->toJson()
        );
    }
}
