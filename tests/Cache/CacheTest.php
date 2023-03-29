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

namespace ILIAS\GlobalScreen\Scope\Layout;

use ILIAS\Cache\Adaptor\APCu;
use ILIAS\Cache\Adaptor\Memcached;
use ILIAS\Cache\Config;
use ILIAS\Cache\Container\ActiveContainer;
use ILIAS\Cache\Container\Request;
use ILIAS\Cache\Nodes\Node;
use ILIAS\Cache\Services;
use PHPUnit\Framework\TestCase;
use ILIAS\Cache\Container\VoidContainer;
use ILIAS\Cache\Adaptor\PHPStatic;
use ILIAS\Cache\Container\BaseRequest;
use ILIAS\Cache\Nodes\NodeRepository;
use ILIAS\Cache\Nodes\NullNodeRepository;

require_once(__DIR__ . '/../../libs/composer/vendor/autoload.php');

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class CacheTest extends TestCase
{
    public const TEST_CONTAINER = 'test_container';
    /**
     * @var \ilLanguage|(\ilLanguage&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit\Framework\MockObject\MockObject
     */
    private \ilLanguage $language_mock;
    private \ILIAS\Refinery\Factory $refinery;

    protected function setUp(): void
    {
        $this->language_mock = $this->createMock(\ilLanguage::class);
        $this->refinery = new \ILIAS\Refinery\Factory(
            new \ILIAS\Data\Factory(),
            $this->language_mock
        );
        // prevent chached values between tests
        $static_flush = new PHPStatic($this->getConfig(Config::PHPSTATIC));
        $static_flush->flush();
    }

    /**
     * @return Config
     */
    protected function getConfig(string $adaptor_name = Config::PHPSTATIC): Config
    {
        return new Config(
            $adaptor_name,
            true,
            [self::TEST_CONTAINER],
            new class () extends NullNodeRepository {
                public function getNodes(): array
                {
                    return [new Node('127.0.0.1', 11211, 100)];
                }
            }
        );
    }

    public function testActivatedComponents()
    {
        $config = new Config(
            Config::PHPSTATIC,
            true,
            ['one', 'two']
        );

        $services = new Services($config);
        $this->assertInstanceOf(ActiveContainer::class, $services->get($this->getDummyRequest('one')));
        $this->assertInstanceOf(ActiveContainer::class, $services->get($this->getDummyRequest('two')));
        $this->assertInstanceOf(VoidContainer::class, $services->get($this->getDummyRequest('three')));

        $config = new Config(
            Config::PHPSTATIC,
            true,
            ['*']
        );
        $services = new Services($config);
        $this->assertInstanceOf(ActiveContainer::class, $services->get($this->getDummyRequest('one')));
        $this->assertInstanceOf(ActiveContainer::class, $services->get($this->getDummyRequest('two')));
        $this->assertInstanceOf(ActiveContainer::class, $services->get($this->getDummyRequest('three')));
    }

    public function testMultipleContainers(): void
    {
        $config = new Config(
            Config::PHPSTATIC,
            true,
            ['one', 'two']
        );

        $services = new Services($config);

        $one = $this->getDummyRequest('one');
        $two = $this->getDummyRequest('two');
        $three = $this->getDummyRequest('three');

        $this->assertEquals('one', $one->getContainerKey());
        $this->assertEquals('two', $two->getContainerKey());
        $this->assertEquals('three', $three->getContainerKey());

        $container_one = $services->get($one);
        $container_two = $services->get($two);
        $container_three = $services->get($three);

        $this->assertInstanceOf(ActiveContainer::class, $container_one);
        $this->assertInstanceOf(ActiveContainer::class, $container_two);
        $this->assertInstanceOf(VoidContainer::class, $container_three);

        $container_one->set('test', 'test_value');
        $this->assertTrue($container_one->has('test'));
        $this->assertEquals('test_value', $container_one->get('test', $this->refinery->to()->string()));

        $container_two->set('test', 'test_value');
        $this->assertTrue($container_two->has('test'));
        $this->assertEquals('test_value', $container_two->get('test', $this->refinery->to()->string()));

        $container_three->set('test', 'test_value');
        $this->assertFalse($container_three->has('test'));
        $this->assertNull($container_three->get('test', $this->refinery->to()->string()));
    }

    public function testLock(): void
    {
        $services = new Services($this->getConfig());
        $container = $services->get($this->getDummyRequest(self::TEST_CONTAINER));

        $container->set('test', 'test_value');
        $this->assertTrue($container->has('test'));
        $this->assertEquals('test_value', $container->get('test', $this->refinery->to()->string()));

        $container->lock(1 / 1000);
        $this->assertTrue($container->isLocked());
        $this->assertFalse($container->has('test'));
        $this->assertNull($container->get('test', $this->refinery->to()->string()));
        usleep(1000);
        $this->assertFalse($container->isLocked());
        $this->assertTrue($container->has('test'));
        $this->assertEquals('test_value', $container->get('test', $this->refinery->to()->string()));

        // Second Run
        $container->lock(1 / 1000);
        $this->assertTrue($container->isLocked());
        $this->assertFalse($container->has('test'));
        $this->assertNull($container->get('test', $this->refinery->to()->string()));
        usleep(100);
        $this->assertTrue($container->isLocked());
        $this->assertFalse($container->has('test'));
        $this->assertNull($container->get('test', $this->refinery->to()->string()));

        usleep(5000); // to avoid problems with the next test
        $this->assertFalse($container->isLocked());
    }

    private function getInvalidLockTimes(): array
    {
        return [
            [-10],
            [-1],
            [301],
            [300.1],
        ];
    }

    /**
     * @dataProvider getInvalidLockTimes
     */
    public function testInvalidLockTimes(float|int $time): void
    {
        $services = new Services($this->getConfig());
        $container = $services->get($this->getDummyRequest(self::TEST_CONTAINER));
        $this->expectException(\InvalidArgumentException::class);
        $container->lock($time);
    }

    public function testDelete(): void
    {
        $services = new Services($this->getConfig());
        $container = $services->get($this->getDummyRequest(self::TEST_CONTAINER));

        $container->set('test', 'test_value');
        $this->assertTrue($container->has('test'));
        $this->assertEquals('test_value', $container->get('test', $this->refinery->to()->string()));

        $container->delete('test');
        $this->assertFalse($container->has('test'));
        $this->assertNull($container->get('test', $this->refinery->to()->string()));
    }

    public function testDefaultAdaptor(): void
    {
        $request = $this->getDummyRequest(self::TEST_CONTAINER);

        $to_string = $this->refinery->kindlyTo()->string();

        $config = $this->getConfig();

        $static = new PHPStatic($config);
        $this->assertTrue($static->isAvailable());

        $services = new Services($config);
        $container = $services->get($request);
        $this->assertInstanceOf(ActiveContainer::class, $container);
        $this->assertEquals(Config::PHPSTATIC, $container->getAdaptorName());
        $this->assertEquals(self::TEST_CONTAINER, $container->getContainerName());

        $this->assertFalse($container->has('test'));
        $container->set('test', 'test_value');
        $this->assertTrue($container->has('test'));
        $this->assertEquals('test_value', $container->get('test', $to_string));
        $container->delete('test');
        $this->assertFalse($container->has('test'));

        $container->set('test2', 'test_value2');
        $container->set('test3', 'test_value3');
        $this->assertTrue($container->has('test2'));
        $this->assertTrue($container->has('test3'));

        $container->flush();

        $this->assertFalse($container->has('test2'));
        $this->assertFalse($container->has('test3'));
    }

    public function testFlush(): void
    {
        $services = new Services($this->getConfig());
        $container = $services->get($this->getDummyRequest(self::TEST_CONTAINER));

        $container->set('test', 'test_value');
        $this->assertTrue($container->has('test'));
        $this->assertEquals('test_value', $container->get('test', $this->refinery->to()->string()));

        $container->flush();
        $this->assertFalse($container->has('test'));
        $this->assertNull($container->get('test', $this->refinery->to()->string()));

        $container->set('test', 'test_value');
        $this->assertTrue($container->has('test'));
        $this->assertEquals('test_value', $container->get('test', $this->refinery->to()->string()));

        $services->flushAdapter();
        $this->assertFalse($container->has('test'));
        $this->assertNull($container->get('test', $this->refinery->to()->string()));
    }

    public function testAPCAdapter(): void
    {
        $config = $this->getConfig(Config::APCU);
        $services = new Services($config);
        $container = $services->get($this->getDummyRequest(self::TEST_CONTAINER));
        $this->assertEquals(Config::APCU, $container->getAdaptorName());
        $this->assertEquals(self::TEST_CONTAINER, $container->getContainerName());

        $apcu = new APCu($config);
        if (!$apcu->isAvailable() || !(bool) ini_get('apc.enable_cli')) {
            $this->markTestSkipped('APCu is not available or not enabled for CLI');
        }

        $to_string = $this->refinery->kindlyTo()->string();

        $this->assertFalse($container->has('test'));
        $container->set('test', 'test_value');
        $this->assertTrue($container->has('test'));
        $this->assertEquals('test_value', $container->get('test', $to_string));
        $container->delete('test');
        $this->assertFalse($container->has('test'));

        $container->set('test2', 'test_value2');
        $container->set('test3', 'test_value3');
        $this->assertTrue($container->has('test2'));
        $this->assertTrue($container->has('test3'));

        $container->flush();

        $this->assertFalse($container->has('test2'));
        $this->assertFalse($container->has('test3'));
    }

    public function testMemcachedAdapter(): void
    {
        $config = $this->getConfig(Config::MEMCACHED);
        $services = new Services($config);
        $container = $services->get($this->getDummyRequest(self::TEST_CONTAINER));
        $this->assertEquals(Config::MEMCACHED, $container->getAdaptorName());
        $this->assertEquals(self::TEST_CONTAINER, $container->getContainerName());

        $apcu = new Memcached($config);
        if (!$apcu->isAvailable()) {
            $this->markTestSkipped('Memcached is not available');
        }

        $this->assertFalse($container->has('test'));
        $container->set('test', 'test_value');
        $this->assertTrue($container->has('test'));
        $this->assertEquals('test_value', $container->get('test'));
        $container->delete('test');
        $this->assertFalse($container->has('test'));

        $container->set('test2', 'test_value2');
        $container->set('test3', 'test_value3');
        $this->assertTrue($container->has('test2'));
        $this->assertTrue($container->has('test3'));

        $container->flush();

        $this->assertFalse($container->has('test2'));
        $this->assertFalse($container->has('test3'));
    }

    public function testObjectStorage(): void
    {
        $services = new Services($this->getConfig());
        $container = $services->get($this->getDummyRequest(self::TEST_CONTAINER));

        $first_object = new \stdClass();
        $first_object->test = 'test';
        $container->set('first_object', serialize($first_object));

        $this->assertTrue($container->has('first_object'));

        $to_string = $this->refinery->kindlyTo()->string();

        $data = $container->get('first_object', $to_string);
        $first_object_from_cache = unserialize($data, ['allowed_classes' => [\stdClass::class]]);
        $this->assertInstanceOf(\stdClass::class, $first_object_from_cache);
        $this->assertEquals($first_object, $first_object_from_cache);
    }

    public function testTypes(): void
    {
        $services = new Services($this->getConfig());
        $container = $services->get($this->getDummyRequest(self::TEST_CONTAINER));

        // TRANSFORMATION
        $to_string = $this->refinery->to()->string();
        $to_int = $this->refinery->to()->int();
        $to_array = $this->refinery->to()->listOf($to_string);
        $to_bool = $this->refinery->to()->bool();

        // STRING
        $string = 'test';
        $container->set('string', $string);
        $this->assertTrue($container->has('string'));
        $string_from_cache = $container->get('string', $to_string);
        $this->assertEquals($string, $string_from_cache);
        $this->assertEquals(null, $container->get('string', $to_int));
        $this->assertEquals(null, $container->get('string', $to_bool));
        $this->assertEquals(null, $container->get('string', $to_array));
        $this->assertIsString($string_from_cache);

        // ARRAY
        $array = ['test', 'test2', 'test3'];
        $container->set('array', $array);
        $this->assertTrue($container->has('array'));
        $array_from_cache = $container->get('array', $to_array);
        $this->assertEquals($array, $array_from_cache);
        $this->assertEquals(null, $container->get('array', $to_int));
        $this->assertEquals(null, $container->get('array', $to_bool));
        $this->assertEquals(null, $container->get('array', $to_string));
        $this->assertIsArray($array_from_cache);

        // BOOL
        $bool = true;
        $container->set('bool', $bool);
        $this->assertTrue($container->has('bool'));
        $bool_from_cache = $container->get('bool', $to_bool);
        $this->assertEquals($bool, $bool_from_cache);
        $this->assertEquals(null, $container->get('bool', $to_int));
        $this->assertEquals(null, $container->get('bool', $to_array));
        $this->assertEquals(null, $container->get('bool', $to_string));
        $this->assertIsBool($bool_from_cache);

        // ARRAY Different values
        $array_with_different_values = ['test' => true, 'test2' => 123, 'test3' => ['test' => 'test'], 'test4' => null];
        $container->set('array_with_different_values', $array_with_different_values);
        $this->assertTrue($container->has('array_with_different_values'));

        $trafo = $this->refinery->to()->dictOf(
            $this->refinery->custom()->transformation(
                function ($value) {
                    return $value;
                },
                function ($value) {
                    return $value;
                }
            )
        );

        $array_with_different_values_from_cache = $container->get('array_with_different_values', $trafo);
        $this->assertEquals($array_with_different_values, $array_with_different_values_from_cache);
        $this->assertIsArray($array_with_different_values_from_cache);
        $this->assertIsBool($array_with_different_values_from_cache['test']);
        $this->assertIsInt($array_with_different_values_from_cache['test2']);
        $this->assertIsArray($array_with_different_values_from_cache['test3']);
        $this->assertNull($array_with_different_values_from_cache['test4']);

        $this->assertEquals(null, $container->get('array_with_different_values', $to_int));
        $this->assertEquals(null, $container->get('array_with_different_values', $to_bool));
        $this->assertEquals(null, $container->get('array_with_different_values', $to_string));

        // INT
        $int = 123;
        $container->set('int', $int);
        $this->assertTrue($container->has('int'));
        $int_from_cache = $container->get('int', $to_int);
        $this->assertEquals($int, $int_from_cache);
        $this->assertEquals(null, $container->get('int', $to_string));
        $this->assertEquals(null, $container->get('int', $to_bool));
        $this->assertEquals(null, $container->get('int', $to_array));
        $this->assertIsInt($int_from_cache);
    }

    public function testIncomatibleType(): void
    {
        $services = new Services($this->getConfig());
        $container = $services->get($this->getDummyRequest(self::TEST_CONTAINER));

        $this->expectException(\TypeError::class);
        $container->set('test', new \stdClass());

        $array_with_incompatible_type_in_it = [
            'test' => 'test',
            'test2' => 'test2',
            'test3' => ['test' => new \stdClass()]
        ];
        $this->expectException(\TypeError::class);
        $container->set('array_with_incompatible_type_in_it', $array_with_incompatible_type_in_it);
    }

    public function testIncomatibleTypeNested(): void
    {
        $services = new Services($this->getConfig());
        $container = $services->get($this->getDummyRequest(self::TEST_CONTAINER));

        $array_with_incompatible_type_in_it = [
            'test' => 'test',
            'test2' => 'test2',
            'test3' => ['test' => new \stdClass()]
        ];
        $this->expectException(\InvalidArgumentException::class);
        $container->set('array_with_incompatible_type_in_it', $array_with_incompatible_type_in_it);
    }

    protected function getDummyRequest(string $container_key): Request
    {
        return new BaseRequest($container_key);
    }
}
