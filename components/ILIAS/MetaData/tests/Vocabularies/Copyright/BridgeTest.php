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

namespace ILIAS\MetaData\Vocabularies\Copyright;

use PHPUnit\Framework\TestCase;
use ILIAS\MetaData\Settings\SettingsInterface;
use ILIAS\MetaData\Settings\NullSettings;
use ILIAS\MetaData\Copyright\RepositoryInterface as CopyrightRepository;
use ILIAS\MetaData\Copyright\NullRepository;
use ILIAS\MetaData\Copyright\EntryInterface;
use ILIAS\MetaData\Copyright\NullEntry;
use ILIAS\MetaData\Copyright\Identifiers\HandlerInterface as IdentifierHandler;
use ILIAS\MetaData\Copyright\Identifiers\NullHandler;
use ILIAS\MetaData\Vocabularies\Factory\FactoryInterface;
use ILIAS\MetaData\Vocabularies\Factory\NullFactory;
use ILIAS\MetaData\Vocabularies\Factory\BuilderInterface;
use ILIAS\MetaData\Vocabularies\NullVocabulary;
use ILIAS\MetaData\Vocabularies\Factory\NullBuilder;
use ILIAS\MetaData\Vocabularies\VocabularyInterface;
use ILIAS\MetaData\Vocabularies\Slots\Identifier as SlotIdentifier;
use ILIAS\MetaData\Vocabularies\Slots\Identifier;

class BridgeTest extends TestCase
{
    protected function getVocabFactory(): FactoryInterface
    {
        return new class () extends NullFactory {
            public function copyright(string ...$values): BuilderInterface
            {
                return new class ($values) extends NullBuilder {
                    public function __construct(protected array $values)
                    {
                    }

                    public function get(): VocabularyInterface
                    {
                        return new class ($this->values) extends NullVocabulary {
                            public function __construct(protected array $values)
                            {
                            }

                            public function values(): \Generator
                            {
                                yield from $this->values;
                            }
                        };
                    }
                };
            }
        };
    }

    protected function getSettings(bool $cp_selection_active = true): SettingsInterface
    {
        return new class ($cp_selection_active) extends NullSettings {
            public function __construct(protected bool $cp_selection_active)
            {
            }

            public function isCopyrightSelectionActive(): bool
            {
                return $this->cp_selection_active;
            }
        };
    }

    protected function getCopyright(int $id, string $title): EntryInterface
    {
        return new class ($id, $title) extends NullEntry {
            public function __construct(
                protected int $id,
                protected string $title
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
        };
    }

    protected function getCopyrightRepository(): CopyrightRepository
    {
        $copyrights = [];
        $copyrights[] = $this->getCopyright(1, 'title cp 1');
        $copyrights[] = $this->getCopyright(2, 'title cp 2');
        $copyrights[] = $this->getCopyright(3, 'title cp 3');

        return new class ($copyrights) extends NullRepository {
            public function __construct(protected array $copyrights)
            {
            }

            public function getAllEntries(): \Generator
            {
                yield from $this->copyrights;
            }
        };
    }

    protected function getIdentifierHandler(): IdentifierHandler
    {
        return new class () extends NullHandler {
            public function buildIdentifierFromEntryID(int $entry_id): string
            {
                return 'id_' . $entry_id;
            }
        };
    }

    public function testVocabularyWrongSlot(): void
    {
        $bridge = new Bridge(
            $this->getVocabFactory(),
            $this->getSettings(),
            $this->getCopyrightRepository(),
            $this->getIdentifierHandler()
        );

        $vocab = $bridge->vocabulary(SlotIdentifier::CLASSIFICATION_PURPOSE);

        $this->assertNull($vocab);
    }

    public function testVocabularySelectionDisabled(): void
    {
        $bridge = new Bridge(
            $this->getVocabFactory(),
            $this->getSettings(false),
            $this->getCopyrightRepository(),
            $this->getIdentifierHandler()
        );

        $vocab = $bridge->vocabulary(SlotIdentifier::RIGHTS_DESCRIPTION);

        $this->assertNull($vocab);
    }

    public function testVocabulary(): void
    {
        $bridge = new Bridge(
            $this->getVocabFactory(),
            $this->getSettings(),
            $this->getCopyrightRepository(),
            $this->getIdentifierHandler()
        );

        $vocab = $bridge->vocabulary(SlotIdentifier::RIGHTS_DESCRIPTION);

        $this->assertNotNull($vocab);
        $this->assertSame(
            ['id_1', 'id_2', 'id_3'],
            iterator_to_array($vocab->values())
        );
    }

    public function testLabelsForValuesWrongSlot(): void
    {
        $bridge = new Bridge(
            $this->getVocabFactory(),
            $this->getSettings(),
            $this->getCopyrightRepository(),
            $this->getIdentifierHandler()
        );

        $labelled_values = $bridge->labelsForValues(
            SlotIdentifier::CLASSIFICATION_PURPOSE,
            'id_1',
            'id_3',
            'something'
        );

        $this->assertNull($labelled_values->current());
    }

    public function testLabelsForValuesSelectionDisabled(): void
    {
        $bridge = new Bridge(
            $this->getVocabFactory(),
            $this->getSettings(false),
            $this->getCopyrightRepository(),
            $this->getIdentifierHandler()
        );

        $labelled_values = $bridge->labelsForValues(
            SlotIdentifier::RIGHTS_DESCRIPTION,
            'id_1',
            'id_3',
            'something'
        );

        $this->assertNull($labelled_values->current());
    }

    public function testLabelsForValues(): void
    {
        $bridge = new Bridge(
            $this->getVocabFactory(),
            $this->getSettings(),
            $this->getCopyrightRepository(),
            $this->getIdentifierHandler()
        );

        $labelled_values = $bridge->labelsForValues(
            SlotIdentifier::RIGHTS_DESCRIPTION,
            'id_1',
            'id_3',
            'something'
        );

        $label_1 = $labelled_values->current();
        $this->assertSame('id_1', $label_1->value());
        $this->assertSame('title cp 1', $label_1->label());
        $labelled_values->next();
        $label_3 = $labelled_values->current();
        $this->assertSame('id_3', $label_3->value());
        $this->assertSame('title cp 3', $label_3->label());
        $labelled_values->next();
        $this->assertNull($labelled_values->current());
    }
}
