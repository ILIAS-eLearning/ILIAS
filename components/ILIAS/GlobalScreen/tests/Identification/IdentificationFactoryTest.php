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

namespace ILIAS\GlobalScreen\MainMenu;

use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\BackupStaticProperties;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ILIAS\GlobalScreen\Identification\CoreIdentification;
use ILIAS\GlobalScreen\Identification\IdentificationFactory;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Identification\IdentificationProviderInterface;
use ILIAS\GlobalScreen\Identification\PluginIdentification;
use ILIAS\GlobalScreen\Provider\Provider;
use ILIAS\GlobalScreen\Provider\ProviderFactory;
use ilPlugin;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use Serializable;

require_once('./vendor/composer/vendor/autoload.php');

/**
 * Class IdentificationFactoryTest
 *
 * @author                 Fabian Schmid <fs@studer-raimann.ch>
 */
#[BackupGlobals(false)]
#[BackupStaticProperties(false)]
#[PreserveGlobalState(false)]
#[RunTestsInSeparateProcesses]
class IdentificationFactoryTest extends TestCase
{
    protected Provider $provider_mock;
    protected string $provider_class_name;
    protected IdentificationFactory $identification;


    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->provider_mock = $this->createStub(Provider::class);
        $this->provider_mock->method('getProviderNameForPresentation')->willReturn('Provider');
        $this->provider_class_name = $this->provider_mock::class;

        $provider_factory = $this->createStub(ProviderFactory::class);
        $provider_factory->method('getProviderByClassName')->willReturnMap([
            [$this->provider_class_name, $this->provider_mock],
        ]);
        $provider_factory->method('isInstanceCreationPossible')->willReturn(true);
        $provider_factory->method('isRegistered')->willReturn(true);

        $this->identification = new IdentificationFactory($provider_factory);
    }


    public function testAvailableMethods(): void
    {
        $r = new ReflectionClass($this->identification);

        $methods = [];
        foreach ($r->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $methods[] = $method->getName();
        }
        sort($methods);
        $this->assertSame(
            [
                0 => '__construct',
                1 => 'core',
                2 => 'fromSerializedIdentification',
                3 => 'plugin',
                4 => 'tool'
            ],
            $methods
        );
    }


    public function testCore(): void
    {
        $this->assertInstanceOf(IdentificationProviderInterface::class, $this->identification->core($this->provider_mock));
        $this->assertInstanceOf(IdentificationInterface::class, $this->identification->core($this->provider_mock)->identifier('dummy'));
    }


    public function testPlugin(): void
    {
        $plugin_mock = $this->createMock(ilPlugin::class);
        $plugin_mock->expects($this->once())->method('getId')->willReturn('xdemo');
        $identification_provider = $this->identification->plugin($plugin_mock->getId(), $this->provider_mock);
        $this->assertInstanceOf(IdentificationProviderInterface::class, $identification_provider);
        $identification = $identification_provider->identifier('dummy');
        $this->assertInstanceOf(IdentificationInterface::class, $identification);
    }


    public function testSerializingCore(): void
    {
        $identification = $this->identification->core($this->provider_mock)->identifier('dummy');
        $this->assertInstanceOf(Serializable::class, $identification);
        $this->assertEquals($identification->serialize(), $this->provider_mock::class . "|dummy");
    }


    public function testUnserializingCore(): void
    {
        $identification = $this->identification->core($this->provider_mock)->identifier('dummy');
        $serialized_identification = $identification->serialize();

        $new_identification = $this->identification->fromSerializedIdentification($serialized_identification);
        $this->assertEquals($identification, $new_identification);
    }


    public function testUnserializingPlugin(): void
    {
        $plugin_mock = $this->createMock(ilPlugin::class);
        $plugin_mock->expects($this->once())->method('getId')->willReturn('xdemo');
        $identification = $this->identification->plugin($plugin_mock->getId(), $this->provider_mock)->identifier('dummy');
        $serialized_identification = $identification->serialize();
        $new_identification = $this->identification->fromSerializedIdentification($serialized_identification);
        $this->assertEquals($identification, $new_identification);
    }


    public function testUnserializingFailsSinceNobpbyCanhandleTheString(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Nobody can handle serialized identification 'ThisStringWillNobodyHandle'.");
        $this->identification->fromSerializedIdentification("ThisStringWillNobodyHandle");
    }


    public function testFactoryMustReturnCorrectTypeCore(): void
    {
        $class_name = $this->provider_class_name;
        $internal_identifier = "internal_identifier";

        $string_core = "$class_name|$internal_identifier";
        $identification = $this->identification->fromSerializedIdentification($string_core);

        $this->assertInstanceOf(CoreIdentification::class, $identification);
        $this->assertSame($identification->getClassName(), $class_name);
        $this->assertSame($identification->getInternalIdentifier(), $internal_identifier);
    }


    public function testFactoryMustReturnCorrectTypePlugin(): void
    {
        $class_name = $this->provider_class_name;
        $internal_identifier = "internal_identifier";

        $string_plugin = "xdemo|$class_name|$internal_identifier";
        $identification = $this->identification->fromSerializedIdentification($string_plugin);

        $this->assertInstanceOf(PluginIdentification::class, $identification);
        $this->assertSame($identification->getClassName(), $class_name);
        $this->assertSame($identification->getInternalIdentifier(), $internal_identifier);
    }
}
