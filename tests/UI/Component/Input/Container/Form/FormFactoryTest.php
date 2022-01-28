<?php declare(strict_types=1);

require_once 'tests/UI/AbstractFactoryTest.php';

use ILIAS\UI\Component\Input\Container\Form;
use ILIAS\UI\Implementation\Component\SignalGenerator;
use ILIAS\Data;
use ILIAS\UI\Implementation\Component\Input as I;
use ILIAS\Refinery\Factory;

class FormFactoryTest extends AbstractFactoryTest
{
    public array $kitchensink_info_settings = [
        "standard" => [
            "context" => false,
        ],
    ];

    public string $factory_title = 'ILIAS\\UI\\Component\\Input\\Container\\Form\\Factory';

    final public function buildFactory() : I\Container\Form\Factory
    {
        $df = new Data\Factory();
        $language = $this->createMock(ilLanguage::class);
        return new I\Container\Form\Factory(
            new I\Field\Factory(
                new SignalGenerator(),
                $df,
                new Factory($df, $language),
                $language
            ),
            new DefNamesource()

        );
    }

    public function test_implements_factory_interface() : void
    {
        $f = $this->buildFactory();

        $form = $f->standard("#", []);
        $this->assertInstanceOf(Form\Form::class, $form);
        $this->assertInstanceOf(Form\Standard::class, $form);
    }
}
