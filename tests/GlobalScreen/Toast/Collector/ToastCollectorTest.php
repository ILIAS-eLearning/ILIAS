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

use ILIAS\GlobalScreen\Scope\Toast\Collector\ToastCollector;

require_once(__DIR__ . "/../BaseToastSetUp.php");

class ToastCollectorTest extends BaseToastSetUp
{
    public function testConstruct(): void
    {
        $povider = $this->getDummyToastProviderWithToasts([]);
        $collector = new ToastCollector([$povider]);
        $this->assertInstanceOf(ToastCollector::class, $collector);
    }

    public function testGetToasts(): void
    {
        $povider = $this->getDummyToastProviderWithToasts([]);
        $collector = new ToastCollector([$povider]);
        $this->assertEquals([], $collector->getToasts());

        $toast1 = $this->factory->standard(
            'Test Toast 1',
            new \ILIAS\UI\Implementation\Component\Symbol\Icon\Standard('test', 'Test Icon', 'small', false)
        );
        $toast2 = $this->factory->standard(
            'Test Toast 2',
            new \ILIAS\UI\Implementation\Component\Symbol\Icon\Standard('test', 'Test Icon', 'small', false)
        );
        $povider = $this->getDummyToastProviderWithToasts([$toast1, $toast2]);
        $collector = new ToastCollector([$povider]);
        $this->assertEquals([$toast1, $toast2], $collector->getToasts());
    }
}
