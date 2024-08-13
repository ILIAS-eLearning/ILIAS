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

namespace ILIAS\MetaData\Presentation;

use PHPUnit\Framework\TestCase;
use ILIAS\Data\DateFormat\DateFormat;
use ILIAS\MetaData\Elements\Data\DataInterface as ElementData;
use ILIAS\MetaData\Elements\Data\Type;
use ILIAS\MetaData\Elements\Data\NullData;
use ILIAS\MetaData\DataHelper\NullDataHelper;
use ILIAS\MetaData\Vocabularies\Dispatch\Presentation\NullPresentation as NullVocabulariesPresentation;
use ILIAS\MetaData\Presentation\UtilitiesInterface as PresentationUtilities;
use ILIAS\MetaData\Vocabularies\Slots\Identifier as SlotIdentifier;
use ILIAS\MetaData\Vocabularies\Dispatch\Presentation\NullLabelledValue;
use ILIAS\MetaData\Vocabularies\Slots\Identifier;

class DataTest extends TestCase
{
    protected function getElementData(
        Type $type,
        string $value,
        SlotIdentifier $vocab_slot = SlotIdentifier::NULL
    ): ElementData {
        return new class ($type, $value, $vocab_slot) extends NullData {
            public function __construct(
                protected Type $type,
                protected string $value,
                protected SlotIdentifier $vocab_slot
            ) {
                $this->type = $type;
                $this->value = $value;
                $this->vocab_slot = $vocab_slot;
            }

            public function type(): Type
            {
                return $this->type;
            }

            public function value(): string
            {
                return $this->value;
            }

            public function vocabularySlot(): SlotIdentifier
            {
                return $this->vocab_slot;
            }
        };
    }

    protected function getData(): Data
    {
        $format = $this->createMock(DateFormat::class);
        $format->method('applyTo')->willReturnCallback(function (\DateTimeImmutable $arg) {
            return $arg->format('d:m:Y');
        });

        $util = new class ($format) extends NullUtilities {
            protected DateFormat $format;

            public function __construct(DateFormat $format)
            {
                $this->format = $format;
            }

            public function getUserDateFormat(): DateFormat
            {
                return $this->format;
            }

            public function txt(string $key): string
            {
                return 'translated ' . $key;
            }
        };

        $helper = new class () extends NullDataHelper {
            public function durationToIterator(string $duration): \Generator
            {
                foreach (explode(':', $duration) as $v) {
                    if ($v === '') {
                        yield null;
                    } else {
                        yield $v;
                    }
                }
            }

            public function datetimeToObject(string $datetime): \DateTimeImmutable
            {
                return new \DateTimeImmutable($datetime);
            }
        };

        $vocab_presentation = new class () extends NullVocabulariesPresentation {
            public function presentableLabels(
                PresentationUtilities $presentation_utilities,
                SlotIdentifier $slot,
                bool $with_unknown_vocab_flag,
                string ...$values
            ): \Generator {
                foreach ($values as $value) {
                    yield new class ($value, $slot, $with_unknown_vocab_flag) extends NullLabelledValue {
                        public function __construct(
                            protected string $value,
                            protected SlotIdentifier $slot,
                            protected bool $with_unknown_vocab_flag
                        ) {
                        }

                        public function value(): string
                        {
                            return $this->value;
                        }

                        public function label(): string
                        {
                            return 'vocab ' . $this->slot->value . ' ' .
                                $this->value . ($this->with_unknown_vocab_flag ? ' flagged' : '');
                        }
                    };
                }
            }
        };

        return new Data($util, $helper, $vocab_presentation);
    }

    public function testVocabularyValue(): void
    {
        $data = $this->getData();
        $this->assertSame(
            'vocab rights_cost SomeKey',
            $data->vocabularyValue('SomeKey', SlotIdentifier::RIGHTS_COST),
        );
    }

    public function testLanguage(): void
    {
        $data = $this->getData();
        $this->assertSame(
            'translated meta_l_key',
            $data->language('key')
        );
    }

    public function testDatetime(): void
    {
        $data = $this->getData();
        $this->assertSame(
            '31:12:2012',
            $data->datetime('2012-12-31')
        );
    }

    public function testDuration(): void
    {
        $data = $this->getData();
        $this->assertSame(
            '89 translated years, 0 translated months, 1 translated second',
            $data->duration('89:0::::1')
        );
    }

    public function testDataValue(): void
    {
        $data = $this->getData();
        $this->assertSame(
            'vocab rights_cost SomeKey',
            $data->dataValue($this->getElementData(
                Type::VOCAB_VALUE,
                'SomeKey',
                SlotIdentifier::RIGHTS_COST
            ))
        );
        $this->assertSame(
            'vocab rights_cost SomeKey',
            $data->dataValue($this->getElementData(
                Type::STRING,
                'SomeKey',
                SlotIdentifier::RIGHTS_COST
            ))
        );
        $this->assertSame(
            'translated meta_l_key',
            $data->dataValue($this->getElementData(Type::LANG, 'key'))
        );
        $this->assertSame(
            '31:12:2012',
            $data->dataValue($this->getElementData(Type::DATETIME, '2012-12-31'))
        );
        $this->assertSame(
            '89 translated years, 5 translated months, 1 translated second',
            $data->dataValue($this->getElementData(Type::DURATION, '89:5::::1'))
        );
        $this->assertSame(
            'This should just go through.',
            $data->dataValue($this->getElementData(Type::VOCAB_SOURCE, 'This should just go through.'))
        );
    }
}
