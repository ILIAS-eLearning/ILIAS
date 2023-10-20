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

class DataTest extends TestCase
{
    protected function getElementData(Type $type, string $value): ElementData
    {
        return new class ($type, $value) extends NullData {
            protected Type $type;
            protected string $value;

            public function __construct(Type $type, string $value)
            {
                $this->type = $type;
                $this->value = $value;
            }

            public function type(): Type
            {
                return $this->type;
            }

            public function value(): string
            {
                return $this->value;
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

        return new Data($util, $helper);
    }

    public function testVocabularyValue(): void
    {
        $data = $this->getData();
        $this->assertSame(
            'translated meta_some_key',
            $data->vocabularyValue('SomeKey')
        );
        $this->assertSame(
            'translated meta_subjectmatterexpert',
            $data->vocabularyValue('subjectMatterExpert')
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
            'translated meta_some_key',
            $data->dataValue($this->getElementData(Type::VOCAB_VALUE, 'SomeKey'))
        );
        $this->assertSame(
            'translated meta_subjectmatterexpert',
            $data->dataValue($this->getElementData(Type::VOCAB_VALUE, 'subjectMatterExpert'))
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
            $data->dataValue($this->getElementData(Type::STRING, 'This should just go through.'))
        );
    }
}
