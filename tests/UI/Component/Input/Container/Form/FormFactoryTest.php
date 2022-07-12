<?php declare(strict_types=1);

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
