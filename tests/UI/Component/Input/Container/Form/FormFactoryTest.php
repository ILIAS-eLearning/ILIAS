<?php

require_once 'tests/UI/AbstractFactoryTest.php';

use \ILIAS\UI\Component\Input\Container\Form;
use ILIAS\UI\Implementation\Component\SignalGenerator;
use \ILIAS\Data;
use ILIAS\Refinery;

class FormFactoryTest extends AbstractFactoryTest
{
    public $kitchensink_info_settings = array(
        "standard" => array(
            "context" => false,
        ),
    );
    public $factory_title = 'ILIAS\\UI\\Component\\Input\\Container\\Form\\Factory';


    final public function buildFactory()
    {
        $df = new Data\Factory();
        $language = $this->createMock(\ilLanguage::class);
        return new \ILIAS\UI\Implementation\Component\Input\Container\Form\Factory(
            new \ILIAS\UI\Implementation\Component\Input\Field\Factory(
                new SignalGenerator(),
                $df,
                new \ILIAS\Refinery\Factory($df, $language),
                $language
            )
        );
    }


    public function test_implements_factory_interface()
    {
        $f = $this->buildFactory();

        $form = $f->standard("#", []);
        $this->assertInstanceOf(Form\Form::class, $form);
        $this->assertInstanceOf(Form\Standard::class, $form);
    }
}
