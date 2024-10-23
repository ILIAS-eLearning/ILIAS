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
use ILIAS\MetaData\Settings\SettingsInterface;
use ILIAS\MetaData\Settings\NullSettings;
use ILIAS\MetaData\Paths\FactoryInterface as PathFactory;
use ILIAS\MetaData\Paths\NullFactory as NullPathFactory;
use ILIAS\MetaData\Paths\BuilderInterface;
use ILIAS\MetaData\Paths\NullBuilder;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Paths\NullPath;
use ILIAS\MetaData\Copyright\RepositoryInterface as CopyrightRepository;
use ILIAS\MetaData\Copyright\NullRepository;
use ILIAS\MetaData\Copyright\EntryInterface;
use ILIAS\MetaData\Copyright\NullEntry;
use ILIAS\MetaData\Copyright\Identifiers\HandlerInterface as IdentifierHandler;
use ILIAS\MetaData\Copyright\Identifiers\NullHandler;
use ILIAS\MetaData\Search\Clauses\FactoryInterface as SearchClauseFactory;
use ILIAS\MetaData\Search\Clauses\NullFactory as NullSearchClauseFactory;
use ILIAS\MetaData\Search\Clauses\Mode;
use ILIAS\MetaData\Search\Clauses\ClauseInterface;
use ILIAS\MetaData\Search\Clauses\Operator;
use ILIAS\MetaData\Search\Clauses\NullClause;
use ILIAS\MetaData\Copyright\NullRenderer;
use ILIAS\MetaData\Services\Reader\ReaderInterface;
use ILIAS\MetaData\Services\Reader\NullReader;
use ILIAS\MetaData\Elements\Data\DataInterface;
use ILIAS\MetaData\Elements\Data\NullData;
use ILIAS\MetaData\Services\Manipulator\ManipulatorInterface;
use ILIAS\MetaData\Services\Manipulator\NullManipulator;

class CopyrightHelperTest extends TestCase
{
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

    protected function getPathFactory(): PathFactory
    {
        return new class () extends NullPathFactory {
            public function custom(): BuilderInterface
            {
                return new class () extends NullBuilder {
                    protected string $path = '';

                    public function withNextStep(string $name, bool $add_as_first = false): BuilderInterface
                    {
                        $clone = clone $this;
                        $clone->path .= '>' . $name;
                        return $clone;
                    }

                    public function get(): PathInterface
                    {
                        return new class ($this->path) extends NullPath {
                            public function __construct(protected string $path)
                            {
                            }

                            public function toString(): string
                            {
                                return $this->path;
                            }
                        };
                    }
                };
            }
        };
    }

    protected function getCopyrightEntry(int $id): EntryInterface
    {
        return new class ($id) extends NullEntry {
            public function __construct(protected int $id)
            {
            }

            public function id(): int
            {
                return $this->id;
            }
        };
    }

    /**
     * @param int[] $entry_ids
     * @param int[] $outdated_entry_ids
     */
    protected function getCopyrightRepository(
        array $entry_ids,
        array $outdated_entry_ids,
        int $default_entry_id
    ): CopyrightRepository {
        $entries = [];
        foreach ($entry_ids as $id) {
            $entries[] = $this->getCopyrightEntry($id);
        }

        return new class ($entries, $outdated_entry_ids, $default_entry_id) extends NullRepository {
            public function __construct(
                protected array $entries,
                protected array $outdated_entry_ids,
                protected int $default_entry_id
            ) {
            }

            public function getAllEntries(): \Generator
            {
                yield from $this->entries;
            }

            public function getActiveEntries(): \Generator
            {
                foreach ($this->entries as $entry) {
                    if (!in_array($entry->id(), $this->outdated_entry_ids)) {
                        yield $entry;
                    }
                }
            }

            public function getDefaultEntry(): EntryInterface
            {
                foreach ($this->entries as $entry) {
                    if ($entry->id() === $this->default_entry_id) {
                        return $entry;
                    }
                }
                throw new \ilMDServicesException('Default entry id not found.');
            }

            public function getEntry(int $id): EntryInterface
            {
                foreach ($this->entries as $entry) {
                    if ($entry->id() === $id) {
                        return $entry;
                    }
                }
                throw new \ilMDServicesException('Entry id not found.');
            }
        };
    }

    protected function getIdentifierHandler(): IdentifierHandler
    {
        return new class () extends NullHandler {
            public function isIdentifierValid(string $identifier): bool
            {
                return str_contains($identifier, 'valid_');
            }

            public function parseEntryIDFromIdentifier(string $identifier): int
            {
                if ($this->isIdentifierValid($identifier)) {
                    return (int) str_replace('valid_', '', $identifier);
                }
                return 0;
            }
        };
    }

    protected function getClauseFactory(): SearchClauseFactory
    {
        return new class () extends NullSearchClauseFactory {
            public function getBasicClause(
                PathInterface $path,
                Mode $mode,
                string $value,
                bool $is_mode_negated = false
            ): ClauseInterface {
                $search_data = '"' . $path->toString() . '" ' .
                    ($is_mode_negated ? 'not ' : '') .
                    $mode->value . ' "' . $value . '"';
                return new class ($search_data) extends NullClause {
                    public function __construct(public string $exposed_search_data)
                    {
                    }
                };
            }

            public function getJoinedClauses(
                Operator $operator,
                ClauseInterface $first_clause,
                ClauseInterface ...$further_clauses
            ): ClauseInterface {
                $clauses_data = [];
                foreach ([$first_clause, ...$further_clauses] as $clause) {
                    $clauses_data[] = $clause->exposed_search_data;
                }
                $search_data = implode(' ' . $operator->value . ' ', $clauses_data);
                return new class ($search_data) extends NullClause {
                    public function __construct(public string $exposed_search_data)
                    {
                    }
                };
            }
        };
    }

    protected function getReader(string $raw_copyright): ReaderInterface
    {
        return new class ($raw_copyright) extends NullReader {
            public function __construct(protected string $raw_copyright)
            {
            }

            public function firstData(PathInterface $path): DataInterface
            {
                if ($path->toString() !== '>rights>description>string') {
                    throw new \ilMDServicesException('Wrong Path!');
                }
                return new class ($this->raw_copyright) extends NullData {
                    public function __construct(protected string $raw_copyright)
                    {
                    }

                    public function value(): string
                    {
                        return $this->raw_copyright;
                    }
                };
            }
        };
    }

    protected function getManipulator(): ManipulatorInterface
    {
        return new class () extends NullManipulator {
            public array $prepared_creates_or_updates = [];

            public function prepareCreateOrUpdate(PathInterface $path, string ...$values): ManipulatorInterface
            {
                $clone = clone $this;
                $clone->prepared_creates_or_updates[] = [
                    'path' => $path->toString(),
                    'values' => $values,
                ];
                return $clone;
            }
        };
    }

    protected function getCopyrightHelper(
        SettingsInterface $settings,
        CopyrightRepository $copyright_repo
    ): CopyrightHelper {
        return new class (
            $settings,
            $this->getPathFactory(),
            $copyright_repo,
            $this->getIdentifierHandler(),
            new NullRenderer(),
            $this->getClauseFactory()
        ) extends CopyrightHelper {
            protected function getCopyrightEntryWrapper(EntryInterface $entry): CopyrightInterface
            {
                return new class ((string) $entry->id()) extends NullCopyright {
                    public function __construct(public string $exposed_id)
                    {
                    }
                };
            }

            protected function getNullCopyrightEntryWrapper(): CopyrightInterface
            {
                return new class ('null') extends NullCopyright {
                    public function __construct(public string $exposed_id)
                    {
                    }
                };
            }
        };
    }

    public function testIsCopyrightSelectionActiveTrue(): void
    {
        $helper = $this->getCopyrightHelper(
            $this->getSettings(true),
            $this->getCopyrightRepository([], [], 0)
        );

        $this->assertTrue($helper->isCopyrightSelectionActive());
    }

    public function testIsCopyrightSelectionActiveFalse(): void
    {
        $helper = $this->getCopyrightHelper(
            $this->getSettings(false),
            $this->getCopyrightRepository([], [], 0)
        );

        $this->assertFalse($helper->isCopyrightSelectionActive());
    }

    public function testHasPresetCopyrightTrue(): void
    {
        $helper = $this->getCopyrightHelper(
            $this->getSettings(true),
            $this->getCopyrightRepository([13, 77, 932, 5], [77], 13)
        );
        $reader = $this->getReader('valid_5');

        $this->assertTrue($helper->hasPresetCopyright($reader));
    }

    public function testHasPresetCopyrightTrueEmpty(): void
    {
        $helper = $this->getCopyrightHelper(
            $this->getSettings(true),
            $this->getCopyrightRepository([13, 77, 932, 5], [77], 13)
        );
        $reader = $this->getReader('');

        $this->assertTrue($helper->hasPresetCopyright($reader));
    }

    public function testHasPresetCopyrightFalse(): void
    {
        $helper = $this->getCopyrightHelper(
            $this->getSettings(true),
            $this->getCopyrightRepository([13, 77, 932, 5], [77], 13)
        );
        $reader = $this->getReader('something else');

        $this->assertFalse($helper->hasPresetCopyright($reader));
    }

    public function testHasPresetCopyrightFalseInactiveCPSelection(): void
    {
        $helper = $this->getCopyrightHelper(
            $this->getSettings(false),
            $this->getCopyrightRepository([13, 77, 932, 5], [77], 13)
        );
        $reader = $this->getReader('valid_5');

        $this->assertFalse($helper->hasPresetCopyright($reader));
    }

    public function testReadPresetCopyright(): void
    {
        $helper = $this->getCopyrightHelper(
            $this->getSettings(true),
            $this->getCopyrightRepository([13, 77, 932, 5], [77], 13)
        );
        $reader = $this->getReader('valid_5');

        $this->assertSame('5', $helper->readPresetCopyright($reader)->exposed_id);
    }

    public function testReadPresetCopyrightInactiveCPSelection(): void
    {
        $helper = $this->getCopyrightHelper(
            $this->getSettings(false),
            $this->getCopyrightRepository([13, 77, 932, 5], [77], 13)
        );
        $reader = $this->getReader('valid_5');

        $this->assertSame('null', $helper->readPresetCopyright($reader)->exposed_id);
    }

    public function testReadPresetCopyrightInvalidIdentifier(): void
    {
        $helper = $this->getCopyrightHelper(
            $this->getSettings(true),
            $this->getCopyrightRepository([13, 77, 932, 5], [77], 13)
        );
        $reader = $this->getReader('something else');

        $this->assertSame('null', $helper->readPresetCopyright($reader)->exposed_id);
    }

    public function testReadPresetCopyrightEmptyRawCopyright(): void
    {
        $helper = $this->getCopyrightHelper(
            $this->getSettings(true),
            $this->getCopyrightRepository([13, 77, 932, 5], [77], 13)
        );
        $reader = $this->getReader('');

        $this->assertSame('13', $helper->readPresetCopyright($reader)->exposed_id);
    }

    public function testReadCustomCopyright(): void
    {
        $helper = $this->getCopyrightHelper(
            $this->getSettings(true),
            $this->getCopyrightRepository([13, 77, 932, 5], [77], 13)
        );
        $reader = $this->getReader('custom info about the copyright');

        $this->assertSame(
            'custom info about the copyright',
            $helper->readCustomCopyright($reader)
        );
    }

    public function testReadCustomCopyrightValidIdentifier(): void
    {
        $helper = $this->getCopyrightHelper(
            $this->getSettings(true),
            $this->getCopyrightRepository([13, 77, 932, 5], [77], 13)
        );
        $reader = $this->getReader('valid_5');

        $this->assertSame(
            '',
            $helper->readCustomCopyright($reader)
        );
    }

    public function testReadCustomCopyrightValidIdentifierButInactiveCPSelection(): void
    {
        $helper = $this->getCopyrightHelper(
            $this->getSettings(false),
            $this->getCopyrightRepository([13, 77, 932, 5], [77], 13)
        );
        $reader = $this->getReader('valid_5');

        $this->assertSame(
            'valid_5',
            $helper->readCustomCopyright($reader)
        );
    }

    public function testPrepareCreateOrUpdateOfCopyrightFromPreset(): void
    {
        $helper = $this->getCopyrightHelper(
            $this->getSettings(false),
            $this->getCopyrightRepository([13, 77, 932, 5], [77], 13)
        );
        $manipulator = $this->getManipulator();

        $manipulator = $helper->prepareCreateOrUpdateOfCopyrightFromPreset(
            $manipulator,
            'valid_5'
        );

        $this->assertSame(
            [['path' => '>rights>description>string', 'values' => ['valid_5']]],
            $manipulator->prepared_creates_or_updates
        );
    }

    public function testPrepareCreateOrUpdateOfCustomCopyright(): void
    {
        $helper = $this->getCopyrightHelper(
            $this->getSettings(false),
            $this->getCopyrightRepository([13, 77, 932, 5], [77], 13)
        );
        $manipulator = $this->getManipulator();

        $manipulator = $helper->prepareCreateOrUpdateOfCustomCopyright(
            $manipulator,
            'custom info about the copyright'
        );

        $this->assertSame(
            [['path' => '>rights>description>string', 'values' => ['custom info about the copyright']]],
            $manipulator->prepared_creates_or_updates
        );
    }

    public function testGetAllCopyrightPresets(): void
    {
        $helper = $this->getCopyrightHelper(
            $this->getSettings(true),
            $this->getCopyrightRepository([13, 77, 932, 5], [77], 13)
        );

        $presets = $helper->getAllCopyrightPresets();

        $this->assertSame('13', $presets->current()->exposed_id);
        $presets->next();
        $this->assertSame('77', $presets->current()->exposed_id);
        $presets->next();
        $this->assertSame('932', $presets->current()->exposed_id);
        $presets->next();
        $this->assertSame('5', $presets->current()->exposed_id);
        $presets->next();
        $this->assertNull($presets->current());
    }

    public function testGetAllCopyrightPresetsInactiveCPSelection(): void
    {
        $helper = $this->getCopyrightHelper(
            $this->getSettings(false),
            $this->getCopyrightRepository([13, 77, 932, 5], [77], 13)
        );

        $presets = $helper->getAllCopyrightPresets();

        $this->assertNull($presets->current());
    }

    public function testGetNonOutdatedCopyrightPresets(): void
    {
        $helper = $this->getCopyrightHelper(
            $this->getSettings(true),
            $this->getCopyrightRepository([13, 77, 932, 5], [77], 13)
        );

        $presets = $helper->getNonOutdatedCopyrightPresets();

        $this->assertSame('13', $presets->current()->exposed_id);
        $presets->next();
        $this->assertSame('932', $presets->current()->exposed_id);
        $presets->next();
        $this->assertSame('5', $presets->current()->exposed_id);
        $presets->next();
        $this->assertNull($presets->current());
    }

    public function testGetNonOutdatedCopyrightPresetsInactiveCPSelection(): void
    {
        $helper = $this->getCopyrightHelper(
            $this->getSettings(false),
            $this->getCopyrightRepository([13, 77, 932, 5], [77], 13)
        );

        $presets = $helper->getNonOutdatedCopyrightPresets();

        $this->assertNull($presets->current());
    }

    public function testGetCopyrightSearchClause(): void
    {
        $helper = $this->getCopyrightHelper(
            $this->getSettings(true),
            $this->getCopyrightRepository([13, 77, 932, 5], [77], 13)
        );

        $clause = $helper->getCopyrightSearchClause('valid_5');

        $this->assertSame(
            '">rights>description>string" equals "valid_5"',
            $clause->exposed_search_data
        );
    }

    public function testGetCopyrightSearchClauseMultipleCPIdentifiers(): void
    {
        $helper = $this->getCopyrightHelper(
            $this->getSettings(true),
            $this->getCopyrightRepository([13, 77, 932, 5], [77], 13)
        );

        $clause = $helper->getCopyrightSearchClause(
            'valid_5',
            'valid_77',
            'something else'
        );

        $this->assertSame(
            '">rights>description>string" equals "valid_5" or ' .
            '">rights>description>string" equals "valid_77" or ' .
            '">rights>description>string" equals "something else"',
            $clause->exposed_search_data
        );
    }

    public function testGetCopyrightSearchClauseDefaultCPIdentifier(): void
    {
        $helper = $this->getCopyrightHelper(
            $this->getSettings(true),
            $this->getCopyrightRepository([13, 77, 932, 5], [77], 13)
        );

        $clause = $helper->getCopyrightSearchClause('valid_13');

        $this->assertSame(
            '">rights>description>string" equals "valid_13" or ' .
            '">rights>description>string" equals ""',
            $clause->exposed_search_data
        );
    }

    public function testGetCopyrightSearchClauseDefaultCPIdentifierButInactiveCPSelection(): void
    {
        $helper = $this->getCopyrightHelper(
            $this->getSettings(false),
            $this->getCopyrightRepository([13, 77, 932, 5], [77], 13)
        );

        $clause = $helper->getCopyrightSearchClause('valid_13');

        $this->assertSame(
            '">rights>description>string" equals "valid_13"',
            $clause->exposed_search_data
        );
    }
}
