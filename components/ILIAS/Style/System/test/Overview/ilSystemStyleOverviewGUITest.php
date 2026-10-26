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

require_once('vendor/composer/vendor/autoload.php');
include_once('./components/ILIAS/UI/tests/UITestHelper.php');

use ILIAS\HTTP\Services as HttpServices;
use ILIAS\HTTP\Wrapper\WrapperFactory;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\FileUpload\FileUpload;
use ILIAS\Tests\Refinery\ilLanguageMock;
use Psr\Http\Message\ServerRequestInterface;

class ilSystemStyleOverviewGUITest extends ilSystemStyleBaseFS
{
    use UITestHelper;

    protected ilSystemStyleOverviewGUI $overview_gui;

    protected ilCtrl $ctrl;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ctrl = $this->getMockBuilder(ilCtrl::class)->disableOriginalConstructor()->onlyMethods([
            'getFormAction',
            'getCmd'
        ])->getMock();

        $lng = new ilLanguageMock();
        $tpl = $this->mainTemplate();
        $ui_factory = $this->factory();
        $renderer = $this->renderer();
        $request_wrapper = $this->getMockBuilder(WrapperFactory::class)->disableOriginalConstructor()->getMock();

        $toolbar = $this->getMockBuilder(ilToolbarGUI::class)->disableOriginalConstructor()->getMock();
        $tabs = $this->getMockBuilder(ilTabsGUI::class)->disableOriginalConstructor()->getMock();
        $data_factory = new DataFactory();
        $refinery = new Refinery($data_factory, $lng);

        $factory = new ilSkinFactory($this->lng, $this->system_style_config);
        $help = $this->getMockBuilder(ilHelpGUI::class)->disableOriginalConstructor()->getMock();
        $upload = $this->getMockBuilder(FileUpload::class)->disableOriginalConstructor()->getMock();

        $this->overview_gui = new ilSystemStyleOverviewGUI(
            $this->ctrl,
            $lng,
            $tpl,
            $ui_factory,
            $renderer,
            $request_wrapper,
            $toolbar,
            $refinery,
            $factory,
            $upload,
            $tabs,
            $help,
            $this->getMockBuilder(ServerRequestInterface::class)->getMock(),
            $this->getMockBuilder(HttpServices::class)->disableOriginalConstructor()->getMock(),
            $this->container->getSkin()->getId(),
            $this->style->getId(),
            '1',
            false,
            true
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(ilSystemStyleOverviewGUI::class, $this->overview_gui);
    }
}
