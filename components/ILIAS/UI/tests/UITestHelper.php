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

require_once(__DIR__ . '/../../../../vendor/composer/vendor/autoload.php');
require_once(__DIR__ . '/Base.php');

use ILIAS\DI\Container;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ILIAS\Refinery\Factory as RefinaryFactory;
use ILIAS\Data\Factory as DataFactory;

/**
 * Class UITestHelper can be helpful for test cases outside the UI Components, to inject a working
 * factory and renderer into some classes to be unit tested.
 * See UITestHelperTest for an example of how this can be used.
 */
trait UITestHelper
{
    protected Container $dic_with_ui;

    public function init(?Container $dic = null): Container
    {
        if ($dic) {
            $this->dic_with_ui = $dic;
        } else {
            $this->dic_with_ui = new Container();
        }

        $tpl_fac = new ilIndependentTemplateFactory();
        $this->dic_with_ui["tpl"] = $tpl_fac->getTemplate("tpl.main.html", false, false);
        $this->dic_with_ui["lng"] = new LanguageMock();
        $data_factory = new DataFactory();
        $this->dic_with_ui["refinery"] = new RefinaryFactory($data_factory, $this->dic_with_ui["lng"]);

        (new InitUIFramework())->init($this->dic_with_ui);

        $this->dic_with_ui["ui.template_factory"] = new ilIndependentTemplateFactory();
        $this->dic_with_ui["help.text_retriever"] = new ILIAS\UI\Help\TextRetriever\Echoing();

        return $this->dic_with_ui;
    }

    public function factory(): Factory
    {
        if (!isset($this->dic_with_ui)) {
            $this->init();
        }
        return $this->dic_with_ui->ui()->factory();
    }

    public function renderer(): Renderer
    {
        if (!isset($this->dic_with_ui)) {
            $this->init();
        }
        return $this->dic_with_ui->ui()->renderer();
    }

    public function mainTemplate(): ilGlobalTemplateInterface
    {
        if (!isset($this->dic_with_ui)) {
            $this->init();
        }
        return $this->dic_with_ui->ui()->mainTemplate();
    }
}
