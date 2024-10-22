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

namespace ILIAS\Export\Test\ExportHandler\Repository\Element;

use DateTimeImmutable;
use Exception;
use ILIAS\Export\ExportHandler\I\Repository\Element\HandlerInterface as ilExportHandlerRepositoryElementInterface;
use ILIAS\Export\ExportHandler\I\Repository\Values\HandlerInterface as ilExportHandlerRepositoryValuesInterface;
use ILIAS\Export\ExportHandler\Repository\Element\Collection as ilExportHandlerRepositoryElementCollection;
use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
    public function testExportHandlerRepositoryElementCollection(): void
    {
        $date_1 = new DateTimeImmutable('2020-01-01');
        $date_2 = new DateTimeImmutable('2020-01-02');
        $date_3 = new DateTimeImmutable('2020-01-03');
        $values_mock_01 = $this->createMock(ilExportHandlerRepositoryValuesInterface::class);
        $values_mock_01->method("getCreationDate")->willReturn($date_1);
        $values_mock_02 = $this->createMock(ilExportHandlerRepositoryValuesInterface::class);
        $values_mock_02->method("getCreationDate")->willReturn($date_2);
        $values_mock_03 = $this->createMock(ilExportHandlerRepositoryValuesInterface::class);
        $values_mock_03->method("getCreationDate")->willReturn($date_2);
        $values_mock_04 = $this->createMock(ilExportHandlerRepositoryValuesInterface::class);
        $values_mock_04->method("getCreationDate")->willReturn($date_3);
        $element_mock_01 = $this->createMock(ilExportHandlerRepositoryElementInterface::class);
        $element_mock_01->method('getValues')->willReturn($values_mock_01);
        $element_mock_02 = $this->createMock(ilExportHandlerRepositoryElementInterface::class);
        $element_mock_02->method('getValues')->willReturn($values_mock_02);
        $element_mock_03 = $this->createMock(ilExportHandlerRepositoryElementInterface::class);
        $element_mock_03->method('getValues')->willReturn($values_mock_03);
        $element_mock_04 = $this->createMock(ilExportHandlerRepositoryElementInterface::class);
        $element_mock_04->method('getValues')->willReturn($values_mock_04);
        $element_mock_01->method("equals")->willReturnMap([
            [$element_mock_01, true], [$element_mock_02, false],
            [$element_mock_03, false], [$element_mock_04, false]
        ]);
        $element_mock_02->method("equals")->willReturnMap([
            [$element_mock_01, false], [$element_mock_02, true],
            [$element_mock_03, false], [$element_mock_04, false]
        ]);
        $element_mock_03->method("equals")->willReturnMap([
            [$element_mock_01, false], [$element_mock_02, false],
            [$element_mock_03, true], [$element_mock_04, false]
        ]);
        $element_mock_04->method("equals")->willReturnMap([
            [$element_mock_01, false], [$element_mock_02, false],
            [$element_mock_03, false], [$element_mock_04, true]
        ]);
        try {
            $collection_empty = new ilExportHandlerRepositoryElementCollection();
            $collection = $collection_empty
                ->withElement($element_mock_01)
                ->withElement($element_mock_02)
                ->withElement($element_mock_03)
                ->withElement($element_mock_04);
            $collection->rewind();
            $collection_empty->rewind();
            self::assertTrue($element_mock_01->equals($element_mock_01));
            self::assertFalse($element_mock_01->equals($element_mock_02));
            self::assertFalse($element_mock_01->equals($element_mock_03));
            self::assertFalse($element_mock_01->equals($element_mock_04));
            self::assertTrue($collection->valid());
            self::assertFalse($collection_empty->valid());
            self::assertCount(4, $collection);
            self::assertCount(0, $collection_empty);
            $this->checkElements($collection, [
                $element_mock_01,
                $element_mock_02,
                $element_mock_03,
                $element_mock_04
            ]);
            self::assertObjectEquals($element_mock_04, $collection->newest());
            # Check if newest() call changed element order
            $this->checkElements($collection, [
                $element_mock_01,
                $element_mock_02,
                $element_mock_03,
                $element_mock_04
            ]);
        } catch (Exception $exception) {
            self::fail($exception->getMessage());
        }
    }

    /**
     * @param ilExportHandlerRepositoryElementInterface[] $elements
     */
    protected function checkElements(
        ilExportHandlerRepositoryElementCollection $collection,
        array $elements
    ): void {
        $i = 0;
        foreach ($collection as $element) {
            self::assertTrue($elements[$i++]->equals($element));
        }
    }
}
