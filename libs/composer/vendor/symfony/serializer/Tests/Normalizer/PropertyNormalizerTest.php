<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Serializer\Tests\Normalizer;

use Doctrine\Common\Annotations\AnnotationReader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Tests\Fixtures\GroupDummy;
use Symfony\Component\Serializer\Tests\Fixtures\GroupDummyChild;
use Symfony\Component\Serializer\Tests\Fixtures\MaxDepthDummy;
use Symfony\Component\Serializer\Tests\Fixtures\PropertyCircularReferenceDummy;
use Symfony\Component\Serializer\Tests\Fixtures\PropertySiblingHolder;

class PropertyNormalizerTest extends TestCase
{
    /**
     * @var PropertyNormalizer
     */
    private $normalizer;
    /**
     * @var SerializerInterface
     */
    private $serializer;

    protected function setUp()
    {
        $this->createNormalizer();
    }

    private function createNormalizer(array $defaultContext = [])
    {
        $this->serializer = $this->getMockBuilder('Symfony\Component\Serializer\SerializerInterface')->getMock();
        $this->normalizer = new PropertyNormalizer(null, null, null, null, null, $defaultContext);
        $this->normalizer->setSerializer($this->serializer);
    }

    public function testNormalize()
    {
        $obj = new PropertyDummy();
        $obj->foo = 'foo';
        $obj->setBar('bar');
        $obj->setCamelCase('camelcase');
        $this->assertEquals(
            ['foo' => 'foo', 'bar' => 'bar', 'camelCase' => 'camelcase'],
            $this->normalizer->normalize($obj, 'any')
        );
    }

    public function testDenormalize()
    {
        $obj = $this->normalizer->denormalize(
            ['foo' => 'foo', 'bar' => 'bar'],
            __NAMESPACE__.'\PropertyDummy',
            'any'
        );
        $this->assertEquals('foo', $obj->foo);
        $this->assertEquals('bar', $obj->getBar());
    }

    public function testNormalizeWithParentClass()
    {
        $group = new GroupDummyChild();
        $group->setBaz('baz');
        $group->setFoo('foo');
        $group->setBar('bar');
        $group->setKevin('Kevin');
        $group->setCoopTilleuls('coop');
        $this->assertEquals(
            ['foo' => 'foo', 'bar' => 'bar', 'kevin' => 'Kevin', 'coopTilleuls' => 'coop', 'fooBar' => null, 'symfony' => null, 'baz' => 'baz'],
            $this->normalizer->normalize($group, 'any')
        );
    }

    public function testDenormalizeWithParentClass()
    {
        $obj = $this->normalizer->denormalize(
            ['foo' => 'foo', 'bar' => 'bar', 'kevin' => 'Kevin', 'baz' => 'baz'],
            GroupDummyChild::class,
            'any'
        );
        $this->assertEquals('foo', $obj->getFoo());
        $this->assertEquals('bar', $obj->getBar());
        $this->assertEquals('Kevin', $obj->getKevin());
        $this->assertEquals('baz', $obj->getBaz());
        $this->assertNull($obj->getSymfony());
    }

    public function testConstructorDenormalize()
    {
        $obj = $this->normalizer->denormalize(
            ['foo' => 'foo', 'bar' => 'bar'],
            __NAMESPACE__.'\PropertyConstructorDummy',
            'any'
        );
        $this->assertEquals('foo', $obj->getFoo());
        $this->assertEquals('bar', $obj->getBar());
    }

    public function testConstructorDenormalizeWithNullArgument()
    {
        $obj = $this->normalizer->denormalize(
            ['foo' => null, 'bar' => 'bar'],
            __NAMESPACE__.'\PropertyConstructorDummy', '
            any'
        );
        $this->assertNull($obj->getFoo());
        $this->assertEquals('bar', $obj->getBar());
    }

    /**
     * @dataProvider provideCallbacks
     */
    public function testCallbacks($callbacks, $value, $result, $message)
    {
        $this->doTestCallbacks($callbacks, $value, $result, $message);
    }

    /**
     * @dataProvider provideCallbacks
     */
    public function testLegacyCallbacks($callbacks, $value, $result, $message)
    {
        $this->doTestCallbacks($callbacks, $value, $result, $message, true);
    }

    private function doTestCallbacks($callbacks, $value, $result, $message, bool $legacy = false)
    {
        $legacy ? $this->normalizer->setCallbacks($callbacks) : $this->createNormalizer([PropertyNormalizer::CALLBACKS => $callbacks]);

        $obj = new PropertyConstructorDummy('', $value);

        $this->assertEquals(
            $result,
            $this->normalizer->normalize($obj, 'any'),
            $message
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testUncallableCallbacks()
    {
        $this->doTestUncallableCallbacks();
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testLegacyUncallableCallbacks()
    {
        $this->doTestUncallableCallbacks(true);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    private function doTestUncallableCallbacks(bool $legacy = false)
    {
        $callbacks = ['bar' => null];
        $legacy ? $this->normalizer->setCallbacks($callbacks) : $this->createNormalizer([PropertyNormalizer::CALLBACKS => $callbacks]);

        $obj = new PropertyConstructorDummy('baz', 'quux');

        $this->normalizer->normalize($obj, 'any');
    }

    public function testIgnoredAttributes()
    {
        $this->doTestIgnoredAttributes();
    }

    public function testLegacyIgnoredAttributes()
    {
        $this->doTestIgnoredAttributes(true);
    }

    private function doTestIgnoredAttributes(bool $legacy = false)
    {
        $ignoredAttributes = ['foo', 'bar', 'camelCase'];
        $legacy ? $this->normalizer->setIgnoredAttributes($ignoredAttributes) : $this->createNormalizer([PropertyNormalizer::IGNORED_ATTRIBUTES => $ignoredAttributes]);

        $obj = new PropertyDummy();
        $obj->foo = 'foo';
        $obj->setBar('bar');

        $this->assertEquals(
            [],
            $this->normalizer->normalize($obj, 'any')
        );
    }

    public function testGroupsNormalize()
    {
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $this->normalizer = new PropertyNormalizer($classMetadataFactory);
        $this->normalizer->setSerializer($this->serializer);

        $obj = new GroupDummy();
        $obj->setFoo('foo');
        $obj->setBar('bar');
        $obj->setFooBar('fooBar');
        $obj->setSymfony('symfony');
        $obj->setKevin('kevin');
        $obj->setCoopTilleuls('coopTilleuls');

        $this->assertEquals([
            'bar' => 'bar',
        ], $this->normalizer->normalize($obj, null, [PropertyNormalizer::GROUPS => ['c']]));

        // The PropertyNormalizer is also able to hydrate properties from parent classes
        $this->assertEquals([
            'symfony' => 'symfony',
            'foo' => 'foo',
            'fooBar' => 'fooBar',
            'bar' => 'bar',
            'kevin' => 'kevin',
            'coopTilleuls' => 'coopTilleuls',
        ], $this->normalizer->normalize($obj, null, [PropertyNormalizer::GROUPS => ['a', 'c']]));
    }

    public function testGroupsDenormalize()
    {
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $this->normalizer = new PropertyNormalizer($classMetadataFactory);
        $this->normalizer->setSerializer($this->serializer);

        $obj = new GroupDummy();
        $obj->setFoo('foo');

        $toNormalize = ['foo' => 'foo', 'bar' => 'bar'];

        $normalized = $this->normalizer->denormalize(
            $toNormalize,
            'Symfony\Component\Serializer\Tests\Fixtures\GroupDummy',
            null,
            [PropertyNormalizer::GROUPS => ['a']]
        );
        $this->assertEquals($obj, $normalized);

        $obj->setBar('bar');

        $normalized = $this->normalizer->denormalize(
            $toNormalize,
            'Symfony\Component\Serializer\Tests\Fixtures\GroupDummy',
            null,
            [PropertyNormalizer::GROUPS => ['a', 'b']]
        );
        $this->assertEquals($obj, $normalized);
    }

    public function testGroupsNormalizeWithNameConverter()
    {
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $this->normalizer = new PropertyNormalizer($classMetadataFactory, new CamelCaseToSnakeCaseNameConverter());
        $this->normalizer->setSerializer($this->serializer);

        $obj = new GroupDummy();
        $obj->setFooBar('@dunglas');
        $obj->setSymfony('@coopTilleuls');
        $obj->setCoopTilleuls('les-tilleuls.coop');

        $this->assertEquals(
            [
                'bar' => null,
                'foo_bar' => '@dunglas',
                'symfony' => '@coopTilleuls',
            ],
            $this->normalizer->normalize($obj, null, [PropertyNormalizer::GROUPS => ['name_converter']])
        );
    }

    public function testGroupsDenormalizeWithNameConverter()
    {
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $this->normalizer = new PropertyNormalizer($classMetadataFactory, new CamelCaseToSnakeCaseNameConverter());
        $this->normalizer->setSerializer($this->serializer);

        $obj = new GroupDummy();
        $obj->setFooBar('@dunglas');
        $obj->setSymfony('@coopTilleuls');

        $this->assertEquals(
            $obj,
            $this->normalizer->denormalize([
                'bar' => null,
                'foo_bar' => '@dunglas',
                'symfony' => '@coopTilleuls',
                'coop_tilleuls' => 'les-tilleuls.coop',
            ], 'Symfony\Component\Serializer\Tests\Fixtures\GroupDummy', null, [PropertyNormalizer::GROUPS => ['name_converter']])
        );
    }

    public function provideCallbacks()
    {
        return [
            [
                [
                    'bar' => function ($bar) {
                        return 'baz';
                    },
                ],
                'baz',
                ['foo' => '', 'bar' => 'baz'],
                'Change a string',
            ],
            [
                [
                    'bar' => function ($bar) {
                    },
                ],
                'baz',
                ['foo' => '', 'bar' => null],
                'Null an item',
            ],
            [
                [
                    'bar' => function ($bar) {
                        return $bar->format('d-m-Y H:i:s');
                    },
                ],
                new \DateTime('2011-09-10 06:30:00'),
                ['foo' => '', 'bar' => '10-09-2011 06:30:00'],
                'Format a date',
            ],
            [
                [
                    'bar' => function ($bars) {
                        $foos = '';
                        foreach ($bars as $bar) {
                            $foos .= $bar->getFoo();
                        }

                        return $foos;
                    },
                ],
                [new PropertyConstructorDummy('baz', ''), new PropertyConstructorDummy('quux', '')],
                ['foo' => '', 'bar' => 'bazquux'],
                'Collect a property',
            ],
            [
                [
                    'bar' => function ($bars) {
                        return \count($bars);
                    },
                ],
                [new PropertyConstructorDummy('baz', ''), new PropertyConstructorDummy('quux', '')],
                ['foo' => '', 'bar' => 2],
                'Count a property',
            ],
        ];
    }

    /**
     * @expectedException \Symfony\Component\Serializer\Exception\CircularReferenceException
     */
    public function testUnableToNormalizeCircularReference()
    {
        $this->doTestUnableToNormalizeCircularReference();
    }

    /**
     * @expectedException \Symfony\Component\Serializer\Exception\CircularReferenceException
     */
    public function testLegacyUnableToNormalizeCircularReference()
    {
        $this->doTestUnableToNormalizeCircularReference(true);
    }

    private function doTestUnableToNormalizeCircularReference(bool $legacy = false)
    {
        $legacy ? $this->normalizer->setCircularReferenceLimit(2) : $this->createNormalizer([PropertyNormalizer::CIRCULAR_REFERENCE_LIMIT => 2]);
        $this->serializer = new Serializer([$this->normalizer]);
        $this->normalizer->setSerializer($this->serializer);

        $obj = new PropertyCircularReferenceDummy();

        $this->normalizer->normalize($obj);
    }

    public function testSiblingReference()
    {
        $serializer = new Serializer([$this->normalizer]);
        $this->normalizer->setSerializer($serializer);

        $siblingHolder = new PropertySiblingHolder();

        $expected = [
            'sibling0' => ['coopTilleuls' => 'Les-Tilleuls.coop'],
            'sibling1' => ['coopTilleuls' => 'Les-Tilleuls.coop'],
            'sibling2' => ['coopTilleuls' => 'Les-Tilleuls.coop'],
        ];
        $this->assertEquals($expected, $this->normalizer->normalize($siblingHolder));
    }

    public function testCircularReferenceHandler()
    {
        $this->doTestCircularReferenceHandler();
    }

    public function testLegacyCircularReferenceHandler()
    {
        $this->doTestCircularReferenceHandler(true);
    }

    private function doTestCircularReferenceHandler(bool $legacy = false)
    {
        $handler = function ($obj) {
            return \get_class($obj);
        };
        $legacy ? $this->normalizer->setCircularReferenceHandler($handler) : $this->createNormalizer([PropertyNormalizer::CIRCULAR_REFERENCE_HANDLER => $handler]);

        $this->serializer = new Serializer([$this->normalizer]);
        $this->normalizer->setSerializer($this->serializer);

        $obj = new PropertyCircularReferenceDummy();

        $expected = ['me' => 'Symfony\Component\Serializer\Tests\Fixtures\PropertyCircularReferenceDummy'];
        $this->assertEquals($expected, $this->normalizer->normalize($obj));
    }

    public function testDenormalizeNonExistingAttribute()
    {
        $this->assertEquals(
            new PropertyDummy(),
            $this->normalizer->denormalize(['non_existing' => true], __NAMESPACE__.'\PropertyDummy')
        );
    }

    public function testDenormalizeShouldIgnoreStaticProperty()
    {
        $obj = $this->normalizer->denormalize(['outOfScope' => true], __NAMESPACE__.'\PropertyDummy');

        $this->assertEquals(new PropertyDummy(), $obj);
        $this->assertEquals('out_of_scope', PropertyDummy::$outOfScope);
    }

    /**
     * @expectedException \Symfony\Component\Serializer\Exception\LogicException
     * @expectedExceptionMessage Cannot normalize attribute "bar" because the injected serializer is not a normalizer
     */
    public function testUnableToNormalizeObjectAttribute()
    {
        $serializer = $this->getMockBuilder('Symfony\Component\Serializer\SerializerInterface')->getMock();
        $this->normalizer->setSerializer($serializer);

        $obj = new PropertyDummy();
        $object = new \stdClass();
        $obj->setBar($object);

        $this->normalizer->normalize($obj, 'any');
    }

    public function testNoTraversableSupport()
    {
        $this->assertFalse($this->normalizer->supportsNormalization(new \ArrayObject()));
    }

    public function testNoStaticPropertySupport()
    {
        $this->assertFalse($this->normalizer->supportsNormalization(new StaticPropertyDummy()));
    }

    public function testMaxDepth()
    {
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $this->normalizer = new PropertyNormalizer($classMetadataFactory);
        $serializer = new Serializer([$this->normalizer]);
        $this->normalizer->setSerializer($serializer);

        $level1 = new MaxDepthDummy();
        $level1->foo = 'level1';

        $level2 = new MaxDepthDummy();
        $level2->foo = 'level2';
        $level1->child = $level2;

        $level3 = new MaxDepthDummy();
        $level3->foo = 'level3';
        $level2->child = $level3;

        $result = $serializer->normalize($level1, null, [PropertyNormalizer::ENABLE_MAX_DEPTH => true]);

        $expected = [
            'foo' => 'level1',
            'child' => [
                    'foo' => 'level2',
                    'child' => [
                            'child' => null,
                            'bar' => null,
                        ],
                    'bar' => null,
                ],
            'bar' => null,
        ];

        $this->assertEquals($expected, $result);
    }

    public function testInheritedPropertiesSupport()
    {
        $this->assertTrue($this->normalizer->supportsNormalization(new PropertyChildDummy()));
    }
}

class PropertyDummy
{
    public static $outOfScope = 'out_of_scope';
    public $foo;
    private $bar;
    protected $camelCase;

    public function getBar()
    {
        return $this->bar;
    }

    public function setBar($bar)
    {
        $this->bar = $bar;
    }

    public function getCamelCase()
    {
        return $this->camelCase;
    }

    public function setCamelCase($camelCase)
    {
        $this->camelCase = $camelCase;
    }
}

class PropertyConstructorDummy
{
    protected $foo;
    private $bar;

    public function __construct($foo, $bar)
    {
        $this->foo = $foo;
        $this->bar = $bar;
    }

    public function getFoo()
    {
        return $this->foo;
    }

    public function getBar()
    {
        return $this->bar;
    }
}

class PropertyCamelizedDummy
{
    private $kevinDunglas;
    public $fooBar;
    public $bar_foo;

    public function __construct($kevinDunglas = null)
    {
        $this->kevinDunglas = $kevinDunglas;
    }
}

class StaticPropertyDummy
{
    private static $property = 'value';
}

class PropertyParentDummy
{
    private $foo = 'bar';
}

class PropertyChildDummy extends PropertyParentDummy
{
}
