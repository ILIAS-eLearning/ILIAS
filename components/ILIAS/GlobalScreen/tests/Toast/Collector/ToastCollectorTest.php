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

use ILIAS\GlobalScreen\Scope\Toast\Collector\ToastCollector;
use ILIAS\GlobalScreen\Identification\IdentificationFactory;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;

require_once(__DIR__ . "/../BaseToastSetUp.php");

class ToastCollectorTest extends BaseToastSetUp
{
    public function testConstruct(): void
    {
        $provider = $this->getDummyToastProviderWithToasts([]);
        $collector = new ToastCollector([$provider]);
        $this->assertInstanceOf(ToastCollector::class, $collector);
    }

    public function testGetToasts(): void
    {
        $provider = $this->getDummyToastProviderWithToasts([]);
        $collector = new ToastCollector([$provider]);
        $this->assertEquals([], $collector->getToasts());

        $id_one = $this->createMock(IdentificationInterface::class);
        $id_two = $this->createMock(IdentificationInterface::class);

        $toast1 = $this->factory->standard(
            $id_one,
            'Test Toast 1'
        );

        $toast2 = $this->factory->standard(
            $id_two,
            'Test Toast 2'
        );

        $provider = $this->getDummyToastProviderWithToasts([$toast1, $toast2]);
        $collector = new ToastCollector([$provider]);
        $this->assertEquals([$toast1, $toast2], $collector->getToasts());
    }
}
