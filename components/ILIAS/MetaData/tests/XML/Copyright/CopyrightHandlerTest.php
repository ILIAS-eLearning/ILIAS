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

namespace ILIAS\MetaData\XML\Copyright;

use PHPUnit\Framework\TestCase;
use ILIAS\MetaData\Copyright\RepositoryInterface as CopyrightRepository;
use ILIAS\MetaData\Copyright\Identifiers\HandlerInterface as IdentifierHandler;
use ILIAS\MetaData\Copyright\NullRepository;
use ILIAS\MetaData\Copyright\Identifiers\NullHandler;
use ILIAS\MetaData\Copyright\EntryInterface;
use ILIAS\MetaData\Copyright\NullEntry;
use ILIAS\Data\URI;
use ILIAS\MetaData\Copyright\CopyrightDataInterface;
use ILIAS\MetaData\Copyright\NullCopyrightData;
use ILIAS\MetaData\Copyright\RendererInterface;
use ILIAS\MetaData\Copyright\NullRenderer;
use ILIAS\MetaData\Settings\SettingsInterface;
use ILIAS\MetaData\Settings\NullSettings;

class CopyrightHandlerTest extends TestCase
{
    protected function getURI(string $link): URI
    {
        $url = $this->createMock(URI::class);
        $url->method('__toString')->willReturn($link);
        return $url;
    }

    protected function getCopyrightEntry(
        int $id,
        string $full_name,
        ?string $link,
        bool $default = false
    ): EntryInterface {
        $url = is_null($link) ? null : $this->getURI($link);

        return new class ($id, $full_name, $url, $default) extends NullEntry {
            public function __construct(
                protected int $id,
                protected string $full_name,
                protected ?URI $url,
                protected bool $default
            ) {
            }

            public function id(): int
            {
                return $this->id;
            }

            public function isDefault(): bool
            {
                return $this->default;
            }

            public function copyrightData(): CopyrightDataInterface
            {
                return new class ($this->full_name, $this->url) extends NullCopyrightData {
                    public function __construct(
                        protected string $full_name,
                        protected ?URI $url
                    ) {
                    }

                    public function fullName(): string
                    {
                        return $this->full_name;
                    }

                    public function link(): ?URI
                    {
                        return $this->url;
                    }
                };
            }
        };
    }

    protected function getCopyrightRepository(EntryInterface ...$entries): CopyrightRepository
    {
        return new class ($entries) extends NullRepository {
            public function __construct(protected array $entries)
            {
            }

            public function getAllEntries(): \Generator
            {
                yield from $this->entries;
            }

            public function getEntry(int $id): EntryInterface
            {
                foreach ($this->entries as $entry) {
                    if ($entry->id() === $id) {
                        return $entry;
                    }
                }
                return new NullEntry();
            }

            public function getDefaultEntry(): EntryInterface
            {
                foreach ($this->entries as $entry) {
                    if ($entry->isDefault()) {
                        return $entry;
                    }
                }
                return new NullEntry();
            }
        };
    }

    protected function getIdentifierHandler(): IdentifierHandler
    {
        return new class () extends NullHandler {
            public function isIdentifierValid(string $identifier): bool
            {
                return str_contains($identifier, 'valid_identifier_');
            }

            public function parseEntryIDFromIdentifier(string $identifier): int
            {
                return (int) str_replace('valid_identifier_', '', $identifier);
            }

            public function buildIdentifierFromEntryID(int $entry_id): string
            {
                return 'valid_identifier_' . $entry_id;
            }
        };
    }

    protected function getSettings(bool $selection_active): SettingsInterface
    {
        return new class ($selection_active) extends NullSettings {
            public function __construct(protected bool $selection_active)
            {
            }

            public function isCopyrightSelectionActive(): bool
            {
                return $this->selection_active;
            }
        };
    }

    public function testIsCopyrightSelectionActiveTrue(): void
    {
        $handler = new CopyrightHandler(
            $this->getCopyrightRepository(),
            $this->getIdentifierHandler(),
            $this->getSettings(true)
        );

        $this->assertTrue($handler->isCopyrightSelectionActive());
    }

    public function testIsCopyrightSelectionActiveFalse(): void
    {
        $handler = new CopyrightHandler(
            $this->getCopyrightRepository(),
            $this->getIdentifierHandler(),
            $this->getSettings(false)
        );

        $this->assertFalse($handler->isCopyrightSelectionActive());
    }

    public function testCopyrightAsString(): void
    {
        $entries = [
            $this->getCopyrightEntry(13, 'first entry', 'https://www.example1.com'),
            $this->getCopyrightEntry(55, 'second entry', 'http://www.example2.com'),
            $this->getCopyrightEntry(123, 'third entry', 'https://www.example3.com/something')
        ];
        $handler = new CopyrightHandler(
            $this->getCopyrightRepository(...$entries),
            $this->getIdentifierHandler(),
            $this->getSettings(true)
        );

        $this->assertSame(
            'http://www.example2.com',
            $handler->copyrightAsString('valid_identifier_55')
        );
    }

    public function testCopyrightAsStringInactiveCPSelection(): void
    {
        $entries = [
            $this->getCopyrightEntry(13, 'first entry', 'https://www.example1.com'),
            $this->getCopyrightEntry(55, 'second entry', 'http://www.example2.com'),
            $this->getCopyrightEntry(123, 'third entry', 'https://www.example3.com/something')
        ];
        $handler = new CopyrightHandler(
            $this->getCopyrightRepository(...$entries),
            $this->getIdentifierHandler(),
            $this->getSettings(false)
        );

        $this->assertSame(
            'valid_identifier_55',
            $handler->copyrightAsString('valid_identifier_55')
        );
    }

    public function testCopyrightAsStringInvalidAsIdentifier(): void
    {
        $entries = [
            $this->getCopyrightEntry(13, 'first entry', 'https://www.example1.com'),
            $this->getCopyrightEntry(55, 'second entry', 'http://www.example2.com'),
            $this->getCopyrightEntry(123, 'third entry', 'https://www.example3.com/something')
        ];
        $handler = new CopyrightHandler(
            $this->getCopyrightRepository(...$entries),
            $this->getIdentifierHandler(),
            $this->getSettings(true)
        );

        $this->assertSame(
            'something invalid',
            $handler->copyrightAsString('something invalid')
        );
    }

    public function testCopyrightAsStringEntryIdNotFound(): void
    {
        $entries = [
            $this->getCopyrightEntry(13, 'first entry', 'https://www.example1.com'),
            $this->getCopyrightEntry(55, 'second entry', 'http://www.example2.com'),
            $this->getCopyrightEntry(123, 'third entry', 'https://www.example3.com/something')
        ];
        $handler = new CopyrightHandler(
            $this->getCopyrightRepository(...$entries),
            $this->getIdentifierHandler(),
            $this->getSettings(true)
        );

        $this->assertSame(
            '',
            $handler->copyrightAsString('valid_identifier_678')
        );
    }

    public function testCopyrightForExportInvalidAsIdentifier(): void
    {
        $entries = [
            $this->getCopyrightEntry(13, 'first entry', 'https://www.example1.com'),
            $this->getCopyrightEntry(55, 'second entry', 'http://www.example2.com'),
            $this->getCopyrightEntry(123, 'third entry', 'https://www.example3.com/something')
        ];
        $handler = new CopyrightHandler(
            $this->getCopyrightRepository(...$entries),
            $this->getIdentifierHandler(),
            $this->getSettings(true)
        );

        $this->assertSame(
            'something invalid',
            $handler->copyrightForExport('something invalid')
        );
    }

    public function testCopyrightForExportEntryIdNotFound(): void
    {
        $entries = [
            $this->getCopyrightEntry(13, 'first entry', 'https://www.example1.com'),
            $this->getCopyrightEntry(55, 'second entry', 'http://www.example2.com'),
            $this->getCopyrightEntry(123, 'third entry', 'https://www.example3.com/something')
        ];
        $handler = new CopyrightHandler(
            $this->getCopyrightRepository(...$entries),
            $this->getIdentifierHandler(),
            $this->getSettings(true)
        );

        $this->assertSame(
            '',
            $handler->copyrightForExport('valid_identifier_678')
        );
    }

    public function testCopyrightForExportInactiveCPSelection(): void
    {
        $entries = [
            $this->getCopyrightEntry(13, 'first entry', 'https://www.example1.com'),
            $this->getCopyrightEntry(55, 'second entry', 'http://www.example2.com'),
            $this->getCopyrightEntry(123, 'third entry', 'https://www.example3.com/something')
        ];
        $handler = new CopyrightHandler(
            $this->getCopyrightRepository(...$entries),
            $this->getIdentifierHandler(),
            $this->getSettings(false)
        );

        $this->assertSame(
            'valid_identifier_55',
            $handler->copyrightForExport('valid_identifier_55')
        );
    }

    public function testCopyrightForExportEmpty(): void
    {
        $entries = [
            $this->getCopyrightEntry(13, 'first entry', 'https://www.example1.com'),
            $this->getCopyrightEntry(55, 'second entry', 'http://www.example2.com', true),
            $this->getCopyrightEntry(123, 'third entry', 'https://www.example3.com/something')
        ];
        $handler = new CopyrightHandler(
            $this->getCopyrightRepository(...$entries),
            $this->getIdentifierHandler(),
            $this->getSettings(true)
        );

        $this->assertSame(
            'http://www.example2.com',
            $handler->copyrightForExport('')
        );
    }

    public function testCopyrightForExportHasLink(): void
    {
        $entries = [
            $this->getCopyrightEntry(13, 'first entry', 'https://www.example1.com'),
            $this->getCopyrightEntry(55, '', 'http://www.example2.com'),
            $this->getCopyrightEntry(123, 'third entry', 'https://www.example3.com/something')
        ];
        $handler = new CopyrightHandler(
            $this->getCopyrightRepository(...$entries),
            $this->getIdentifierHandler(),
            $this->getSettings(true)
        );

        $this->assertSame(
            'http://www.example2.com',
            $handler->copyrightForExport('valid_identifier_55')
        );
    }

    public function testCopyrightForExportHasFullName(): void
    {
        $entries = [
            $this->getCopyrightEntry(13, 'first entry', 'https://www.example1.com'),
            $this->getCopyrightEntry(55, 'second entry', null),
            $this->getCopyrightEntry(123, 'third entry', 'https://www.example3.com/something')
        ];
        $handler = new CopyrightHandler(
            $this->getCopyrightRepository(...$entries),
            $this->getIdentifierHandler(),
            $this->getSettings(true)
        );

        $this->assertSame(
            'second entry',
            $handler->copyrightForExport('valid_identifier_55')
        );
    }

    public function testCopyrightForExportHasFullNameAndLink(): void
    {
        $entries = [
            $this->getCopyrightEntry(13, 'first entry', 'https://www.example1.com'),
            $this->getCopyrightEntry(55, 'second entry', 'http://www.example2.com'),
            $this->getCopyrightEntry(123, 'third entry', 'https://www.example3.com/something')
        ];
        $handler = new CopyrightHandler(
            $this->getCopyrightRepository(...$entries),
            $this->getIdentifierHandler(),
            $this->getSettings(true)
        );

        $this->assertSame(
            'http://www.example2.com',
            $handler->copyrightForExport('valid_identifier_55')
        );
    }

    public function testCopyrightForExportHasNoFullNameOrLink(): void
    {
        $entries = [
            $this->getCopyrightEntry(13, 'first entry', 'https://www.example1.com'),
            $this->getCopyrightEntry(55, '', null),
            $this->getCopyrightEntry(123, 'third entry', 'https://www.example3.com/something')
        ];
        $handler = new CopyrightHandler(
            $this->getCopyrightRepository(...$entries),
            $this->getIdentifierHandler(),
            $this->getSettings(true)
        );

        $this->assertSame(
            '',
            $handler->copyrightForExport('valid_identifier_55')
        );
    }

    public function testCopyrightFromExportNoMatches(): void
    {
        $entries = [
            $this->getCopyrightEntry(13, 'first entry', null),
            $this->getCopyrightEntry(55, 'second entry', 'http://www.example2.com'),
            $this->getCopyrightEntry(123, 'third entry', 'https://www.example3.com/something')
        ];
        $handler = new CopyrightHandler(
            $this->getCopyrightRepository(...$entries),
            $this->getIdentifierHandler(),
            $this->getSettings(true)
        );

        $this->assertSame(
            'just some text',
            $handler->copyrightFromExport('just some text')
        );
    }

    public function testCopyrightFromExportURLWithNoMatchesShouldBeUnchanged(): void
    {
        $entries = [
            $this->getCopyrightEntry(13, 'first entry', null),
            $this->getCopyrightEntry(55, 'second entry', 'http://www.example2.com'),
            $this->getCopyrightEntry(123, 'third entry', 'https://www.example3.com/something')
        ];
        $handler = new CopyrightHandler(
            $this->getCopyrightRepository(...$entries),
            $this->getIdentifierHandler(),
            $this->getSettings(true)
        );

        $this->assertSame(
            'https://www.nonmatching.com',
            $handler->copyrightFromExport('https://www.nonmatching.com')
        );
        $this->assertSame(
            'http://www.nonmatching.com',
            $handler->copyrightFromExport('http://www.nonmatching.com')
        );
    }

    public function testCopyrightFromExportNoMatchesContainsFullName(): void
    {
        $entries = [
            $this->getCopyrightEntry(13, 'first entry', null),
            $this->getCopyrightEntry(55, 'second entry', 'http://www.example2.com'),
            $this->getCopyrightEntry(123, 'third entry', 'https://www.example3.com/something')
        ];
        $handler = new CopyrightHandler(
            $this->getCopyrightRepository(...$entries),
            $this->getIdentifierHandler(),
            $this->getSettings(true)
        );

        $this->assertSame(
            'just some text which contains first entry',
            $handler->copyrightFromExport('just some text which contains first entry')
        );
    }

    public function testCopyrightFromExportInactiveCPSelection(): void
    {
        $entries = [
            $this->getCopyrightEntry(13, 'first entry', null),
            $this->getCopyrightEntry(55, 'second entry', 'http://www.example2.com'),
            $this->getCopyrightEntry(123, 'third entry', 'https://www.example3.com/something')
        ];
        $handler = new CopyrightHandler(
            $this->getCopyrightRepository(...$entries),
            $this->getIdentifierHandler(),
            $this->getSettings(false)
        );

        $this->assertSame(
            'some text containing http://www.example2.com',
            $handler->copyrightFromExport('some text containing http://www.example2.com')
        );
    }

    public function testCopyrightFromExportMatchesByFullName(): void
    {
        $entries = [
            $this->getCopyrightEntry(13, 'first entry', null),
            $this->getCopyrightEntry(55, 'second entry', 'http://www.example2.com'),
            $this->getCopyrightEntry(123, 'third entry', 'https://www.example3.com/something')
        ];
        $handler = new CopyrightHandler(
            $this->getCopyrightRepository(...$entries),
            $this->getIdentifierHandler(),
            $this->getSettings(true)
        );

        $this->assertSame(
            'valid_identifier_13',
            $handler->copyrightFromExport('first entry')
        );
        $this->assertSame(
            'valid_identifier_55',
            $handler->copyrightFromExport('second entry')
        );
    }

    public function testCopyrightFromExportMultipleMatchesByFullName(): void
    {
        $entries = [
            $this->getCopyrightEntry(13, 'first entry', null),
            $this->getCopyrightEntry(55, 'second entry', 'http://www.example2.com'),
            $this->getCopyrightEntry(123, 'second entry', 'https://www.example3.com/something')
        ];
        $handler = new CopyrightHandler(
            $this->getCopyrightRepository(...$entries),
            $this->getIdentifierHandler(),
            $this->getSettings(true)
        );

        $this->assertSame(
            'valid_identifier_55',
            $handler->copyrightFromExport('second entry')
        );
    }

    public function testCopyrightFromExportMatchesByLink(): void
    {
        $entries = [
            $this->getCopyrightEntry(13, 'first entry', null),
            $this->getCopyrightEntry(55, 'second entry', 'http://www.example2.com'),
            $this->getCopyrightEntry(123, 'third entry', 'https://www.example3.com/something')
        ];
        $handler = new CopyrightHandler(
            $this->getCopyrightRepository(...$entries),
            $this->getIdentifierHandler(),
            $this->getSettings(true)
        );

        $this->assertSame(
            'valid_identifier_55',
            $handler->copyrightFromExport('some text containing http://www.example2.com')
        );
    }

    public function testCopyrightFromExportMultipleMatchesByLink(): void
    {
        $entries = [
            $this->getCopyrightEntry(13, 'first entry', null),
            $this->getCopyrightEntry(55, 'second entry', 'https://www.example3.com/something'),
            $this->getCopyrightEntry(123, 'third entry', 'https://www.example3.com/something')
        ];
        $handler = new CopyrightHandler(
            $this->getCopyrightRepository(...$entries),
            $this->getIdentifierHandler(),
            $this->getSettings(true)
        );

        $this->assertSame(
            'valid_identifier_55',
            $handler->copyrightFromExport('some text containing https://www.example3.com/something')
        );
    }

    public function testCopyrightFromExportMatchesByLinkButDifferentScheme(): void
    {
        $entries = [
            $this->getCopyrightEntry(13, 'first entry', null),
            $this->getCopyrightEntry(55, 'second entry', 'http://www.example2.com'),
            $this->getCopyrightEntry(123, 'third entry', 'https://www.example3.com/something')
        ];
        $handler = new CopyrightHandler(
            $this->getCopyrightRepository(...$entries),
            $this->getIdentifierHandler(),
            $this->getSettings(true)
        );

        $this->assertSame(
            'valid_identifier_55',
            $handler->copyrightFromExport('some text containing https://www.example2.com')
        );
    }
}
