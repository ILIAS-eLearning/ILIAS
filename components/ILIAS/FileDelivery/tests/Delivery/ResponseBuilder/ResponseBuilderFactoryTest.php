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

namespace ILIAS\Tests\FileDelivery\Delivery\ResponseBuilder;

use ILIAS\FileDelivery\Delivery\ResponseBuilder\PHPResponseBuilder;
use ILIAS\FileDelivery\Delivery\ResponseBuilder\ResponseBuilderFactory;
use ILIAS\FileDelivery\Delivery\ResponseBuilder\XAccelResponseBuilder;
use ILIAS\FileDelivery\Delivery\ResponseBuilder\XSendFileResponseBuilder;
use ILIAS\FileDelivery\Setup\DeliveryMethodObjective;
use PHPUnit\Framework\TestCase;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class ResponseBuilderFactoryTest extends TestCase
{
    /** @var list<string> */
    private array $artefacts = [];

    public function testEmptySettingsFallBackToPHPDelivery(): void
    {
        $this->assertInstanceOf(PHPResponseBuilder::class, ResponseBuilderFactory::fromArray([]));
    }

    public function testUnknownDeliveryMethodFallsBackToPHPDelivery(): void
    {
        $builder = ResponseBuilderFactory::fromArray([
            DeliveryMethodObjective::SETTINGS => 'something_else',
        ]);

        $this->assertInstanceOf(PHPResponseBuilder::class, $builder);
    }

    public function testPHPDeliveryIsBuilt(): void
    {
        $builder = ResponseBuilderFactory::fromArray([
            DeliveryMethodObjective::SETTINGS => DeliveryMethodObjective::PHP,
        ]);

        $this->assertInstanceOf(PHPResponseBuilder::class, $builder);
    }

    public function testXSendFileDeliveryIsBuilt(): void
    {
        $builder = ResponseBuilderFactory::fromArray([
            DeliveryMethodObjective::SETTINGS => DeliveryMethodObjective::XSENDFILE,
        ]);

        $this->assertInstanceOf(XSendFileResponseBuilder::class, $builder);
    }

    public function testXAccelDeliveryIsBuiltWithTheExternalDataDirectory(): void
    {
        $builder = ResponseBuilderFactory::fromArray([
            DeliveryMethodObjective::SETTINGS => DeliveryMethodObjective::XACCEL,
            DeliveryMethodObjective::SETTINGS_EXTERNAL_DATA_DIR => '/var/ilias/data',
        ]);

        $this->assertInstanceOf(XAccelResponseBuilder::class, $builder);
    }

    /**
     * X-Accel cannot be constructed without the directory the web server maps
     * its internal location to, so an incomplete artefact must not take the
     * installation down on every download.
     */
    public function testXAccelWithoutExternalDataDirectoryFallsBackToPHPDelivery(): void
    {
        $builder = ResponseBuilderFactory::fromArray([
            DeliveryMethodObjective::SETTINGS => DeliveryMethodObjective::XACCEL,
        ]);

        $this->assertInstanceOf(PHPResponseBuilder::class, $builder);
    }

    public function testXAccelWithEmptyExternalDataDirectoryFallsBackToPHPDelivery(): void
    {
        $builder = ResponseBuilderFactory::fromArray([
            DeliveryMethodObjective::SETTINGS => DeliveryMethodObjective::XACCEL,
            DeliveryMethodObjective::SETTINGS_EXTERNAL_DATA_DIR => '',
        ]);

        $this->assertInstanceOf(PHPResponseBuilder::class, $builder);
    }

    public function testMissingArtefactFallsBackToPHPDelivery(): void
    {
        $builder = ResponseBuilderFactory::fromArtefact(
            sys_get_temp_dir() . '/does_not_exist_delivery_method.php'
        );

        $this->assertInstanceOf(PHPResponseBuilder::class, $builder);
    }

    public function testArtefactIsRead(): void
    {
        $path = $this->artefact("<?php return ['delivery_method' => 'xsendfile'];");

        $this->assertInstanceOf(XSendFileResponseBuilder::class, ResponseBuilderFactory::fromArtefact($path));
    }

    public function testArtefactWithNonArrayContentFallsBackToPHPDelivery(): void
    {
        $builder = ResponseBuilderFactory::fromArtefact($this->artefact('<?php return "nonsense";'));

        $this->assertInstanceOf(PHPResponseBuilder::class, $builder);
    }

    public function testArtefactWithoutReturnValueFallsBackToPHPDelivery(): void
    {
        $builder = ResponseBuilderFactory::fromArtefact($this->artefact('<?php // nothing returned'));

        $this->assertInstanceOf(PHPResponseBuilder::class, $builder);
    }

    private function artefact(string $php): string
    {
        $path = (string) tempnam(sys_get_temp_dir(), 'delivery_method_') . '.php';
        file_put_contents($path, $php);
        $this->artefacts[] = $path;

        return $path;
    }

    protected function tearDown(): void
    {
        foreach ($this->artefacts as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }

        $this->artefacts = [];
    }
}
