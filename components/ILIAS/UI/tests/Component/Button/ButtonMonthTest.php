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

require_once(__DIR__ . "/../../../../../../vendor/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use ILIAS\UI\Implementation as I;

/**
 * Test month button
 */
class ButtonMonthTest extends ILIAS_UI_TestBase
{
    public function getFactory(): I\Component\Button\Factory
    {
        return new I\Component\Button\Factory();
    }

    public function testImplementsFactoryInterface(): void
    {
        $f = $this->getFactory();

        $this->assertInstanceOf("ILIAS\\UI\\Component\\Button\\Month", $f->month("02-2017"));
    }

    public function testGetDefault(): void
    {
        $f = $this->getFactory();
        $c = $f->month("02-2017");

        $this->assertEquals("02-2017", $c->getDefault());
    }

    public function testRender(): void
    {
        $f = $this->getFactory();
        $r = $this->getDefaultRenderer();

        $c = $f->month("02-2017");

        $html = $r->render($c);

        $expected_html = <<<EOT
            <div id="id_1" class="btn-group il-btn-month">
                <input type="month" class="btn btn-default" value="2017-02" />
            </div>
EOT;
        $this->assertEquals(
            $this->brutallyTrimHTML($expected_html),
            $this->brutallyTrimHTML($html)
        );
    }
}
