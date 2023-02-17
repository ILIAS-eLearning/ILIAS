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

use ILIAS\UI\Component\Table\Action;
use ILIAS\Data;

class ActionFactoryTest extends AbstractFactoryTest
{
    public $kitchensink_info_settings = [
        "standard" => ["context" => false, "rules" => false],
        "single" => ["context" => false, "rules" => false],
        "multi" => ["context" => false, "rules" => false]
    ];

    public $factory_title = 'ILIAS\\UI\\Component\\Table\\Action\\Factory';

    protected function buildFactories()
    {
        return [
            new \ILIAS\UI\Implementation\Component\Table\Action\Factory(),
            new Data\Factory()
        ];
    }

    public function testImplementsInterfaces()
    {
        list($f, $df) = $this->buildFactories();

        $standard = $f->standard("", "", $df->uri('http://www.ilias.de'));
        $this->assertInstanceOf(Action\Action::class, $standard);
        $this->assertInstanceOf(Action\Standard::class, $standard);

        $single = $f->single("", "", $df->uri('http://www.ilias.de'));
        $this->assertInstanceOf(Action\Action::class, $single);
        $this->assertInstanceOf(Action\Single::class, $single);

        $multi = $f->multi("", "", $df->uri('http://www.ilias.de'));
        $this->assertInstanceOf(Action\Action::class, $multi);
        $this->assertInstanceOf(Action\Multi::class, $multi);
    }
}
