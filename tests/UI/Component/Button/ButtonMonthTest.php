<?php

/* Copyright (c) 2017 Alex Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use \ILIAS\UI\Component as C;
use \ILIAS\UI\Implementation as I;

/**
 * Test month button
 */
class ButtonMonthTest extends ILIAS_UI_TestBase
{

    /**
     * @return \ILIAS\UI\Implementation\Factory
     */
    public function getFactory()
    {
        return $this->button_factory = new I\Component\Button\Factory();
    }

    public function test_implements_factory_interface()
    {
        $f = $this->getFactory();

        $this->assertInstanceOf("ILIAS\\UI\\Component\\Button\\Month", $f->month("02-2017"));
    }

    public function test_get_default()
    {
        $f = $this->getFactory();
        $c = $f->month("02-2017");

        $this->assertEquals($c->getDefault(), "02-2017");
    }

    public function test_render()
    {
        $f = $this->getFactory();
        $r = $this->getDefaultRenderer();

        $c = $f->month("02-2017");

        $html = $r->render($c);

        $expected_html = <<<EOT
		<div  class="btn-group il-btn-month">
	<button type="button" class="btn btn-default dropdown-toggle" href="" data-toggle="dropdown" aria-expanded="false">
		<span class="il-current-month">month_02_short 2017</span>
		<span class="caret"></span>
		<span class="sr-only"></span>
	</button>
	<div class="dropdown-menu" data-default-date="02/1/2017" data-lang="en">
		<div class="inline-picker"></div>
	</div>
</div>
<script>il.Util.addOnLoad(function() {il.UI.button.initMonth('');});</script>
EOT;
        $this->assertHTMLEquals("<div>" . $expected_html . "</div>", "<div>" . $html . "</div>");
    }
}
