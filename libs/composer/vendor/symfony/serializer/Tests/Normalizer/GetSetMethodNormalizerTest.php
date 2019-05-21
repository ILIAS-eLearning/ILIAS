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
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Tests\Fixtures\CircularReferenceDummy;
use Symfony\Component\Serializer\Tests\Fixtures\GroupDummy;
use Symfony\Component\Serializer\Tests\Fixtures\MaxDepthDummy;
use Symfony\Component\Serializer\Tests\Fixtures\SiblingHolder;

class GetSetMethodNormalizerTest extends TestCase
{
    /**
     * @var GetSetMethodNormalizer
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
        $this->serializer = $this->getMockBuilder(__NAMESPACE__.'\SerializerNormalizer')->getMock();
        $this->normalizer = new GetSetMethodNormalizer(null, null, null, null, null, $defaultContext);
        $this->normalizer->setSerializer($this->serializer);
    }

    public function testInterface()
    {
        $this->assertInstanceOf('Symfony\Component\Serializer\Normalizer\NormalizerInterface', $this->normalizer);
        $this->assertInstanceOf('Symfony\Component\Serializer\Normalizer\DenormalizerInterface', $this->normalizer);
    }

    public function testNormalize()
    {
        $obj = new GetSetDummy();
        $object = new \stdClass();
        $obj->setFoo('foo');
        $obj->setBar('bar');
        $obj->setBaz(true);
        $obj->setCamelCase('camelcase');
        $obj->setObject($object);

        $this->serializer
            ->expects($this->once())
            ->method('normalize')
            ->with($object, 'any')
            ->will($this->returnValue('string_object'))
        ;

        $this->assertEquals(
            [
                'foo' => 'foo',
                'bar' => 'bar',
                'baz' => true,
                'fooBar' => 'foobar',
                'camelCase' => 'camelcase',
                'object' => 'string_object',
            ],
            $this->normalizer->normalize($obj, 'any')
        );
    }

    public function testDenormalize()
    {
        $obj = $this->normalizer->denormalize(
            ['foo' => 'foo', 'bar' => 'bar', 'baz' => true, 'fooBar' => 'foobar'],
            __NAMESPACE__.'\GetSetDummy',
            'any'
        );
        $this->assertEquals('foo', $obj->getFoo());
        $this->assertEquals('bar', $obj->getBar());
        $this->assertTrue($obj->isBaz());
    }

    public function testIgnoredAttributesInContext()
    {
        $ignoredAttributes = ['foo', 'bar', 'baz', 'object'];
        $obj = new GetSetDummy();
        $obj->setFoo('foo');
        $obj->setBar('bar');
        $obj->setCamelCase(true);
        $this->assertEquals(
            [
                'fooBar' => 'foobar',
                'camelCase' => true,
            ],
            $this->normalizer->normalize($obj, 'any', [AbstractNormalizer::IGNORED_ATTRIBUTES => $ignoredAttributes])
        );
    }

    public function testDenormalizeWithObject()
    {
        $data = new \stdClass();
        $data->foo = 'foo';
        $data->bar = 'bar';
        $data->fooBar = 'foobar';
        $obj = $this->normalizer->denormalize($data, __NAMESPACE__.'\GetSetDummy', 'any');
        $this->assertEquals('foo', $obj->getFoo());
        $this->assertEquals('bar', $obj->getBar());
    }

    public function testDenormalizeNull()
    {
        $this->assertEquals(new GetSetDummy(), $this->normalizer->denormalize(null, __NAMESPACE__.'\GetSetDummy'));
    }

    public function testConstructorDenormalize()
    {
        $obj = $this->normalizer->denormalize(
            ['foo' => 'foo', 'bar' => 'bar', 'baz' => true, 'fooBar' => 'foobar'],
            __NAMESPACE__.'\GetConstructorDummy', 'any');
        $this->assertEquals('foo', $obj->getFoo());
        $this->assertEquals('bar', $obj->getBar());
        $this->assertTrue($obj->isBaz());
    }

    public function testConstructorDenormalizeWithNullArgument()
    {
        $obj = $this->normalizer->denormalize(
            ['foo' => 'foo', 'bar' => null, 'baz' => true],
            __NAMESPACE__.'\GetConstructorDummy', 'any');
        $this->assertEquals('foo', $obj->getFoo());
        $this->assertNull($obj->getBar());
        $this->assertTrue($obj->isBaz());
    }

    public function testConstructorDenormalizeWithMissingOptionalArgument()
    {
        $obj = $this->normalizer->denormalize(
            ['foo' => 'test', 'baz' => [1, 2, 3]],
            __NAMESPACE__.'\GetConstructorOptionalArgsDummy', 'any');
        $this->assertEquals('test', $obj->getFoo());
        $this->assertEquals([], $obj->getBar());
        $this->assertEquals([1, 2, 3], $obj->getBaz());
    }

    public function testConstructorDenormalizeWithOptionalDefaultArgument()
    {
        $obj = $this->normalizer->denormalize(
            ['bar' => 'test'],
            __NAMESPACE__.'\GetConstructorArgsWithDefaultValueDummy', 'any');
        $this->assertEquals([], $obj->getFoo());
        $this->assertEquals('test', $obj->getBar());
    }

    public function testConstructorDenormalizeWithVariadicArgument()
    {
        $obj = $this->normalizer->denormalize(
            ['foo' => [1, 2, 3]],
            'Symfony\Component\Serializer\Tests\Fixtures\VariadicConstructorArgsDummy', 'any');
        $this->assertEquals([1, 2, 3], $obj->getFoo());
    }

    public function testConstructorDenormalizeWithMissingVariadicArgument()
    {
        $obj = $this->normalizer->denormalize(
            [],
            'Symfony\Component\Serializer\Tests\Fixtures\VariadicConstructorArgsDummy', 'any');
        $this->assertEquals([], $obj->getFoo());
    }

    public function testConstructorWithObjectDenormalize()
    {
        $data = new \stdClass();
        $data->foo = 'foo';
        $data->bar = 'bar';
        $data->baz = true;
        $data->fooBar = 'foobar';
        $obj = $this->normalizer->denormalize($data, __NAMESPACE__.'\GetConstructorDummy', 'any');
        $this->assertEquals('foo', $obj->getFoo());
        $this->assertEquals('bar', $obj->getBar());
    }

    public function testConstructorWArgWithPrivateMutator()
    {
        $obj = $this->normalizer->denormalize(['foo' => 'bar'], __NAMESPACE__.'\ObjectConstructorArgsWithPrivateMutatorDummy', 'any');
        $this->assertEquals('bar', $obj->getFoo());
    }

    public function testGroupsNormalize()
    {
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $this->normalizer = new GetSetMethodNormalizer($classMetadataFactory);
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
        ], $this->normalizer->normalize($obj, null, [GetSetMethodNormalizer::GROUPS => ['c']]));

        $this->assertEquals([
            'symfony' => 'symfony',
            'foo' => 'foo',
            'fooBar' => 'fooBar',
            'bar' => 'bar',
            'kevin' => 'kevin',
            'coopTilleuls' => 'coopTilleuls',
        ], $this->normalizer->normalize($obj, null, [GetSetMethodNormalizer::GROUPS => ['a', 'c']]));
    }

    public function testGroupsDenormalize()
    {
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $this->normalizer = new GetSetMethodNormalizer($classMetadataFactory);
        $this->normalizer->setSerializer($this->serializer);

        $obj = new GroupDummy();
        $obj->setFoo('foo');

        $toNormalize = ['foo' => 'foo', 'bar' => 'bar'];

        $normalized = $this->normalizer->denormalize(
            $toNormalize,
            'Symfony\Component\Serializer\Tests\Fixtures\GroupDummy',
            null,
            [GetSetMethodNormalizer::GROUPS => ['a']]
        );
        $this->assertEquals($obj, $normalized);

        $obj->setBar('bar');

        $normalized = $this->normalizer->denormalize(
            $toNormalize,
            'Symfony\Component\Serializer\Tests\Fixtures\GroupDummy',
            null,
            [GetSetMethodNormalizer::GROUPS => ['a', 'b']]
        );
        $this->assertEquals($obj, $normalized);
    }

    public function testGroupsNormalizeWithNameConverter()
    {
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $this->normalizer = new GetSetMethodNormalizer($classMetadataFactory, new CamelCaseToSnakeCaseNameConverter());
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
            $this->normalizer->normalize($obj, null, [GetSetMethodNormalizer::GROUPS => ['name_converter']])
        );
    }

    public function testGroupsDenormalizeWithNameConverter()
    {
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $this->normalizer = new GetSetMethodNormalizer($classMetadataFactory, new CamelCaseToSnakeCaseNameConverter());
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
            ], 'Symfony\Component\Serializer\Tests\Fixtures\GroupDummy', null, [GetSetMethodNormalizer::GROUPS => ['name_converter']])
        );
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
        $legacy ? $this->normalizer->setCallbacks($callbacks) : $this->createNormalizer([GetSetMethodNormalizer::CALLBACKS => $callbacks]);

        $obj = new GetConstructorDummy('', $value, true);
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

    private function doTestUncallableCallbacks(bool $legacy = false)
    {
        $callbacks = ['bar' => null];
        $legacy ? $this->normalizer->setCallbacks($callbacks) : $this->createNormalizer([GetSetMethodNormalizer::CALLBACKS => $callbacks]);

        $obj = new GetConstructorDummy('baz', 'quux', true);

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
        $ignoredAttributes = ['foo', 'bar', 'baz', 'camelCase', 'object'];
        $legacy ? $this->normalizer->setIgnoredAttributes($ignoredAttributes) : $this->createNormalizer([GetSetMethodNormalizer::IGNORED_ATTRIBUTES => $ignoredAttributes]);

        $obj = new GetSetDummy();
        $obj->setFoo('foo');
        $obj->setBar('bar');
        $obj->setBaz(true);

        $this->assertEquals(
            ['fooBar' => 'foobar'],
            $this->normalizer->normalize($obj, 'any')
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
                ['foo' => '', 'bar' => 'baz', 'baz' => true],
                'Change a string',
            ],
            [
                [
                    'bar' => function ($bar) {
                    },
                ],
                'baz',
                ['foo' => '', 'bar' => null, 'baz' => true],
                'Null an item',
            ],
            [
                [
                    'bar' => function ($bar) {
                        return $bar->format('d-m-Y H:i:s');
                    },
                ],
                new \DateTime('2011-09-10 06:30:00'),
                ['foo' => '', 'bar' => '10-09-2011 06:30:00', 'baz' => true],
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
                [new GetConstructorDummy('baz', '', false), new GetConstructorDummy('quux', '', false)],
                ['foo' => '', 'bar' => 'bazquux', 'baz' => true],
                'Collect a property',
            ],
            [
                [
                    'bar' => function ($bars) {
                        return \count($bars);
                    },
                ],
                [new GetConstructorDummy('baz', '', false), new GetConstructorDummy('quux', '', false)],
                ['foo' => '', 'bar' => 2, 'baz' => true],
                'Count a property',
            ],
        ];
    }

    /**
     * @expectedException \Symfony\Component\Serializer\Exception\LogicException
     * @expectedExceptionMessage Cannot normalize attribute "object" because the injected serializer is not a normalizer
     */
    public function testUnableToNormalizeObjectAttribute()
    {
        $serializer = $this->getMockBuilder('Symfony\Component\Serializer\SerializerInterface')->getMock();
        $this->normalizer->setSerializer($serializer);

        $obj = new GetSetDummy();
        $object = new \stdClass();
        $obj->setObject($object);

        $this->normalizer->normalize($obj, 'any');
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
        $legacy ? $this->normalizer->setCircularReferenceLimit(2) : $this->createNormalizer([GetSetMethodNormalizer::CIRCULAR_REFERENCE_LIMIT => 2]);
        $this->serializer = new Serializer([$this->normalizer]);
        $this->normalizer->setSerializer($this->serializer);

        $obj = new CircularReferenceDummy();
        $this->normalizer->normalize($obj);
    }

    public function testSiblingReference()
    {
        $serializer = new Serializer([$this->normalizer]);
        $this->normalizer->setSerializer($serializer);

        $siblingHolder = new SiblingHolder();

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

        $legacy ? $this->normalizer->setCircularReferenceHandler($handler) : $this->createNormalizer([GetSetMethodNormalizer::CIRCULAR_REFERENCE_HANDLER => $handler]);
        $this->serializer = new Serializer([$this->normalizer]);
        $this->normalizer->setSerializer($this->serializer);

        $obj = new CircularReferenceDummy();

        $expected = ['me' => 'Symfony\Component\Serializer\Tests\Fixtures\CircularReferenceDummy'];
        $this->assertEquals($expected, $this->normalizer->normalize($obj));
    }

    public function testObjectToPopulate()
    {
        $dummy = new GetSetDummy();
        $dummy->setFoo('foo');

        $obj = $this->normalizer->denormalize(
            ['bar' => 'bar'],
            __NAMESPACE__.'\GetSetDummy',
            null,
            [GetSetMethodNormalizer::OBJECT_TO_POPULATE => $dummy]
        );

        $this->assertEquals($dummy, $obj);
        $this->assertEquals('foo', $obj->getFoo());
        $this->assertEquals('bar', $obj->getBar());
    }

    public function testDenormalizeNonExistingAttribute()
    {
        $this->assertEquals(
            new GetSetDummy(),
            $this->normalizer->denormalize(['non_existing' => true], __NAMESPACE__.'\GetSetDummy')
        );
    }

    public function testDenormalizeShouldNotSetStaticAttribute()
    {
        $obj = $this->normalizer->denormalize(['staticObject' => true], __NAMESPACE__.'\GetSetDummy');

        $this->assertEquals(new GetSetDummy(), $obj);
        $this->assertNull(GetSetDummy::getStaticObject());
    }

    public function testNoTraversableSupport()
    {
        $this->assertFalse($this->normalizer->supportsNormalization(new \ArrayObject()));
    }

    public function testNoStaticGetSetSupport()
    {
        $this->assertFalse($this->normalizer->supportsNormalization(new ObjectWithJustStaticSetterDummy()));
    }

    public function testPrivateSetter()
    {
        $obj = $this->normalizer->denormalize(['foo' => 'foobar'], __NAMESPACE__.'\ObjectWithPrivateSetterDummy');
        $this->assertEquals('bar', $obj->getFoo());
    }

    public function testHasGetterDenormalize()
    {
        $obj = $this->normalizer->denormalize(['foo' => true], ObjectWithHasGetterDummy::class);
        $this->assertTrue($obj->hasFoo());
    }

    public function testHasGetterNormalize()
    {
        $obj = new ObjectWithHasGetterDummy();
        $obj->setFoo(true);

        $this->assertEquals(
            ['foo' => true],
            $this->normalizer->normalize($obj, 'any')
        );
    }

    public function testMaxDepth()
    {
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $this->normalizer = new GetSetMethodNormalizer($classMetadataFactory);
        $serializer = new Serializer([$this->normalizer]);
        $this->normalizer->setSerializer($serializer);

        $level1 = new MaxDepthDummy();
        $level1->bar = 'level1';

        $level2 = new MaxDepthDummy();
        $level2->bar = 'level2';
        $level1->child = $level2;

        $level3 = new MaxDepthDummy();
        $level3->bar = 'level3';
        $level2->child = $level3;

        $level4 = new MaxDepthDummy();
        $level4->bar = 'level4';
        $level3->child = $level4;

        $result = $serializer->normalize($level1, null, [GetSetMethodNormalizer::ENABLE_MAX_DEPTH => true]);

        $expected = [
            'bar' => 'level1',
            'child' => [
                    'bar' => 'level2',
                    'child' => [
                            'bar' => 'level3',
                            'child' => [
                                    'child' => null,
                                ],
                        ],
                ],
            ];

        $this->assertEquals($expected, $result);
    }
}

class GetSetDummy
{
    protected $foo;
    private $bar;
    private $baz;
    protected $camelCase;
    protected $object;
    private static $staticObject;

    public function getFoo()
    {
        return $this->foo;
    }

    public function setFoo($foo)
    {
        $this->foo = $foo;
    }

    public function getBar()
    {
        return $this->bar;
    }

    public function setBar($bar)
    {
        $this->bar = $bar;
    }

    public function isBaz()
    {
        return $this->baz;
    }

    public function setBaz($baz)
    {
        $this->baz = $baz;
    }

    public function getFooBar()
    {
        return $this->foo.$this->bar;
    }

    public function getCamelCase()
    {
        return $this->camelCase;
    }

    public function setCamelCase($camelCase)
    {
        $this->camelCase = $camelCase;
    }

    public function otherMethod()
    {
        throw new \RuntimeException('Dummy::otherMethod() should not be called');
    }

    public function setObject($object)
    {
        $this->object = $object;
    }

    public function getObject()
    {
        return $this->object;
    }

    public static function getStaticObject()
    {
        return self::$staticObject;
    }

    public static function setStaticObject($object)
    {
        self::$staticObject = $object;
    }

    protected function getPrivate()
    {
        throw new \RuntimeException('Dummy::getPrivate() should not be called');
    }
}

class GetConstructorDummy
{
    protected $foo;
    private $bar;
    private $baz;

    public function __construct($foo, $bar, $baz)
    {
        $this->foo = $foo;
        $this->bar = $bar;
        $this->baz = $baz;
    }

    public function getFoo()
    {
        return $this->foo;
    }

    public function getBar()
    {
        return $this->bar;
    }

    public function isBaz()
    {
        return $this->baz;
    }

    public function otherMethod()
    {
        throw new \RuntimeException('Dummy::otherMethod() should not be called');
    }
}

abstract class SerializerNormalizer implements SerializerInterface, NormalizerInterface
{
}

class GetConstructorOptionalArgsDummy
{
    protected $foo;
    private $bar;
    private $baz;

    public function __construct($foo, $bar = [], $baz = [])
    {
        $this->foo = $foo;
        $this->bar = $bar;
        $this->baz = $baz;
    }

    public function getFoo()
    {
        return $this->foo;
    }

    public function getBar()
    {
        return $this->bar;
    }

    public function getBaz()
    {
        return $this->baz;
    }

    public function otherMethod()
    {
        throw new \RuntimeException('Dummy::otherMethod() should not be called');
    }
}

class GetConstructorArgsWithDefaultValueDummy
{
    protected $foo;
    protected $bar;

    public function __construct($foo = [], $bar)
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

    public function otherMethod()
    {
        throw new \RuntimeException('Dummy::otherMethod() should not be called');
    }
}

class GetCamelizedDummy
{
    private $kevinDunglas;
    private $fooBar;
    private $bar_foo;

    public function __construct($kevinDunglas = null)
    {
        $this->kevinDunglas = $kevinDunglas;
    }

    public function getKevinDunglas()
    {
        return $this->kevinDunglas;
    }

    public function setFooBar($fooBar)
    {
        $this->fooBar = $fooBar;
    }

    public function getFooBar()
    {
        return $this->fooBar;
    }

    public function setBar_foo($bar_foo)
    {
        $this->bar_foo = $bar_foo;
    }

    public function getBar_foo()
    {
        return $this->bar_foo;
    }
}

class ObjectConstructorArgsWithPrivateMutatorDummy
{
    private $foo;

    public function __construct($foo)
    {
        $this->setFoo($foo);
    }

    public function getFoo()
    {
        return $this->foo;
    }

    private function setFoo($foo)
    {
        $this->foo = $foo;
    }
}

class ObjectWithPrivateSetterDummy
{
    private $foo = 'bar';

    public function getFoo()
    {
        return $this->foo;
    }

    private function setFoo($foo)
    {
    }
}

class ObjectWithJustStaticSetterDummy
{
    private static $foo = 'bar';

    public static function getFoo()
    {
        return self::$foo;
    }

    public static function setFoo($foo)
    {
        self::$foo = $foo;
    }
}

class ObjectWithHasGetterDummy
{
    private $foo;

    public function setFoo($foo)
    {
        $this->foo = $foo;
    }

    public function hasFoo()
    {
        return $this->foo;
    }
}
