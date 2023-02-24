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
        "statusIcon" => ["context" => false, "rules" => false]
    ];

    public $factory_title = 'ILIAS\\UI\\Component\\Table\\Column\\Factory';

    protected function buildFactories()
    {
        return [
            new \ILIAS\UI\Implementation\Component\Table\Column\Factory(),
            new Data\Factory()
        ];
    }

    public function testImplementsInterfaces()
    {
        list($f, $df) = $this->buildFactories();

        $text = $f->text("");
        $this->assertInstanceOf(Column\Column::class, $text);
        $this->assertInstanceOf(Column\Text::class, $text);

        $number = $f->number("");
        $this->assertInstanceOf(Column\Column::class, $number);
        $this->assertInstanceOf(Column\Number::class, $number);

        $date_format = $df->dateFormat()->germanShort();
        $date = $f->date("", $date_format);
        $this->assertInstanceOf(Column\Column::class, $date);
        $this->assertInstanceOf(Column\Date::class, $date);
    }
}
