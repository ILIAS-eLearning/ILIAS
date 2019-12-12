<?php

namespace ILIAS\GlobalScreen\MainMenu;

use ILIAS\GlobalScreen\Identification\IdentificationFactory;
use ILIAS\GlobalScreen\Identification\Serializer\CoreSerializer;
use ILIAS\GlobalScreen\Identification\Serializer\SerializerInterface;
use ILIAS\GlobalScreen\Provider\Provider;
use ILIAS\GlobalScreen\Provider\ProviderFactoryInterface;
use ilPlugin;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

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
class IdentificationTest extends TestCase
{
    use MockeryPHPUnitIntegration;
    const MOCKED_PROVIDER_CLASSNAME = 'Mockery_1_ILIAS_GlobalScreen_Provider_Provider';
    /**
     * @var Mockery\MockInterface|ProviderFactoryInterface
     */
    private $provider_factory;
    /**
     * @var Mockery\MockInterface|Provider
     */
    private $provider_mock;
    /**
     * @var Mockery\MockInterface|ilPlugin
     */
    private $plugin_mock;
    /**
     * @var IdentificationFactory
     */
    private $identification;


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


    public function testMustThrowExceptionSinceSerializedIdentificationIsTooLong()
    {
        $string = str_repeat("x", SerializerInterface::MAX_LENGTH - strlen(self::MOCKED_PROVIDER_CLASSNAME) - strlen(CoreSerializer::DIVIDER) + 1);
        $this->expectException(\LogicException::class);
        $this->identification->core($this->provider_mock)->identifier($string);
    }


    public function testMustNotThrowExceptionSinceSerializedIdentificationIsExactLength()
    {
        $string = str_repeat("x", SerializerInterface::MAX_LENGTH - strlen(self::MOCKED_PROVIDER_CLASSNAME) - strlen(CoreSerializer::DIVIDER));
        $this->identification->core($this->provider_mock)->identifier($string);
    }
}
