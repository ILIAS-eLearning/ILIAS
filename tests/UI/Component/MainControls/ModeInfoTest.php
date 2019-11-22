<?php

use ILIAS\Data\URI;
use ILIAS\UI\Implementation\Component\MainControls\ModeInfo;
use ILIAS\UI\Implementation\Component\Symbol\Glyph\GlyphRendererFactory;
use ILIAS\UI\Implementation\Render\DefaultRendererFactory;
use ILIAS\UI\Implementation\Render\FSLoader;
use ILIAS\UI\Implementation\Render\JavaScriptBinding;
use ILIAS\UI\Implementation\Render\LoaderCachingWrapper;
use ILIAS\UI\Implementation\Render\LoaderResourceRegistryWrapper;

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
        $mode_info = NEW ModeInfo('That\'s one small step for [a] man', new URI('http://one_giant_leap_for_mankind'));

        $r = $this->getDefaultRenderer();
        $html = $r->render($mode_info);

        $expected = <<<EOT
		<div class="il-maincontrols-footer">
			<div class="il-footer-content">
				<div class="il-footer-text">
					footer text
				</div>

				<div class="il-footer-links">
					<ul>
						<li><a href="http://www.ilias.de" >Goto ILIAS</a></li>
						<li><a href="#" >go up</a></li>
					</ul>
				</div>
			</div>
		</div>
EOT;

        $this->assertEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }


    protected function buildButtonFactory()
    {
        return new ILIAS\UI\Implementation\Component\Button\Factory;
    }


    public function getUIFactory()
    {
        require_once('./tests/UI/Component/Input/Container/Form/StandardFormTest.php');

        return new WithButtonNoUIFactory($this->buildButtonFactory());
    }


    public function getDefaultRenderer(JavaScriptBinding $js_binding = null)
    {
        $ui_factory = $this->getUIFactory();
        $tpl_factory = $this->getTemplateFactory();
        $resource_registry = $this->getResourceRegistry();
        $lng = $this->getLanguage();
        if (!$js_binding) {
            $js_binding = $this->getJavaScriptBinding();
        }

        $refinery = $this->getRefinery();

        $component_renderer_loader
            = new LoaderCachingWrapper(
            new LoaderResourceRegistryWrapper(
                $resource_registry,
                new FSLoader(
                    new DefaultRendererFactory(
                        $ui_factory,
                        $tpl_factory,
                        $lng,
                        $js_binding,
                        $refinery
                    ),
                    new GlyphRendererFactory(
                        $ui_factory,
                        $tpl_factory,
                        $lng,
                        $js_binding,
                        $refinery
                    ),
                    new FieldRendererFactory(
                        $ui_factory,
                        $tpl_factory,
                        $lng,
                        $js_binding,
                        $refinery
                    )
                )
            )
        );

        return new TestDefaultRenderer($component_renderer_loader);
    }
}
