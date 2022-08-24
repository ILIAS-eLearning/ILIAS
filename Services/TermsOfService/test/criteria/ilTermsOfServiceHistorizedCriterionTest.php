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

class ilTermsOfServiceHistorizedCriterionTest extends ilTermsOfServiceCriterionBaseTest
{
    public function testHistorizedCriteriaCanBeBuildFromJsonStrings(): ilTermsOfServiceAcceptanceHistoryCriteriaBag
    {
        $criteria = [
            '{"id":"usr_language","value":{"lng":"de"}}',
            '{"id":"usr_global_role","value":{"role_id":4711}}'
        ];

        $config = '[' . implode(',', $criteria) . ']';

        $bag = new ilTermsOfServiceAcceptanceHistoryCriteriaBag($config);

        self::assertSame($config, $bag->toJson());
        self::assertCount(count($criteria), $bag);

        for ($i = 0, $iMax = count($criteria); $i < $iMax; $i++) {
            $criterion = new ilTermsOfServiceHistorizedCriterion(
                $bag[$i]['id'],
                $bag[$i]['value']
            );

            self::assertStringContainsString($criterion->getCriterionId(), $criteria[$i]);
            self::assertStringContainsString($criterion->getCriterionValue()->toJson(), $criteria[$i]);
        }

        return $bag;
    }

    /**
     * @depends testHistorizedCriteriaCanBeBuildFromJsonStrings
     * @param ilTermsOfServiceAcceptanceHistoryCriteriaBag $criteria_bag
     * @return void
     */
    public function testHistorizedDocumentCanBeCreated(
        ilTermsOfServiceAcceptanceHistoryCriteriaBag $criteria_bag
    ): void {
        $historizedDocument = new ilTermsOfServiceHistorizedDocument(
            $this->getMockBuilder(ilTermsOfServiceAcceptanceEntity::class)->disableOriginalConstructor()->getMock(),
            $criteria_bag
        );

        self::assertCount(count($criteria_bag), $historizedDocument->criteria());
    }
}
