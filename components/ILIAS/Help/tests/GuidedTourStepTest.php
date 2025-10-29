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

use PHPUnit\Framework\TestCase;
use ILIAS\Help\GuidedTour\Settings\PermissionType;
use ILIAS\Help\GuidedTour\InternalDataService;
use ILIAS\Help\GuidedTour\Step\StepType;

class GuidedTourStepTest extends TestCase
{
    protected function tearDown(): void
    {
    }

    public function testStep(): void
    {
        $data = new InternalDataService();
        $step = $data->step(
            14,
            13,
            20,
            StepType::Mainbar,
            "my_el"
        );

        $this->assertEquals(
            14,
            $step->getId()
        );

        $this->assertEquals(
            13,
            $step->getTourId()
        );

        $this->assertEquals(
            StepType::Mainbar,
            $step->getType()
        );

        $this->assertEquals(
            20,
            $step->getOrderNr()
        );

        $this->assertEquals(
            "my_el",
            $step->getElementId()
        );
    }
}
