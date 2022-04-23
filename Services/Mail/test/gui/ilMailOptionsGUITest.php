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

use ILIAS\HTTP\Wrapper\WrapperFactory;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory;

/**
 * Class ilMailOptionsGUITest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailOptionsGUITest extends ilMailBaseTest
{
    /**
     * @throws ReflectionException
     */
    protected function getMailOptionsGUI(
        GlobalHttpState $httpState,
        ilCtrlInterface $ctrl,
        ilSetting $settings
    ) : ilMailOptionsGUI {
        $tpl = $this->getMockBuilder(ilGlobalTemplateInterface::class)->disableOriginalConstructor()->getMock();
        $lng = $this->getMockBuilder(ilLanguage::class)->disableOriginalConstructor()->getMock();
        $user = $this->getMockBuilder(ilObjUser::class)->disableOriginalConstructor()->getMock();

        return new ilMailOptionsGUI(
            $tpl,
            $ctrl,
            $settings,
            $lng,
            $user,
            $httpState,
            new Factory(new \ILIAS\Data\Factory(), $lng)
        );
    }

    /**
     * @doesNotPerformAssertions
     * @throws ReflectionException
     */
    public function testMailOptionsAreAccessibleIfGlobalAccessIsNotDenied() : void
    {
        $ctrl = $this->getMockBuilder(ilCtrl::class)->disableOriginalConstructor()->getMock();
        $settings = $this->getMockBuilder(ilSetting::class)->disableOriginalConstructor()->getMock();
        $form = $this->getMockBuilder(ilMailOptionsFormGUI::class)->disableOriginalConstructor()->getMock();

        $settings->method('get')->with('show_mail_settings')->willReturn('1');
        $ctrl->method('getCmd')->willReturn('showOptions');

        $request = $this->getMockBuilder(ServerRequestInterface::class)->disableOriginalConstructor()->getMock();
        $request->method('getQueryParams')->willReturn([]);
        $wrapper = new WrapperFactory($request);

        $http = $this->getMockBuilder(GlobalHttpState::class)->getMock();
        $http->method('wrapper')->willReturn($wrapper);

        $gui = $this->getMailOptionsGUI($http, $ctrl, $settings);
        $gui->setForm($form);
        $gui->executeCommand();
    }

    /**
     * @throws ReflectionException
     */
    public function testMailOptionsAreNotAccessibleIfGlobalAccessIsDeniedAndUserWillBeRedirectedToMailSystem() : void
    {
        $ctrl = $this->getMockBuilder(ilCtrl::class)->disableOriginalConstructor()->getMock();
        $settings = $this->getMockBuilder(ilSetting::class)->disableOriginalConstructor()->getMock();
        $form = $this->getMockBuilder(ilMailOptionsFormGUI::class)->disableOriginalConstructor()->getMock();

        $settings->method('get')->with('show_mail_settings')->willReturn('0');
        $ctrl->method('getCmd')->willReturn('showOptions');

        $request = $this->getMockBuilder(ServerRequestInterface::class)->disableOriginalConstructor()->getMock();
        $request->method('getQueryParams')->willReturn([]);
        $wrapper = new WrapperFactory($request);

        $http = $this->getMockBuilder(GlobalHttpState::class)->getMock();
        $http->method('wrapper')->willReturn($wrapper);

        $ctrl->expects($this->once())->method('redirectByClass')->with(ilMailGUI::class);

        $gui = $this->getMailOptionsGUI($http, $ctrl, $settings);
        $gui->setForm($form);
        $gui->executeCommand();
    }

    /**
     * @throws ReflectionException
     */
    public function testMailOptionsAreNotAccessibleIfGlobalAccessIsDeniedAndUserWillBeRedirectedToPersonalSettings() : void
    {
        $this->expectException(ilCtrlException::class);

        $request = $this->getMockBuilder(ServerRequestInterface::class)->disableOriginalConstructor()->getMock();
        $ctrl = $this->createMock(ilCtrlInterface::class);
        $settings = $this->getMockBuilder(ilSetting::class)->disableOriginalConstructor()->getMock();
        $form = $this->getMockBuilder(ilMailOptionsFormGUI::class)->disableOriginalConstructor()->getMock();

        $settings->method('get')->with('show_mail_settings')->willReturn('0');
        $ctrl->method('getCmd')->willReturn('showOptions');

        $ctrl->expects($this->once())->method('redirectByClass')->with(ilPersonalSettingsGUI::class)->willThrowException(
            new ilCtrlException('Script terminated')
        );

        $request = $this->getMockBuilder(ServerRequestInterface::class)->disableOriginalConstructor()->getMock();
        $request->method('getQueryParams')->willReturn([
            'referrer' => ilPersonalSettingsGUI::class,
        ]);
        $wrapper = new WrapperFactory($request);

        $http = $this->getMockBuilder(GlobalHttpState::class)->getMock();
        $http->method('wrapper')->willReturn($wrapper);

        $gui = $this->getMailOptionsGUI($http, $ctrl, $settings);
        $gui->setForm($form);
        $gui->executeCommand();
    }
}
