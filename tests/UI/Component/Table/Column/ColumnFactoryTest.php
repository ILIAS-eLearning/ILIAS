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

require_once 'tests/UI/AbstractFactoryTest.php';

use ILIAS\UI\Component\Table\Column;
use ILIAS\Data;

class ColumnFactoryTest extends AbstractFactoryTest
{
    public $kitchensink_info_settings = [
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

    public $factory_title = 'ILIAS\\UI\\Component\\Table\\Column\\Factory';

    protected function buildFactories()
    {
        $lng = $this->getMockBuilder(\ilLanguage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $lng->method('txt')->willReturnCallback(fn($v) => $v);

        return [
            new \ILIAS\UI\Implementation\Component\Table\Column\Factory($lng),
            new Data\Factory()
        ];
    }

    public function getColumnTypeProvider(): array
    {
        list($f, $df) = $this->buildFactories();
        $date_format = $df->dateFormat()->germanShort();

        return [
            [Column\Text::class, $f->text("")],
            [Column\Date::class, $f->date("", $date_format)],
            [Column\TimeSpan::class, $f->timespan("", $date_format)],
            [Column\Number::class, $f->number("")],
            [Column\Boolean::class, $f->boolean("", '1', '0')],
            [Column\Status::class, $f->status("")],
            [Column\StatusIcon::class, $f->statusIcon("")],
            [Column\EMail::class, $f->eMail("")],
            [Column\Link::class, $f->link("")],
            [Column\LinkListing::class, $f->linkListing("")]
        ];
    }

    /**
     * @dataProvider getColumnTypeProvider
     */
    public function testDataTableColsImplementInterfaces($class, $instance)
    {
        $this->assertInstanceOf(Column\Column::class, $instance);
        $this->assertInstanceOf($class, $instance);
    }
}
