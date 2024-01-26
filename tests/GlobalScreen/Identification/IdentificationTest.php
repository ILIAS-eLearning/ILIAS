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

use ILIAS\GlobalScreen\Identification\IdentificationFactory;
use ILIAS\GlobalScreen\Identification\Serializer\CoreSerializer;
use ILIAS\GlobalScreen\Identification\Serializer\SerializerInterface;
use ILIAS\GlobalScreen\Provider\Provider;
use ILIAS\GlobalScreen\Provider\ProviderFactory;
use ilPlugin;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use LogicException;

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
    public const MOCKED_PROVIDER_CLASSNAME = 'Mockery_1_ILIAS_GlobalScreen_Provider_Provider';
    /**
     * @var Mockery\MockInterface|ProviderFactory
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
     * @var \ILIAS\GlobalScreen\Identification\IdentificationFactory
     */
    private $identification;


    /**
     * @inheritDoc
     */
    protected function setUp() : void
    {
        parent::setUp();

        $this->plugin_mock = Mockery::mock(ilPlugin::class);

        $this->provider_mock = Mockery::mock(Provider::class);
        $this->provider_mock->shouldReceive('getProviderNameForPresentation')->andReturn('Provider')->byDefault();

        $this->provider_factory = Mockery::mock(ProviderFactory::class);
        $this->provider_factory->shouldReceive('getProviderByClassName')->with(self::MOCKED_PROVIDER_CLASSNAME)->andReturn($this->provider_mock);
        $this->provider_factory->shouldReceive('isInstanceCreationPossible')->andReturn(true);
        $this->provider_factory->shouldReceive('isRegistered')->andReturn(true);

        $this->identification = new IdentificationFactory($this->provider_factory);
    }


    public function testMustThrowExceptionSinceSerializedIdentificationIsTooLong() : void
    {
        $string = str_repeat("x", SerializerInterface::MAX_LENGTH - strlen(self::MOCKED_PROVIDER_CLASSNAME) - strlen(CoreSerializer::DIVIDER) + 1);
        $this->expectException(LogicException::class);
        $this->identification->core($this->provider_mock)->identifier($string);
    }


    public function testMustNotThrowExceptionSinceSerializedIdentificationIsExactLength() : void
    {
        $string = str_repeat("x", SerializerInterface::MAX_LENGTH - strlen(self::MOCKED_PROVIDER_CLASSNAME) - strlen(CoreSerializer::DIVIDER));
        $this->identification->core($this->provider_mock)->identifier($string);
        $this->assertTrue(true); // No Exception is thrown
    }
}
