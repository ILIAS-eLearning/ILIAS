<?php

namespace ILIAS\GlobalScreen\MainMenu;

use ILIAS\GlobalScreen\Identification\CoreIdentification;
use ILIAS\GlobalScreen\Identification\IdentificationFactory;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Identification\IdentificationProviderInterface;
use ILIAS\GlobalScreen\Identification\PluginIdentification;
use ILIAS\GlobalScreen\Provider\Provider;
use ILIAS\GlobalScreen\Provider\ProviderFactoryInterface;
use ILIAS\GlobalScreen\Provider\StaticProvider\StaticMainMenuProvider;
use ilPlugin;
use InvalidArgumentException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use Serializable;

require_once('./libs/composer/vendor/autoload.php');

/**
 * Class IdentificationFactoryTest
 *
 * @author                 Fabian Schmid <fs@studer-raimann.ch>
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState    disabled
 * @backupGlobals          disabled
 * @backupStaticAttributes disabled
 */
class IdentificationFactoryTest extends TestCase
{
    use MockeryPHPUnitIntegration;
    const MOCKED_PROVIDER_CLASSNAME = 'Mockery_1_ILIAS_GlobalScreen_Provider_Provider';
    /**
     * @var Mockery\MockInterface|ProviderFactoryInterface
     */
    protected $provider_factory;
    /**
     * @var Mockery\MockInterface|Provider
     */
    protected $provider_mock;
    /**
     * @var Mockery\MockInterface|ilPlugin
     */
    protected $plugin_mock;
    /**
     * @var IdentificationFactory
     */
    protected $identification;


    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->plugin_mock = Mockery::mock(ilPlugin::class);

        $this->provider_mock = Mockery::mock(Provider::class);
        $this->provider_mock->shouldReceive('getProviderNameForPresentation')->andReturn('Provider')->byDefault();

        $this->provider_factory = Mockery::mock(ProviderFactoryInterface::class);
        $this->provider_factory->shouldReceive('getProviderByClassName')->with(self::MOCKED_PROVIDER_CLASSNAME)->andReturn($this->provider_mock);
        $this->provider_factory->shouldReceive('isInstanceCreationPossible')->andReturn(true);
        $this->provider_factory->shouldReceive('isRegistered')->andReturn(true);

        $this->identification = new IdentificationFactory($this->provider_factory);
    }


    public function testAvailableMethods()
    {
        $r = new ReflectionClass($this->identification);

        $methods = [];
        foreach ($r->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $methods[] = $method->getName();
        }
        sort($methods);
        $this->assertEquals(
            $methods,
            [
                0 => '__construct',
                1 => 'core',
                2 => 'fromSerializedIdentification',
                3 => 'plugin',
            ]
        );
    }


    public function testCore()
    {
        $this->assertInstanceOf(IdentificationProviderInterface::class, $this->identification->core($this->provider_mock));
        $this->assertInstanceOf(IdentificationInterface::class, $this->identification->core($this->provider_mock)->identifier('dummy'));
    }


    public function testPlugin()
    {
        $this->plugin_mock->shouldReceive('getId')->once()->andReturn('xdemo');
        $identification_provider = $this->identification->plugin($this->plugin_mock, $this->provider_mock);
        $this->assertInstanceOf(IdentificationProviderInterface::class, $identification_provider);
        $identification = $identification_provider->identifier('dummy');
        $this->assertInstanceOf(IdentificationInterface::class, $identification);
    }


    public function testSerializingCore()
    {
        $identification = $this->identification->core($this->provider_mock)->identifier('dummy');
        $this->assertInstanceOf(Serializable::class, $identification);
        $this->assertEquals($identification->serialize(), get_class($this->provider_mock) . "|dummy");
    }


    public function testUnserializingCore()
    {
        $identification = $this->identification->core($this->provider_mock)->identifier('dummy');
        $serialized_identification = $identification->serialize();

        $new_identification = $this->identification->fromSerializedIdentification($serialized_identification);
        $this->assertEquals($identification, $new_identification);
    }


    public function testUnserializingPlugin()
    {
        $this->plugin_mock->shouldReceive('getId')->once()->andReturn('xdemo');
        $identification = $this->identification->plugin($this->plugin_mock, $this->provider_mock)->identifier('dummy');
        $serialized_identification = $identification->serialize();
        $this->provider_mock->shouldReceive('getProviderNameForPresentation')->andReturn('Provider');
        $new_identification = $this->identification->fromSerializedIdentification($serialized_identification);
        $this->assertEquals($identification, $new_identification);
    }


    public function testUnserializingFailsSinceNobpbyCanhandleTheString()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Nobody can handle serialized identification 'ThisStringWillNobodyHandle'.");
        $this->identification->fromSerializedIdentification("ThisStringWillNobodyHandle");
    }


    public function testFactoryMustReturnCorrectTypeCore()
    {
        $class_name = self::MOCKED_PROVIDER_CLASSNAME;
        $internal_identifier = "internal_identifier";

        $string_core = "{$class_name}|{$internal_identifier}";
        $identification = $this->identification->fromSerializedIdentification($string_core);

        $this->assertInstanceOf(CoreIdentification::class, $identification);
        $this->assertEquals($identification->getClassName(), $class_name);
        $this->assertEquals($identification->getInternalIdentifier(), $internal_identifier);
    }


    public function testFactoryMustReturnCorrectTypePlugin()
    {
        $class_name = self::MOCKED_PROVIDER_CLASSNAME;
        $internal_identifier = "internal_identifier";

        // $this->markTestSkipped('I currently have absolutely no idea why this test does not work since this seems to be identical zo the test testUnserializingCore :(');
        $string_plugin = "xdemo|{$class_name}|{$internal_identifier}";
        $identification = $this->identification->fromSerializedIdentification($string_plugin);

        $this->assertInstanceOf(PluginIdentification::class, $identification);
        $this->assertEquals($identification->getClassName(), $class_name);
        $this->assertEquals($identification->getInternalIdentifier(), $internal_identifier);
    }
}
