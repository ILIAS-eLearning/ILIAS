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

use ILIAS\DI\Container;
use PHPUnit\Framework\MockObject\Exception as MockObjectException;
use PHPUnit\Framework\TestCase;

/**
 * Class ilTestBaseClass
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestBaseTestCase extends TestCase
{
    use ilTestBaseTestCaseTrait;

    public const MOCKED_METHOD_WITHOUT_OUTPUT = 'MOCKED_METHOD_WITHOUT_OUTPUT';
    public const DYNAMIC_CLASS = 'DynamicClass';
    protected static int $DYNAMIC_CLASS_COUNT = 0;
    protected ?Container $dic = null;
    protected ?Container $backup_dic = null;

    /**
     * @inheritdoc
     * @throws MockObjectException
     */
    protected function setUp(): void
    {
        error_reporting(E_ALL);
        $this->defineGlobalConstants();

        global $DIC;
        $this->backup_dic = is_object($DIC) ? clone $DIC : $DIC;
        $DIC = $this->getMockBuilder(Container::class)->onlyMethods([])->getMock();

        $this->addGlobals();

        $this->dic = $DIC;

        parent::setUp();
    }

    protected function tearDown(): void
    {
        global $DIC;

        $DIC = $this->backup_dic;

        parent::tearDown();
    }

    /**
     * @throws MockObjectException
     */
    private function addGlobals(): void
    {
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
        $this->addGlobal_ilLoggerFactory();
        $this->addGlobal_GlobalScreenService();
        $this->addGlobal_ilNavigationHistory();
        $this->addGlobal_ilObjTest();
    }

    /**
     * @throws ReflectionException
     */
    public static function callMethod(string|object $obj, string $name, array $args = []): mixed
    {
        return (new ReflectionClass($obj))->getMethod($name)->invokeArgs($obj, $args);
    }

    /**
     * @throws ReflectionException
     */
    public static function getNonPublicPropertyValue(object $obj, string $name): mixed
    {
        $reflection_class = new ReflectionClass($obj);

        while ($reflection_class !== false && !$reflection_class->hasProperty($name)) {
            $reflection_class = $reflection_class->getParentClass();
        }

        return $reflection_class
            ? $reflection_class->getProperty($name)->getValue($obj)
            : throw new ReflectionException('Property not found.');
    }

    /**
     * @throws ReflectionException|Exception|MockObjectException
     */
    public function createInstanceOf(string $class_name, array $explicit_parameters = []): object
    {
        $constructor = (new ReflectionClass($class_name))->getConstructor();

        if (is_null($constructor)) {
            return new $class_name();
        }

        $parameters = [];

        foreach ($constructor->getParameters() as $constructor_parameter) {
            $constructor_parameter_name = $constructor_parameter->getName();

            if (isset($explicit_parameters[$constructor_parameter_name])) {
                $parameters[$constructor_parameter_name] = $explicit_parameters[$constructor_parameter_name];
                continue;
            }

            if ($constructor_parameter->isDefaultValueAvailable()) {
                $parameters[$constructor_parameter_name] = $constructor_parameter->getDefaultValue();
                continue;
            }

            if (!$constructor_parameter->hasType()) {
                throw new Exception('Constructor parameter has no type.');
            }

            $constructor_parameter_type_name = $constructor_parameter->getType()?->getName();
            $parameters[$constructor_parameter_name] = match ($constructor_parameter_type_name) {
                'string' => '',
                'int' => 0,
                'float' => 0.0,
                'bool', 'true' => true ,
                'false' => false,
                'array' => [],
                'null', 'resource' => null,
                'Closure' => (static fn() => null),
                'object' => (object) [],
                default => (function($constructor_parameter_type_name) {
                    if (enum_exists($constructor_parameter_type_name)) {
                        $enum_cases = $constructor_parameter_type_name::cases();
                        return array_shift($enum_cases);
                    }

                    return $this->getOrCreateMock($constructor_parameter_type_name);
                })($constructor_parameter_type_name)
            };
        }

        return new $class_name(...$parameters);
    }

    /**
     * @throws ReflectionException|MockObjectException
     */
    public function createTraitInstanceOf(string $class_name, array $explicit_parameters = []): object
    {
        if (trait_exists($class_name)) {
            $dynamic_class_name = self::DYNAMIC_CLASS . ++self::$DYNAMIC_CLASS_COUNT;
            eval("class $dynamic_class_name{use $class_name;}");
            return $this->createInstanceOf($dynamic_class_name, $explicit_parameters);
        }

        return $this->createInstanceOf($class_name, $explicit_parameters);
    }

    /**
     * @throws MockObjectException
     */
    private function getOrCreateMock(string $parameter_type): PHPUnit\Framework\MockObject\MockObject
    {
        if (isset($this->services[$parameter_type])) {
            global $DIC;
            if (!isset($DIC[$this->services[$parameter_type]])) {
                $DIC[$this->services[$parameter_type]] = $this->createMock($parameter_type);
            }

            return $DIC[$this->services[$parameter_type]];
        }

        return $this->createMock($parameter_type);
    }
}
