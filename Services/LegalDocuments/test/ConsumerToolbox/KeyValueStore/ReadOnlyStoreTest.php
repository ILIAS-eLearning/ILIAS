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

namespace ILIAS\LegalDocuments\test\ConsumerToolbox\KeyValueStore;

use ILIAS\LegalDocuments\test\ContainerMock;
use ILIAS\LegalDocuments\ConsumerToolbox\KeyValueStore;
use ILIAS\LegalDocuments\ConsumerToolbox\KeyValueStore\ReadOnlyStore;
use PHPUnit\Framework\TestCase;
use Exception;

require_once __DIR__ . '/../../ContainerMock.php';

class ReadOnlyStoreTest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(ReadOnlyStore::class, new ReadOnlyStore($this->mock(KeyValueStore::class)));
    }

    public function testValue(): void
    {
        $instance = new ReadOnlyStore($this->mockMethod(KeyValueStore::class, 'value', ['foo'], 'bar'));

        $this->assertSame('bar', $instance->value('foo'));
    }

    public function testUpdate(): void
    {
        $this->expectException(Exception::class);

        $instance = new ReadOnlyStore($this->mock(KeyValueStore::class));
        $instance->update('foo', 'bar');
    }
}
