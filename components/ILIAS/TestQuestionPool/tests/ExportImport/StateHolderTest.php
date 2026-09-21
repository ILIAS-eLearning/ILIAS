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

use ILIAS\Export\ExportHandler\I\Consumer\ExportConfig\CollectionInterface as ExportConfig;
use ILIAS\Export\ExportHandler\I\Target\HandlerInterface as ExportTarget;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Bridge\StateHolder;

class StateHolderTest extends assBaseTestCase
{
    public function testCreateReplacesExistingState(): void
    {
        $holder = new StateHolder();
        $config = $this->createMock(ExportConfig::class);
        $first_target = $this->createMock(ExportTarget::class);
        $second_target = $this->createMock(ExportTarget::class);

        $first = $holder->create($first_target, $config, 'first');
        $second = $holder->create($second_target, $config, 'second');

        $this->assertNotSame($first, $second);
        $this->assertSame($second, $holder->get());
        $this->assertSame($second_target, $second->target());
        $this->assertSame('second', $second->getOption());
    }

    public function testClearRemovesExistingState(): void
    {
        $holder = new StateHolder();
        $holder->create(
            $this->createMock(ExportTarget::class),
            $this->createMock(ExportConfig::class)
        );

        $holder->clear();

        $this->assertFalse($holder->exists());
        $this->expectException(RuntimeException::class);
        $holder->get();
    }
}
