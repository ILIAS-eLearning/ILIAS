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

namespace ILIAS\MetaData\Services\CopyrightHelper;

use PHPUnit\Framework\TestCase;
use ILIAS\MetaData\Copyright\RendererInterface;
use ILIAS\MetaData\Copyright\Identifiers\HandlerInterface as IdentifierHandler;
use ILIAS\MetaData\Copyright\EntryInterface;
use ILIAS\MetaData\Copyright\NullEntry;
use ILIAS\MetaData\Copyright\CopyrightDataInterface;
use ILIAS\MetaData\Copyright\NullCopyrightData;
use ILIAS\MetaData\Copyright\Identifiers\NullHandler;
use ILIAS\MetaData\Copyright\NullRenderer;
use PHPUnit\Framework\MockObject\MockObject;
use ILIAS\UI\Component\Legacy\Content;
use ILIAS\UI\Implementation\Component\Legacy\Content as ILegacy;
use PHPUnit\Framework\MockObject\Rule\AnyInvokedCount;
use ILIAS\MetaData\Copyright\CopyrightData;

class CopyrightTest extends TestCase
{
    protected function getLegacyComponent(): MockObject|Content
    {
        return $this->getMockBuilder(ILegacy::class)
                    ->disableOriginalConstructor()
                    ->addMethods(['exposeData'])
                    ->getMock();
    }

    protected function getRenderer(): RendererInterface
    {
        $legacy_component = $this->getLegacyComponent();
        return new class ($legacy_component, $this->any()) extends NullRenderer {
            public function __construct(
                protected MockObject|Content $legacy,
                protected AnyInvokedCount $any
            ) {
            }

            public function toUIComponents(CopyrightDataInterface $copyright): array
            {
                $legacy_clone = clone $this->legacy;
                $legacy_clone->expects($this->any)
                             ->method('exposeData')
                             ->willReturn($copyright->exposed_data);
                return [$legacy_clone];
            }

            public function toString(CopyrightDataInterface $copyright): string
            {
                return $copyright->exposed_data;
            }
        };
    }

    protected function getIdentifierHandler(): IdentifierHandler
    {
        return new class () extends NullHandler {
            public function buildIdentifierFromEntryID(int $entry_id): string
            {
                return 'identifier_' . $entry_id;
            }
        };
    }

    protected function getEntry(
        bool $is_default,
        bool $is_outdated,
        int $id,
        string $title,
        string $description,
        string $cp_data
    ): EntryInterface {
        return new class ($is_default, $is_outdated, $id, $title, $description, $cp_data) extends NullEntry {
            public function __construct(
                protected bool $is_default,
                protected bool $is_outdated,
                protected int $id,
                protected string $title,
                protected string $description,
                protected string $cp_data
            ) {
            }

            public function id(): int
            {
                return $this->id;
            }

            public function title(): string
            {
                return $this->title;
            }

            public function description(): string
            {
                return $this->description;
            }

            public function isDefault(): bool
            {
                return $this->is_default;
            }

            public function isOutdated(): bool
            {
                return $this->is_outdated;
            }

            public function copyrightData(): CopyrightDataInterface
            {
                return new class ($this->cp_data) extends NullCopyrightData {
                    public function __construct(public string $exposed_data)
                    {
                    }
                };
            }
        };
    }

    public function testIsDefaultTrue(): void
    {
        $copyright = new Copyright(
            $this->getRenderer(),
            $this->getIdentifierHandler(),
            $this->getEntry(
                true,
                false,
                35,
                'cp title',
                'cp description',
                'data of copyright'
            )
        );

        $this->assertTrue($copyright->isDefault());
    }

    public function testIsDefaultFalse(): void
    {
        $copyright = new Copyright(
            $this->getRenderer(),
            $this->getIdentifierHandler(),
            $this->getEntry(
                false,
                false,
                35,
                'cp title',
                'cp description',
                'data of copyright'
            )
        );

        $this->assertFalse($copyright->isDefault());
    }

    public function testIsOutdatedTrue(): void
    {
        $copyright = new Copyright(
            $this->getRenderer(),
            $this->getIdentifierHandler(),
            $this->getEntry(
                false,
                true,
                35,
                'cp title',
                'cp description',
                'data of copyright'
            )
        );

        $this->assertTrue($copyright->isOutdated());
    }

    public function testIsOutdatedFalse(): void
    {
        $copyright = new Copyright(
            $this->getRenderer(),
            $this->getIdentifierHandler(),
            $this->getEntry(
                false,
                false,
                35,
                'cp title',
                'cp description',
                'data of copyright'
            )
        );

        $this->assertFalse($copyright->isOutdated());
    }

    public function testIdentifier(): void
    {
        $copyright = new Copyright(
            $this->getRenderer(),
            $this->getIdentifierHandler(),
            $this->getEntry(
                false,
                false,
                35,
                'cp title',
                'cp description',
                'data of copyright'
            )
        );

        $this->assertSame('identifier_35', $copyright->identifier());
    }

    public function testTitle(): void
    {
        $copyright = new Copyright(
            $this->getRenderer(),
            $this->getIdentifierHandler(),
            $this->getEntry(
                false,
                false,
                35,
                'cp title',
                'cp description',
                'data of copyright'
            )
        );

        $this->assertSame('cp title', $copyright->title());
    }

    public function testDescription(): void
    {
        $copyright = new Copyright(
            $this->getRenderer(),
            $this->getIdentifierHandler(),
            $this->getEntry(
                false,
                false,
                35,
                'cp title',
                'cp description',
                'data of copyright'
            )
        );

        $this->assertSame('cp description', $copyright->description());
    }

    public function testPresentAsUIComponents(): void
    {
        $copyright = new Copyright(
            $this->getRenderer(),
            $this->getIdentifierHandler(),
            $this->getEntry(
                false,
                false,
                35,
                'cp title',
                'cp description',
                'data of copyright'
            )
        );

        $components = $copyright->presentAsUIComponents();

        $this->assertCount(1, $components);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->assertSame('data of copyright', $components[0]->exposeData());
    }

    public function testPresentAsString(): void
    {
        $copyright = new Copyright(
            $this->getRenderer(),
            $this->getIdentifierHandler(),
            $this->getEntry(
                false,
                false,
                35,
                'cp title',
                'cp description',
                'data of copyright'
            )
        );

        $this->assertSame('data of copyright', $copyright->presentAsString());
    }
}
