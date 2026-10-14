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
use ILIAS\GlobalScreen\Identification\IdentificationFactory;
use ILIAS\GlobalScreen\Identification\Serializer\CoreSerializer;
use ILIAS\GlobalScreen\Identification\Serializer\SerializerInterface;
use ILIAS\GlobalScreen\Provider\Provider;
use ILIAS\GlobalScreen\Provider\ProviderFactory;
use PHPUnit\Framework\TestCase;
use LogicException;

require_once('./vendor/composer/vendor/autoload.php');

/**
 * Class IdentificationTest
 *
 * @author                 Fabian Schmid <fs@studer-raimann.ch>
 */
#[BackupGlobals(false)]
#[BackupStaticProperties(false)]
#[PreserveGlobalState(false)]
#[RunTestsInSeparateProcesses]
class IdentificationTest extends TestCase
{
    private Provider $provider_mock;
    private string $provider_class_name;
    private IdentificationFactory $identification;


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


    public function testMustThrowExceptionSinceSerializedIdentificationIsTooLong(): void
    {
        $string = str_repeat("x", SerializerInterface::MAX_LENGTH - strlen($this->provider_class_name) - strlen(CoreSerializer::DIVIDER) + 1);
        $this->expectException(LogicException::class);
        $this->identification->core($this->provider_mock)->identifier($string);
    }


    public function testMustNotThrowExceptionSinceSerializedIdentificationIsExactLength(): void
    {
        $string = str_repeat("x", SerializerInterface::MAX_LENGTH - strlen($this->provider_class_name) - strlen(CoreSerializer::DIVIDER));
        $identification = $this->identification->core($this->provider_mock)->identifier($string);
        $this->assertSame(SerializerInterface::MAX_LENGTH, strlen($identification->serialize()));
    }
}
