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

require_once 'components/ILIAS/UI/tests/AbstractFactoryTestCase.php';

use ILIAS\UI\Component\Table\Column;
use ILIAS\Data;

class ColumnFactoryTest extends AbstractFactoryTestCase
{
    public static array $kitchensink_info_settings = [
        "text" => ["context" => false, "rules" => false],
        "number" => ["context" => false, "rules" => false],
        "date" => ["context" => false, "rules" => false],
        "boolean" => ["context" => false, "rules" => false],
        "eMail" => ["context" => false, "rules" => false],
        "status" => ["context" => false, "rules" => false],
        "statusIcon" => ["context" => false, "rules" => false],
        "timeSpan" => ["context" => false, "rules" => false],
        "link" => ["context" => false, "rules" => false],
        "linkListing" => ["context" => false, "rules" => false]
    ];

    public static string $factory_title = 'ILIAS\\UI\\Component\\Table\\Column\\Factory';

    protected function buildColumnFactory()
    {
        $lng = $this->getMockBuilder(\ilLanguage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $lng->method('txt')->willReturnCallback(fn($v) => $v);

        return new \ILIAS\UI\Implementation\Component\Table\Column\Factory($lng);
    }

    public static function getColumnTypeProvider(): array
    {
        $date_format = (new Data\Factory())->dateFormat()->germanShort();

        return [
            [static fn($f) => [Column\Text::class, $f->text("")]],
            [static fn($f) => [Column\Text::class, $f->text("")]],
            [static fn($f) => [Column\Date::class, $f->date("", $date_format)]],
            [static fn($f) => [Column\TimeSpan::class, $f->timespan("", $date_format)]],
            [static fn($f) => [Column\Number::class, $f->number("")]],
            [static fn($f) => [Column\Boolean::class, $f->boolean("", '1', '0')]],
            [static fn($f) => [Column\Status::class, $f->status("")]],
            [static fn($f) => [Column\StatusIcon::class, $f->statusIcon("")]],
            [static fn($f) => [Column\Link::class, $f->link("")]],
            [static fn($f) => [Column\EMail::class, $f->eMail("")]],
            [static fn($f) => [Column\LinkListing::class, $f->linkListing("")]]
        ];
    }

    /**
     * @dataProvider getColumnTypeProvider
     */
    public function testDataTableColsImplementInterfaces(\Closure $col): void
    {
        $factory = $this->buildColumnFactory();
        list($class, $instance) = $col($factory);
        $this->assertInstanceOf(Column\Column::class, $instance);
        $this->assertInstanceOf($class, $instance);
    }
}
