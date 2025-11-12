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
use ILIAS\MetaData\Copyright\CopyrightData;
use ILIAS\UI\Component\Symbol\Icon\Icon;
use ILIAS\UI\Implementation\Component\Symbol\Icon\Icon as IIcon;
use ILIAS\UI\Component\Link\Link;
use ILIAS\UI\Implementation\Component\Link\Link as ILink;

class CopyrightTest extends TestCase
{
    protected function getIcon(): Icon
    {
        return $this->getMockBuilder(IIcon::class)
                    ->disableOriginalConstructor()
                    ->getMock();
    }

    protected function getLink(): Link
    {
        return $this->getMockBuilder(ILink::class)
                    ->disableOriginalConstructor()
                    ->getMock();
    }

    protected function getRenderer(
        ?Icon $icon = null,
        ?Link $link = null
    ): RendererInterface {
        return new class ($icon, $link) extends NullRenderer {
            public ?string $exposed_copyright_data = null;

            public function __construct(
                protected ?Icon $icon,
                protected ?Link $link
            ) {
            }

            public function toUIComponents(CopyrightDataInterface $copyright): array
            {
                $this->exposed_copyright_data = $copyright->exposed_data;
                $res = [];
                if ($this->icon !== null) {
                    $res[] = $this->icon;
                }
                if ($this->link !== null) {
                    $res[] = $this->link;
                }
                return $res;
            }

            public function toImageOnly(CopyrightDataInterface $copyright): ?Icon
            {
                $this->exposed_copyright_data = $copyright->exposed_data;
                return $this->icon;
            }

            public function toLinkOnly(CopyrightDataInterface $copyright): ?Link
            {
                $this->exposed_copyright_data = $copyright->exposed_data;
                return $this->link;
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
        $icon = $this->getIcon();
        $link = $this->getLink();
        $renderer = $this->getRenderer($icon, $link);
        $copyright = new Copyright(
            $renderer,
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

        $this->assertCount(2, $components);
        $this->assertSame($icon, $components[0] ?? null);
        $this->assertSame($link, $components[1] ?? null);
        $this->assertSame('data of copyright', $renderer->exposed_copyright_data);
    }

    public function testPresentAsImageOnly(): void
    {
        $icon = $this->getIcon();
        $renderer = $this->getRenderer($icon);
        $copyright = new Copyright(
            $renderer,
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

        $image = $copyright->presentAsImageOnly();

        $this->assertSame($icon, $image);
        $this->assertSame('data of copyright', $renderer->exposed_copyright_data);
    }

    public function testPresentAsLinkOnly(): void
    {
        $link = $this->getLink();
        $renderer = $this->getRenderer(null, $link);
        $copyright = new Copyright(
            $renderer,
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

        $res_link = $copyright->presentAsLinkOnly();

        $this->assertSame($link, $res_link);
        $this->assertSame('data of copyright', $renderer->exposed_copyright_data);
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
