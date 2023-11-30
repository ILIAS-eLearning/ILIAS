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

namespace ILIAS\MetaData\Services\DataHelper;

use PHPUnit\Framework\TestCase;
use ILIAS\MetaData\Services\DataHelper\DataHelper;
use ILIAS\MetaData\DataHelper\NullDataHelper;
use ILIAS\MetaData\Presentation\NullData;
use ILIAS\MetaData\Elements\Data\DataInterface as ElementsDataInterface;
use ILIAS\MetaData\Elements\Data\NullData as NullElementsData;

class DataHelperTest extends TestCase
{
    protected function getData(string $value): ElementsDataInterface
    {
        return new class ($value) extends NullElementsData {
            public function __construct(protected string $value)
            {
            }

            public function value(): string
            {
                return $this->value;
            }
        };
    }

    protected function getDataHelper(): DataHelper
    {
        $internal_helper = new class () extends NullDataHelper {
            public function durationToIterator(string $duration): \Generator
            {
                foreach (explode(':', $duration) as $v) {
                    yield $v === '' ? null : $v;
                }
            }

            public function durationToSeconds(string $duration): int
            {
                $r = 0;
                foreach ($this->durationToIterator($duration) as $v) {
                    $r += $v;
                }
                return $r;
            }

            public function datetimeToObject(string $datetime): \DateTimeImmutable
            {
                return new \DateTimeImmutable($datetime);
            }

            public function durationFromIntegers(
                ?int $years,
                ?int $months,
                ?int $days,
                ?int $hours,
                ?int $minutes,
                ?int $seconds
            ): string {
                $array = [$years, $months, $days, $hours, $minutes, $seconds];
                return implode(':', $array);
            }

            public function datetimeFromObject(\DateTimeImmutable $object): string
            {
                return $object->format('Y-m-d');
            }
        };

        $data_presentation = new class () extends NullData {
            public function dataValue(ElementsDataInterface $data): string
            {
                return 'presentable ' . $data->value();
            }
        };

        return new DataHelper($internal_helper, $data_presentation);
    }

    public function testMakePresentable(): void
    {
        $helper = $this->getDataHelper();

        $this->assertSame(
            'presentable value',
            $helper->makePresentable($this->getData('value'))
        );
    }

    public function testMakePresentableAsList(): void
    {
        $helper = $this->getDataHelper();

        $this->assertSame(
            'presentable value1,? ,.presentable value2,? ,.presentable value3',
            $helper->makePresentableAsList(
                ',? ,.',
                $this->getData('value1'),
                $this->getData('value2'),
                $this->getData('value3')
            )
        );
    }

    public function testDurationToArray(): void
    {
        $helper = $this->getDataHelper();

        $this->assertSame(
            [89, 0, null, null, null, 1],
            $helper->durationToArray('89:0::::1')
        );
    }

    public function testDurationToSeconds(): void
    {
        $helper = $this->getDataHelper();

        $this->assertSame(
            89 + 5 + 1,
            $helper->durationToSeconds('89:5::::1')
        );
    }

    public function testDatetimeToObject(): void
    {
        $helper = $this->getDataHelper();

        $this->assertSame(
            '2013-01-20',
            $helper->datetimeToObject('2013-01-20')->format('Y-m-d')
        );
    }

    public function testDurationFromIntegers(): void
    {
        $helper = $this->getDataHelper();

        $this->assertSame(
            '89:0::::1',
            $helper->durationFromIntegers(89, 0, null, null, null, 1)
        );
    }

    public function testDatetimeFromObject(): void
    {
        $helper = $this->getDataHelper();

        $this->assertSame(
            '2013-01-20',
            $helper->datetimeFromObject(new \DateTimeImmutable('2013-01-20'))
        );
    }
}
