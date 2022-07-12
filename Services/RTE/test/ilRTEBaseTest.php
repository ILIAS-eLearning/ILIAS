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

use ILIAS\DI\Container;
use ILIAS\HTTP\Agent\AgentDetermination;
use PHPUnit\Framework\TestCase;
use ILIAS\HTTP\Services as HttpServiceImpl;

/**
 * Class ilRTEBaseTest
 * @author Jephte Abijuru <jephte.abijuru@minervis.com>
 */
abstract class ilRTEBaseTest extends TestCase
{
    protected function setUp() : void
    {
        $GLOBALS['DIC'] = new Container();
        $this->setMocks();

        parent::setUp();
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    protected function setGlobalVariable(string $name, $value) : void
    {
        global $DIC;

        $GLOBALS[$name] = $value;

        unset($DIC[$name]);
        $DIC[$name] = static function ($c) use ($name) {
            return $GLOBALS[$name];
        };
    }

    protected function setMocks() : void
    {
        $tpl_mock = $this->createMock(ilGlobalTemplateInterface::class);
        $this->setGlobalVariable('tpl', $tpl_mock);

        $lng = $this
            ->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['txt', 'getInstalledLanguages', 'loadLanguageModule'])
            ->getMock();
        $this->setGlobalVariable('lng', $lng);

        $this->setGlobalVariable(
            'ilCtrl',
            $this->getMockBuilder(ilCtrlInterface::class)->disableOriginalConstructor()->getMock()
        );

        $this->setGlobalVariable(
            'ilClientIniFile',
            $this->getMockBuilder(ilIniFile::class)->disableOriginalConstructor()->getMock()
        );

        $this->setGlobalVariable(
            'ilUser',
            $this->getMockBuilder(ilObjUser::class)->disableOriginalConstructor()->getMock()
        );

        $http = $this
            ->getMockBuilder(HttpServiceImpl::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['agent'])
            ->getMock();
        $http
            ->method('agent')
            ->willReturn(new AgentDetermination());
        $this->setGlobalVariable('http', $http);
    }
}
