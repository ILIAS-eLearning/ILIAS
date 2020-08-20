<?php

require_once 'tests/UI/AbstractFactoryTest.php';

use \ILIAS\UI\Component\Table\Column;
use \ILIAS\Data;

class ColumnFactoryTest extends AbstractFactoryTest
{
    public $kitchensink_info_settings = [
        "text" => ["context" => false, "rules" => false],
        "number" => ["context" => false, "rules" => false],
        "date" => ["context" => false, "rules" => false]
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
