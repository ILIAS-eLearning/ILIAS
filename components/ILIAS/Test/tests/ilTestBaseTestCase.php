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

require_once __DIR__ . '/ilTestBaseTestCaseTrait.php';

use PHPUnit\Framework\MockObject\Exception as MockObjectException;
use PHPUnit\Framework\TestCase;
use ILIAS\DI\Container;

/**
 * Class ilTestBaseClass
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestBaseTestCase extends TestCase
{
    use ilTestBaseTestCaseTrait;
    public const MOCKED_METHOD_WITHOUT_OUTPUT = "MOCKED_METHOD_WITHOUT_OUTPUT";
    protected ?Container $backup_dic = null;
    protected ?Container $dic = null;


    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        if (!defined("ILIAS_HTTP_PATH")) {
            define("ILIAS_HTTP_PATH", "http://localhost");
        }
        if (!defined("CLIENT_DATA_DIR")) {
            define("CLIENT_DATA_DIR", "/var/iliasdata");
        }
        if (!defined("ANONYMOUS_USER_ID")) {
            define("ANONYMOUS_USER_ID", 13);
        }
        if (!defined("ROOT_FOLDER_ID")) {
            define("ROOT_FOLDER_ID", 8);
        }
        if (!defined("ILIAS_LOG_ENABLED")) {
            define("ILIAS_LOG_ENABLED", true);
        }
        if (!defined("ILIAS_LOG_DIR")) {
            define("ILIAS_LOG_DIR", '/var/log');
        }
        if (!defined("ILIAS_LOG_FILE")) {
            define("ILIAS_LOG_FILE", '/var/log/ilias.log');
        }
        if (!defined("IL_INST_ID")) {
            define("IL_INST_ID", 'someid');
        }

        global $DIC;

        $this->backup_dic = is_object($DIC) ? clone $DIC : $DIC;

        $DIC = $this->getMockBuilder(Container::class)->onlyMethods(['uiService'])->getMock();
        $DIC->method('uiService')->willReturn($this->createMock(ilUIService::class));

        $this->addGlobal_ilAccess();
        $this->addGlobal_dataFactory();
        $this->addGlobal_tpl();
        $this->addGlobal_ilDB();
        $this->addGlobal_ilUser();
        $this->addGlobal_ilias();
        $this->addGlobal_ilErr();
        $this->addGlobal_tree();
        $this->addGlobal_lng();
        $this->addGlobal_ilAppEventHandler();
        $this->addGlobal_objDefinition();
        $this->addGlobal_refinery();
        $this->addGlobal_rbacsystem();
        $this->addGlobal_rbacreview();
        $this->addGlobal_ilRbacAdmin();
        $this->addGlobal_http();
        $this->addGlobal_fileDelivery();
        $this->addGlobal_ilComponentFactory();
        $this->addGlobal_ilComponentRepository();
        $this->addGlobal_uiFactory();
        $this->addGlobal_uiRenderer();
        $this->addGlobal_uiService();
        $this->addGlobal_static_url();
        $this->addGlobal_upload();
        $this->addGlobal_ilLog();
        $this->addGlobal_ilSetting();
        $this->addGlobal_ilCtrl();
        $this->addGlobal_ilObjDataCache();
        $this->addGlobal_ilHelp();
        $this->addGlobal_ilTabs();
        $this->addGlobal_ilLocator();
        $this->addGlobal_ilToolbar();
        $this->addGlobal_ilObjectCustomIconFactory();
        $this->addGlobal_filesystem();

        $this->dic = $DIC;

        $this->defineGlobalConstants();

        parent::setUp();
    }

    protected function tearDown(): void
    {
        global $DIC;

        $DIC = $this->backup_dic;

        parent::tearDown();
    }

    /**
     * @throws ReflectionException
     */
    public static function callMethod($obj, $name, array $args = []): mixed
    {
        return (new ReflectionClass($obj))->getMethod($name)->invokeArgs($obj, $args);
    }

    /**
     * @throws ReflectionException
     */
    public static function getNonPublicPropertyValue(object $obj, string $name): mixed
    {
        return (new ReflectionProperty($obj, $name))->getValue($obj);
    }

    /**
     * @throws ReflectionException|Exception|MockObjectException
     */
    public function createInstanceOf(string $className, array $explicitParameters = []): object
    {
        $constructor = (new ReflectionClass($className))->getConstructor();

        if (is_null($constructor)) {
            return new $className();
        }

        $parameters = [];

        foreach ($constructor->getParameters() as $constructorParameter) {
            $constructorParameterName = $constructorParameter->getName();

            if (isset($explicitParameters[$constructorParameterName])) {
                $parameters[$constructorParameterName] = $explicitParameters[$constructorParameterName];
                continue;
            }

            if ($constructorParameter->isDefaultValueAvailable()) {
                $parameters[$constructorParameterName] = $constructorParameter->getDefaultValue();
                continue;
            }

            if (!$constructorParameter->hasType()) {
                throw new Exception('Constructor parameter has no type.');
            }

            $constructorParameterTypeName = $constructorParameter->getType()?->getName();
            $parameters[$constructorParameterName] = match ($constructorParameterTypeName) {
                'string' => '',
                'int' => 0,
                'float' => 0.0,
                'bool', 'true' => true ,
                'false' => false,
                'array' => [],
                'null', 'resource' => null,
                default => $this->getOrCreateMock($constructorParameterTypeName)
            };
        }
        return new $className(...$parameters);
    }

    private function getOrCreateMock(string $parameterType): PHPUnit\Framework\MockObject\MockObject
    {
        if(isset($this->services[$parameterType])) {
            global $DIC;
            if (!isset($DIC[$this->services[$parameterType]])) {
                $DIC[$this->services[$parameterType]] = $this->createMock($parameterType);
            }

            return $DIC[$this->services[$parameterType]];
        }
        return $this->createMock($parameterType);
    }
}
