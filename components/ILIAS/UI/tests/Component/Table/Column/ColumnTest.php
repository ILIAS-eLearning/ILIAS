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


require_once("vendor/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");

use ILIAS\UI\Implementation\Component\Table\Column;
use ILIAS\UI\Implementation\Component\Link;
use ILIAS\UI\Implementation\Component\Listing;
use ILIAS\UI\Implementation\Component\Symbol\Icon\Standard as StandardIcon;
use ILIAS\UI\Implementation\Component\Symbol\Glyph\Glyph;
use ILIAS\UI\Component\Symbol\Glyph\Glyph as GlyphInterface;

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
        $this->assertFalse($col->withIsOptional(true, false)->isInitiallyVisible());
        $this->assertTrue($col->withIsOptional(true, true)->isInitiallyVisible());

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

    public function testDataTableColumnBoolFormatWithIcon(): void
    {
        $ok = new StandardIcon('', 'ok', 'small', false);
        $no = new StandardIcon('', 'notok', 'small', false);
        $col = new Column\Boolean('col', $ok, $no);
        $this->assertEquals($ok, $col->format(true));
        $this->assertEquals($no, $col->format(false));
    }

    public function testDataTableColumnBoolFormatWithGlyph(): void
    {
        $ok = new Glyph(GlyphInterface::LIKE, '');
        $no = new Glyph(GlyphInterface::DISLIKE, '');
        $col = new Column\Boolean('col', $ok, $no);
        $this->assertEquals($ok, $col->format(true));
        $this->assertEquals($no, $col->format(false));
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

    public static function provideColumnFormats(): array
    {
        return [
            [
                'column' => new Column\LinkListing(''),
                'value' => new Listing\Unordered([(new Link\Standard('label', '#')),(new Link\Standard('label', '#'))]),
                'ok' => true
            ],
            [
                'column' => new Column\LinkListing(''),
                'value' => new Listing\Unordered(['string', 'string']),
                'ok' => false
            ],
            [
                'column' => new Column\LinkListing(''),
                'value' => new Listing\Ordered([(new Link\Standard('label', '#')),(new Link\Standard('label', '#'))]),
                'ok' => true
            ],
            [
                'column' => new Column\LinkListing(''),
                'value' => 123,
                'ok' => false
            ],
            [
                'column' => new Column\Link(''),
                'value' => new Link\Standard('label', '#'),
                'ok' => true
            ],
            [
                'column' => new Column\Link(''),
                'value' => 'some string',
                'ok' => false
            ],
            [
                'column' => new Column\StatusIcon(''),
                'value' => new StandardIcon('', '', 'small', false),
                'ok' => true
            ],
            [
                'column' => new Column\StatusIcon(''),
                'value' => 'some string',
                'ok' => false
            ],
        ];
    }

    /**
￼    * @dataProvider provideColumnFormats
￼    */
    public function testDataTableColumnAllowedFormats(
        Column\Column $column,
        mixed $value,
        bool $ok
    ): void {
        if(! $ok) {
            $this->expectException(\InvalidArgumentException::class);
        }
        $this->assertEquals($value, $column->format($value));
    }

}
