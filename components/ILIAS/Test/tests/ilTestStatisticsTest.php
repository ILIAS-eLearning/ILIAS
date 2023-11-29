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

class ilTestStatisticsTest extends ilTestBaseTestCase
{
    public function testConstruct(): void
    {
        $ilTestStatistics = new ilTestStatistics($this->createMock(ilTestEvaluationData::class));
        $this->assertInstanceOf(ilTestStatistics::class, $ilTestStatistics);
        $this->assertInstanceOf(ilStatistics::class, $ilTestStatistics->statistics);
    }

    public function testGetStatistics(): void
    {
        $ilTestStatistics = new ilTestStatistics($this->createMock(ilTestEvaluationData::class));
        $this->assertInstanceOf(ilTestStatistics::class, $ilTestStatistics);
        $this->assertInstanceOf(ilStatistics::class, $ilTestStatistics->getStatistics());
        $object = (object) [];
        $ilTestStatistics->statistics = $object;
        $this->assertEquals($object, $ilTestStatistics->getStatistics());
    }
}