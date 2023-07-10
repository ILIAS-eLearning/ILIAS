<?php

declare(strict_types=1);

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

require_once(__DIR__ . "/../../../../libs/composer/vendor/autoload.php");
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

    public function test_implements_factory_interface(): void
    {
        $f = $this->getFactory();

        $this->assertInstanceOf("ILIAS\\UI\\Component\\Button\\Month", $f->month("02-2017"));
    }

    public function test_get_default(): void
    {
        $f = $this->getFactory();
        $c = $f->month("02-2017");

        $this->assertEquals("02-2017", $c->getDefault());
    }

    public function test_render(): void
    {
        $f = $this->getFactory();
        $r = $this->getDefaultRenderer();

        $c = $f->month("02-2017");

        $html = $r->render($c);

        $expected_html = <<<EOT
		<div id="id_1" class="btn-group il-btn-month">
	<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
		<span class="il-current-month">month_02_short 2017</span>
		<span class="caret"></span>
	</button>
	<div class="dropdown-menu" data-default-date="02/1/2017" data-lang="en">
		<div class="inline-picker"></div>
	</div>
</div>
EOT;
        $this->assertHTMLEquals("<div>" . $expected_html . "</div>", "<div>" . $html . "</div>");
    }
}
