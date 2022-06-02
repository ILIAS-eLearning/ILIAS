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
 
require_once("libs/composer/vendor/autoload.php");

require_once(__DIR__ . "/Base.php");

use ILIAS\DI\Container;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ILIAS\Refinery\Factory as RefinaryFactory;
use \ILIAS\Data\Factory as DataFactory;

/**
 * Class UITestHelper can be helpful for test cases outside the UI Components, to inject a working
 * factory and renderer into some classes to be unit tested.
 * See UITestHelperTest for an example of how this can be used.
 */
class UITestHelper
{
    protected Container $dic;

    public function init(Container $dic = null) : Container
    {
        if ($dic) {
            $this->dic = $dic;
        } else {
            $this->dic = new Container();
        }

        $tpl_fac = new ilIndependentTemplateFactory();
        $this->dic["tpl"] = $tpl_fac->getTemplate("tpl.main.html", false, false);
        $this->dic["lng"] = new ilLanguageMock();
        $data_factory = new DataFactory();
        $this->dic["refinery"] = new RefinaryFactory($data_factory, $this->dic["lng"]);
        (new InitUIFramework())->init($this->dic);
        $this->dic["ui.template_factory"] = new ilIndependentTemplateFactory();
        ;

        return $this->dic;
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

    public function mainTemplate() : ilGlobalTemplateInterface
    {
        if (!isset($this->dic)) {
            $this->init();
        }
        return $this->dic->ui()->mainTemplate();
    }
}
