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

require_once(__DIR__ . '/../../UI/tests/Base.php');
require_once(__DIR__ . '/../../UI/tests/UITestHelper.php');
require_once(__DIR__ . '/ilTestBaseTestCaseTrait.php');

use PHPUnit\Framework\TestCase;
use ILIAS\DI\Container;

/**
 * Class ilTestBaseClass
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestBaseTestCase extends TestCase
{
    use ilTestBaseTestCaseTrait;

    protected ?Container $dic = null;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        global $DIC;

        $this->dic = is_object($DIC) ? clone $DIC : $DIC;

        $DIC = new Container();

        $this->addGlobal_tpl();
        $this->addGlobal_ilDB();
        $this->addGlobal_ilias();
        $this->addGlobal_ilLog();
        $this->addGlobal_ilErr();
        $this->addGlobal_tree();
        $this->addGlobal_lng();
        $this->addGlobal_ilAppEventHandler();
        $this->addGlobal_objDefinition();
        $this->addGlobal_refinery();
        $this->addGlobal_ilComponentFactory();
        $this->addGlobal_uiFactory();
        $this->addGlobal_uiRenderer();

        $this->getMockBuilder(\ILIAS\DI\LoggingServices::class)->disableOriginalConstructor()->getMock();

        $http_mock = $this
            ->getMockBuilder(\ILIAS\HTTP\Services::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['request', 'wrapper'])->getMock();

        $request_mock = $this
            ->getMockBuilder(\GuzzleHttp\Psr7\ServerRequest::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getParsedBody'])->getMock();
        $request_mock->method('getParsedBody')->willReturn(array());
        $http_mock->method('request')->willReturn($request_mock);

        $wrapper_mock = $this->createMock(\ILIAS\HTTP\Wrapper\WrapperFactory::class);
        $http_mock->method('wrapper')->willReturn($wrapper_mock);

        $this->setGlobalVariable('http', $http_mock);

        parent::setUp();
    }

    protected function tearDown(): void
    {
        global $DIC;

        $DIC = $this->dic;

        parent::tearDown();
    }

    public static function callMethod($obj, $name, array $args = []) {
        return (new ReflectionClass($obj))->getMethod($name)->invokeArgs($obj, $args);
    }
}
