<?php declare(strict_types=1);

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
 ********************************************************************
 */

class ilTermsOfServiceHistorizedCriterionTest extends ilTermsOfServiceCriterionBaseTest
{
    public function testHistorizedCriterion() : void
    {
        $config = '[{"id":"usr_language","value":{"lng":"de"}}]';

        $criterion = new ilTermsOfServiceHistorizedCriterion(
            'usr_language',
            json_decode($config, false, 512, JSON_THROW_ON_ERROR)
        );

        $this->assertEquals('usr_language', $criterion->getCriterionId());
        $this->assertEquals($config, $criterion->getCriterionValue()->toJson());
        $this->assertEquals($config, $criterion->getCriterionValue()->toJson());
    }
}
