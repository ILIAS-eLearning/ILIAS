<?php

use ILIAS\Data\URI;
use ILIAS\UI\Implementation\Component\MainControls\ModeInfo;
use ILIAS\UI\Implementation\Render\JavaScriptBinding;

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

/**
 * Class ModeInfoTest
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ModeInfoTest extends ILIAS_UI_TestBase
{

    public function testRendering()
    {
        $mode_title = 'That\'s one small step for [a] man';
        $uri_string = 'http://one_giant_leap?for=mankind';
        $mode_info = new ModeInfo($mode_title, new URI($uri_string));

        $r = $this->getDefaultRenderer();
        $html = $r->render($mode_info);

        $expected = <<<EOT
		<div class="il-mode-info">
	        <span class="il-mode-info-content">
		        $mode_title<a class="glyph" href="$uri_string"> <span class="glyphicon glyphicon-remove" aria-hidden="true"></a></span>
	        </span>
        </div>
EOT;

        $this->assertEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }


    public function getDefaultRenderer(JavaScriptBinding $js_binding = null)
    {
        return parent::getDefaultRenderer($js_binding);
    }
}
