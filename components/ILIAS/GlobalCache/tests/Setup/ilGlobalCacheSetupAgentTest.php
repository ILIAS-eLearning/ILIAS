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

namespace ILIAS\Tests\GlobalCache\Setup;

use PHPUnit\Framework\TestCase;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Setup\Objective\NullObjective;
use ILIAS\Cache\Config;

class TestObj extends \ilGlobalCacheSetupAgent
{
    public function getMServer(array $node)
    {
        return $this->convertNode($node);
    }
}

class ilGlobalCacheSetupAgentTest extends TestCase
{
    private Refinery $refinery;
    /**
     * @var \ilGlobalCacheSetupAgent
     */
    protected $obj;

    public function setUp(): void
    {
        $this->refinery = new Refinery($this->createMock(DataFactory::class), $this->createMock(\ilLanguage::class));

        $this->obj = new TestObj($this->refinery);
    }

    public function testCreate(): void
    {
        $this->assertInstanceOf(\ilGlobalCacheSetupAgent::class, $this->obj);
    }

    public function testHasConfig(): void
    {
        $this->assertTrue($this->obj->hasConfig());
    }

    public function testGetArrayToConfigTransformationWithNullData(): void
    {
        /** @var Config $config */
        $fnc = $this->obj->getArrayToConfigTransformation();
        $settings = $fnc(null);
        $config = $settings->toConfig();

        $this->assertInstanceOf(\ilGlobalCacheSettingsAdapter::class, $settings);
        $this->assertFalse($config->isActivated());
    }

    public function testGetArrayToConfigTransformationWithEmptyDataArray(): void
    {
        /** @var Config $config */
        $fnc = $this->obj->getArrayToConfigTransformation();
        $settings = $fnc(null);
        $config = $settings->toConfig();

        $this->assertInstanceOf(\ilGlobalCacheSettingsAdapter::class, $settings);
        $this->assertFalse($config->isActivated());
    }

    public function testGetArrayToConfigTransformationWithNullComponents(): void
    {
        /** @var Config $config */
        $fnc = $this->obj->getArrayToConfigTransformation();
        $settings = $fnc(["components" => null]);
        $config = $settings->toConfig();

        $this->assertInstanceOf(\ilGlobalCacheSettingsAdapter::class, $settings);
        $this->assertFalse($config->isActivated());
    }

    public function testGetArrayToConfigTransformationWithNullMemcachedData(): void
    {
        /** @var Config $config */
        $fnc = $this->obj->getArrayToConfigTransformation();
        $settings = $fnc(["service" => "memcached"]);
        $config = $settings->toConfig();

        $this->assertInstanceOf(\ilGlobalCacheSettingsAdapter::class, $settings);
        $this->assertFalse($config->isActivated());
    }

    public function testGetArrayToConfigTransformationWithNullMemcachedDataArray(): void
    {
        /** @var Config $config */
        $fnc = $this->obj->getArrayToConfigTransformation();
        $settings = $fnc(["service" => "memcached", "memcached_nodes" => null]);
        $config = $settings->toConfig();

        $this->assertInstanceOf(\ilGlobalCacheSettingsAdapter::class, $settings);
        $this->assertFalse($config->isActivated());
    }

    public function testGetArrayToConfigTransformationWithEmptyMemcachedDataArray(): void
    {
        /** @var Config $config */
        $fnc = $this->obj->getArrayToConfigTransformation();
        $settings = $fnc(["service" => "memcached", "memcached_nodes" => []]);
        $config = $settings->toConfig();

        $this->assertInstanceOf(\ilGlobalCacheSettingsAdapter::class, $settings);
        $this->assertFalse($config->isActivated());
    }

    public function testGetArrayToConfigTransformationWithDataServices(): void
    {
        $services = [
            Config::PHPSTATIC => "static",
            Config::MEMCACHED => "memcached",
            Config::APCU => "apc"
        ];

        $node = [
            "active" => "1",
            "host" => "test.de",
            "port" => "9874",
            "weight" => "10"
        ];

        foreach ($services as $key => $service) {
            /** @var Config $config */
            $fnc = $this->obj->getArrayToConfigTransformation();

            $settings = $fnc(
                [
                    "service" => $service,
                    "memcached_nodes" => [$node],
                    "components" => ["dummy" => true]
                ]
            );
            $config = $settings->toConfig();
            $this->assertInstanceOf(\ilGlobalCacheSettingsAdapter::class, $settings);
            $this->assertEquals($key, $config->getAdaptorName());
        }
    }

    public function testGetArrayToConfigTransformationWithServiceException(): void
    {
        $fnc = $this->obj->getArrayToConfigTransformation();

        $node = [
            "active" => "1",
            "host" => "test.de",
            "port" => "9874",
            "weight" => "10"
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Unknown caching service: 'non_existing_service'");

        $fnc(
            [
                "service" => "non_existing_service",
                "memcached_nodes" => [$node],
                "components" => ["dummy"]
            ]
        );
    }

    public function testGetArrayToConfigTransformationWithMemcachedNode(): void
    {
        $fnc = $this->obj->getArrayToConfigTransformation();

        $node = [
            "active" => "1",
            "host" => "test.de",
            "port" => "9874",
            "weight" => "10"
        ];

        $settings = $fnc(
            [
                "service" => "memcached",
                "memcached_nodes" => [$node],
                "components" => ["dummy" => true]
            ]
        );

        $this->assertInstanceOf(\ilGlobalCacheSettingsAdapter::class, $settings);
        $this->assertIsArray($settings->getMemcachedNodes());

        $settings = $settings->getMemcachedNodes();
        $node = array_shift($settings);

        $this->assertEquals("test.de", $node->getHost());
        $this->assertEquals("9874", $node->getPort());
        $this->assertEquals("10", $node->getWeight());
    }

    public function testGetMemcachedServerActive(): void
    {
        $node = $node = [
            "active" => "1",
            "host" => "my.test.de",
            "port" => "1111",
            "weight" => "20"
        ];

        $result = $this->obj->getMServer($node);

        $this->assertEquals("my.test.de", $result->getHost());
        $this->assertEquals("1111", $result->getPort());
        $this->assertEquals("20", $result->getWeight());
    }

    public function testGetMemcachedServerInactive(): void
    {
        $node = $node = [
            "active" => "0",
            "host" => "my.test.de",
            "port" => "1111",
            "weight" => "20"
        ];

        $result = $this->obj->getMServer($node);

        $this->assertEquals("my.test.de", $result->getHost());
        $this->assertEquals("1111", $result->getPort());
        $this->assertEquals("20", $result->getWeight());
    }

    public function testGetInstallObjectives(): void
    {
        $setup_conf_mock = $this->createMock(\ilGlobalCacheSettingsAdapter::class);
        $objective_collection = $this->obj->getInstallObjective($setup_conf_mock);

        $this->assertEquals('Store configuration of components/ILIAS/GlobalCache_', $objective_collection->getLabel());
        $this->assertFalse($objective_collection->isNotable());
    }

    public function testGetUpdateObjective(): void
    {
        $setup_conf_mock = $this->createMock(\ilGlobalCacheSettingsAdapter::class);
        $objective_collection = $this->obj->getUpdateObjective($setup_conf_mock);

        $this->assertEquals('Store configuration of components/ILIAS/GlobalCache_', $objective_collection->getLabel());
        $this->assertFalse($objective_collection->isNotable());
    }

    public function testGetUpdateObjectiveWithoutConfig(): void
    {
        $objective_collection = $this->obj->getUpdateObjective();

        $this->assertInstanceOf(NullObjective::class, $objective_collection);
    }


    public function testGetBuildArtifactObjective(): void
    {
        $objective_collection = $this->obj->getBuildObjective();

        $this->assertInstanceOf(NullObjective::class, $objective_collection);
    }
}
