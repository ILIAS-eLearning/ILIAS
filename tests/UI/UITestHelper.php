<?php declare(strict_types=1);

require_once("libs/composer/vendor/autoload.php");

require_once(__DIR__ . "/Base.php");

use ILIAS\DI\Container;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ILIAS\Refinery\Factory as RefinaryFactory;
use \ILIAS\Data\Factory as DataFactory;

/**
 * Class UITestHelper can be helpful for test cases outside the UI Components, to inject a working
 * factory and renderer into some clases to be unit tested.
 *
 * See; UITestHelperTest, testRenderExample for an example of how this can be used.
 */
class UITestHelper
{
    protected Container $dic;

    protected function init() : void
    {
        $this->dic = new Container();
        $tpl_fac = new ilIndependentTemplateFactory();
        $this->dic["tpl"] = $tpl_fac->getTemplate("tpl.main.html", false, false);
        $this->dic["lng"] = new ilLanguageMock();
        $data_factory = new DataFactory();
        $this->dic["refinery"] = new RefinaryFactory($data_factory, $this->dic["lng"]);
        (new InitUIFramework())->init($this->dic);
    }

    public function factory() : Factory
    {
        if (!isset($this->dic)) {
            $this->init();
        }
        return $this->dic->ui()->factory();
    }

    public function renderer() : Renderer
    {
        if (!isset($this->dic)) {
            $this->init();
        }
        return $this->dic->ui()->renderer();
    }
}