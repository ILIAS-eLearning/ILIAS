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


require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");

use ILIAS\UI\Implementation\Component\Table\Column;
use ILIAS\UI\Implementation\Component\Link;

/**
 * Basic Tests for Table-Columns.
 */
class ColumnTest extends ILIAS_UI_TestBase
{
    public function testDataTableColumnsAttributes(): void
    {
        $col = new Column\Text('col');
        $this->assertEquals('col', $col->getTitle());

        $this->assertTrue($col->isSortable());
        $this->assertFalse($col->withIsSortable(false)->isSortable());
        $this->assertTrue($col->withIsSortable(true)->isSortable());

        $this->assertFalse($col->isOptional());
        $this->assertTrue($col->withIsOptional(true)->isOptional());
        $this->assertFalse($col->withIsOptional(false)->isOptional());

        $this->assertTrue($col->isInitiallyVisible());
        $this->assertFalse($col->withIsInitiallyVisible(false)->isInitiallyVisible());
        $this->assertTrue($col->withIsInitiallyVisible(true)->isInitiallyVisible());

        $this->assertFalse($col->isHighlighted());
        $this->assertTrue($col->withHighlight(true)->isHighlighted());
        $this->assertFalse($col->withHighlight(false)->isHighlighted());

        $this->assertEquals(12, $col->withIndex(12)->getIndex());
    }

    public function testDataTableColumnBoolFormat(): void
    {
        $col = new Column\Boolean('col', 'TRUE', 'FALSE');
        $this->assertEquals('TRUE', $col->format(true));
        $this->assertEquals('FALSE', $col->format(false));
    }

    public function testDataTableColumnDateFormat(): void
    {
        $df = new \ILIAS\Data\Factory();
        $format = $df->dateFormat()->germanShort();
        $dat = new \DateTimeImmutable();
        $col = new Column\Date('col', $format);
        $this->assertEquals($dat->format($format->toString()), $col->format($dat));
    }

    public function testDataTableColumnTimespanFormat(): void
    {
        $df = new \ILIAS\Data\Factory();
        $format = $df->dateFormat()->germanShort();
        $dat = new \DateTimeImmutable();
        $col = new Column\Timespan('col', $format);
        $this->assertEquals(
            $dat->format($format->toString()) . ' - ' . $dat->format($format->toString()),
            $col->format([$dat, $dat])
        );
    }

    public function testDataTableColumnNumnberFormat(): void
    {
        $df = new \ILIAS\Data\Factory();
        $dat = new \DateTimeImmutable();
        $col = new Column\Number('col');
        $this->assertEquals('1', $col->format(1));
        $col = $col->withDecimals(3);
        $this->assertEquals('1,000', $col->format(1));
        $col = $col->withDecimals(2)->withUnit('$', $col::UNIT_POSITION_FORE);
        $this->assertEquals('$ 1,00', $col->format(1));
        $col = $col->withUnit('€', $col::UNIT_POSITION_AFT);
        $this->assertEquals('1,00 €', $col->format(1));
    }

    public function testDataTableColumnLinkFormat(): void
    {
        $col = new Column\Link('col');
        $link = new Link\Standard('label', '#');
        $this->assertEquals($link, $col->format($link));
    }

    public function testDataTableColumnLinkFormatAcceptsOnlyLinks(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $col = new Column\Link('col');
        $link = 'some string';
        $this->assertEquals($link, $col->format($link));
    }
}
