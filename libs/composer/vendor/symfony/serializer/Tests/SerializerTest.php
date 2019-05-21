<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Serializer\Tests;

use Doctrine\Common\Annotations\AnnotationReader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorFromClassMetadata;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorMapping;
use Symfony\Component\Serializer\Mapping\ClassMetadata;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\CustomNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Tests\Fixtures\AbstractDummy;
use Symfony\Component\Serializer\Tests\Fixtures\AbstractDummyFirstChild;
use Symfony\Component\Serializer\Tests\Fixtures\AbstractDummySecondChild;
use Symfony\Component\Serializer\Tests\Fixtures\DummyMessageInterface;
use Symfony\Component\Serializer\Tests\Fixtures\DummyMessageNumberOne;
use Symfony\Component\Serializer\Tests\Fixtures\DummyMessageNumberTwo;
use Symfony\Component\Serializer\Tests\Fixtures\NormalizableTraversableDummy;
use Symfony\Component\Serializer\Tests\Fixtures\TraversableDummy;
use Symfony\Component\Serializer\Tests\Normalizer\TestDenormalizer;
use Symfony\Component\Serializer\Tests\Normalizer\TestNormalizer;

class SerializerTest extends TestCase
{
    public function testInterface()
    {
        $serializer = new Serializer();

        $this->assertInstanceOf('Symfony\Component\Serializer\SerializerInterface', $serializer);
        $this->assertInstanceOf('Symfony\Component\Serializer\Normalizer\NormalizerInterface', $serializer);
        $this->assertInstanceOf('Symfony\Component\Serializer\Normalizer\DenormalizerInterface', $serializer);
        $this->assertInstanceOf('Symfony\Component\Serializer\Encoder\EncoderInterface', $serializer);
        $this->assertInstanceOf('Symfony\Component\Serializer\Encoder\DecoderInterface', $serializer);
    }

    /**
     * @expectedDeprecation Passing normalizers ("stdClass") which do not implement either "Symfony\Component\Serializer\Normalizer\NormalizerInterface" or "Symfony\Component\Serializer\Normalizer\DenormalizerInterface" has been deprecated since Symfony 4.2.
     * @group legacy
     */
    public function testDeprecationErrorOnInvalidNormalizer()
    {
        new Serializer([new \stdClass()]);
    }

    /**
     * @expectedDeprecation Passing encoders ("stdClass") which do not implement either "Symfony\Component\Serializer\Encoder\EncoderInterface" or "Symfony\Component\Serializer\Encoder\DecoderInterface" has been deprecated since Symfony 4.2.
     * @group legacy
     */
    public function testDeprecationErrorOnInvalidEncoder()
    {
        new Serializer([], [new \stdClass()]);
    }

    /**
     * @expectedException \Symfony\Component\Serializer\Exception\UnexpectedValueException
     */
    public function testNormalizeNoMatch()
    {
        $serializer = new Serializer([$this->getMockBuilder('Symfony\Component\Serializer\Normalizer\CustomNormalizer')->getMock()]);
        $serializer->normalize(new \stdClass(), 'xml');
    }

    public function testNormalizeTraversable()
    {
        $serializer = new Serializer([], ['json' => new JsonEncoder()]);
        $result = $serializer->serialize(new TraversableDummy(), 'json');
        $this->assertEquals('{"foo":"foo","bar":"bar"}', $result);
    }

    public function testNormalizeGivesPriorityToInterfaceOverTraversable()
    {
        $serializer = new Serializer([new CustomNormalizer()], ['json' => new JsonEncoder()]);
        $result = $serializer->serialize(new NormalizableTraversableDummy(), 'json');
        $this->assertEquals('{"foo":"normalizedFoo","bar":"normalizedBar"}', $result);
    }

    /**
     * @expectedException \Symfony\Component\Serializer\Exception\UnexpectedValueException
     */
    public function testNormalizeOnDenormalizer()
    {
        $serializer = new Serializer([new TestDenormalizer()], []);
        $this->assertTrue($serializer->normalize(new \stdClass(), 'json'));
    }

    /**
     * @expectedException \Symfony\Component\Serializer\Exception\UnexpectedValueException
     */
    public function testDenormalizeNoMatch()
    {
        $serializer = new Serializer([$this->getMockBuilder('Symfony\Component\Serializer\Normalizer\CustomNormalizer')->getMock()]);
        $serializer->denormalize('foo', 'stdClass');
    }

    /**
     * @expectedException \Symfony\Component\Serializer\Exception\UnexpectedValueException
     */
    public function testDenormalizeOnNormalizer()
    {
        $serializer = new Serializer([new TestNormalizer()], []);
        $data = ['title' => 'foo', 'numbers' => [5, 3]];
        $this->assertTrue($serializer->denormalize(json_encode($data), 'stdClass', 'json'));
    }

    public function testCustomNormalizerCanNormalizeCollectionsAndScalar()
    {
        $serializer = new Serializer([new TestNormalizer()], []);
        $this->assertNull($serializer->normalize(['a', 'b']));
        $this->assertNull($serializer->normalize(new \ArrayObject(['c', 'd'])));
        $this->assertNull($serializer->normalize([]));
        $this->assertNull($serializer->normalize('test'));
    }

    public function testNormalizeWithSupportOnData()
    {
        $normalizer1 = $this->getMockBuilder('Symfony\Component\Serializer\Normalizer\NormalizerInterface')->getMock();
        $normalizer1->method('supportsNormalization')
            ->willReturnCallback(function ($data, $format) {
                return isset($data->test);
            });
        $normalizer1->method('normalize')->willReturn('test1');

        $normalizer2 = $this->getMockBuilder('Symfony\Component\Serializer\Normalizer\NormalizerInterface')->getMock();
        $normalizer2->method('supportsNormalization')
            ->willReturn(true);
        $normalizer2->method('normalize')->willReturn('test2');

        $serializer = new Serializer([$normalizer1, $normalizer2]);

        $data = new \stdClass();
        $data->test = true;
        $this->assertEquals('test1', $serializer->normalize($data));

        $this->assertEquals('test2', $serializer->normalize(new \stdClass()));
    }

    public function testDenormalizeWithSupportOnData()
    {
        $denormalizer1 = $this->getMockBuilder('Symfony\Component\Serializer\Normalizer\DenormalizerInterface')->getMock();
        $denormalizer1->method('supportsDenormalization')
            ->willReturnCallback(function ($data, $type, $format) {
                return isset($data['test1']);
            });
        $denormalizer1->method('denormalize')->willReturn('test1');

        $denormalizer2 = $this->getMockBuilder('Symfony\Component\Serializer\Normalizer\DenormalizerInterface')->getMock();
        $denormalizer2->method('supportsDenormalization')
            ->willReturn(true);
        $denormalizer2->method('denormalize')->willReturn('test2');

        $serializer = new Serializer([$denormalizer1, $denormalizer2]);

        $this->assertEquals('test1', $serializer->denormalize(['test1' => true], 'test'));

        $this->assertEquals('test2', $serializer->denormalize([], 'test'));
    }

    public function testSerialize()
    {
        $serializer = new Serializer([new GetSetMethodNormalizer()], ['json' => new JsonEncoder()]);
        $data = ['title' => 'foo', 'numbers' => [5, 3]];
        $result = $serializer->serialize(Model::fromArray($data), 'json');
        $this->assertEquals(json_encode($data), $result);
    }

    public function testSerializeScalar()
    {
        $serializer = new Serializer([], ['json' => new JsonEncoder()]);
        $result = $serializer->serialize('foo', 'json');
        $this->assertEquals('"foo"', $result);
    }

    public function testSerializeArrayOfScalars()
    {
        $serializer = new Serializer([], ['json' => new JsonEncoder()]);
        $data = ['foo', [5, 3]];
        $result = $serializer->serialize($data, 'json');
        $this->assertEquals(json_encode($data), $result);
    }

    /**
     * @expectedException \Symfony\Component\Serializer\Exception\UnexpectedValueException
     */
    public function testSerializeNoEncoder()
    {
        $serializer = new Serializer([], []);
        $data = ['title' => 'foo', 'numbers' => [5, 3]];
        $serializer->serialize($data, 'json');
    }

    /**
     * @expectedException \Symfony\Component\Serializer\Exception\LogicException
     */
    public function testSerializeNoNormalizer()
    {
        $serializer = new Serializer([], ['json' => new JsonEncoder()]);
        $data = ['title' => 'foo', 'numbers' => [5, 3]];
        $serializer->serialize(Model::fromArray($data), 'json');
    }

    public function testDeserialize()
    {
        $serializer = new Serializer([new GetSetMethodNormalizer()], ['json' => new JsonEncoder()]);
        $data = ['title' => 'foo', 'numbers' => [5, 3]];
        $result = $serializer->deserialize(json_encode($data), '\Symfony\Component\Serializer\Tests\Model', 'json');
        $this->assertEquals($data, $result->toArray());
    }

    public function testDeserializeUseCache()
    {
        $serializer = new Serializer([new GetSetMethodNormalizer()], ['json' => new JsonEncoder()]);
        $data = ['title' => 'foo', 'numbers' => [5, 3]];
        $serializer->deserialize(json_encode($data), '\Symfony\Component\Serializer\Tests\Model', 'json');
        $data = ['title' => 'bar', 'numbers' => [2, 8]];
        $result = $serializer->deserialize(json_encode($data), '\Symfony\Component\Serializer\Tests\Model', 'json');
        $this->assertEquals($data, $result->toArray());
    }

    /**
     * @expectedException \Symfony\Component\Serializer\Exception\LogicException
     */
    public function testDeserializeNoNormalizer()
    {
        $serializer = new Serializer([], ['json' => new JsonEncoder()]);
        $data = ['title' => 'foo', 'numbers' => [5, 3]];
        $serializer->deserialize(json_encode($data), '\Symfony\Component\Serializer\Tests\Model', 'json');
    }

    /**
     * @expectedException \Symfony\Component\Serializer\Exception\UnexpectedValueException
     */
    public function testDeserializeWrongNormalizer()
    {
        $serializer = new Serializer([new CustomNormalizer()], ['json' => new JsonEncoder()]);
        $data = ['title' => 'foo', 'numbers' => [5, 3]];
        $serializer->deserialize(json_encode($data), '\Symfony\Component\Serializer\Tests\Model', 'json');
    }

    /**
     * @expectedException \Symfony\Component\Serializer\Exception\UnexpectedValueException
     */
    public function testDeserializeNoEncoder()
    {
        $serializer = new Serializer([], []);
        $data = ['title' => 'foo', 'numbers' => [5, 3]];
        $serializer->deserialize(json_encode($data), '\Symfony\Component\Serializer\Tests\Model', 'json');
    }

    public function testDeserializeSupported()
    {
        $serializer = new Serializer([new GetSetMethodNormalizer()], []);
        $data = ['title' => 'foo', 'numbers' => [5, 3]];
        $this->assertTrue($serializer->supportsDenormalization(json_encode($data), '\Symfony\Component\Serializer\Tests\Model', 'json'));
    }

    public function testDeserializeNotSupported()
    {
        $serializer = new Serializer([new GetSetMethodNormalizer()], []);
        $data = ['title' => 'foo', 'numbers' => [5, 3]];
        $this->assertFalse($serializer->supportsDenormalization(json_encode($data), 'stdClass', 'json'));
    }

    public function testDeserializeNotSupportedMissing()
    {
        $serializer = new Serializer([], []);
        $data = ['title' => 'foo', 'numbers' => [5, 3]];
        $this->assertFalse($serializer->supportsDenormalization(json_encode($data), '\Symfony\Component\Serializer\Tests\Model', 'json'));
    }

    public function testEncode()
    {
        $serializer = new Serializer([], ['json' => new JsonEncoder()]);
        $data = ['foo', [5, 3]];
        $result = $serializer->encode($data, 'json');
        $this->assertEquals(json_encode($data), $result);
    }

    public function testDecode()
    {
        $serializer = new Serializer([], ['json' => new JsonEncoder()]);
        $data = ['foo', [5, 3]];
        $result = $serializer->decode(json_encode($data), 'json');
        $this->assertEquals($data, $result);
    }

    public function testSupportsArrayDeserialization()
    {
        $serializer = new Serializer(
            [
                new GetSetMethodNormalizer(),
                new PropertyNormalizer(),
                new ObjectNormalizer(),
                new CustomNormalizer(),
                new ArrayDenormalizer(),
            ],
            [
                'json' => new JsonEncoder(),
            ]
        );

        $this->assertTrue(
            $serializer->supportsDenormalization([], __NAMESPACE__.'\Model[]', 'json')
        );
    }

    public function testDeserializeArray()
    {
        $jsonData = '[{"title":"foo","numbers":[5,3]},{"title":"bar","numbers":[2,8]}]';

        $expectedData = [
            Model::fromArray(['title' => 'foo', 'numbers' => [5, 3]]),
            Model::fromArray(['title' => 'bar', 'numbers' => [2, 8]]),
        ];

        $serializer = new Serializer(
            [
                new GetSetMethodNormalizer(),
                new ArrayDenormalizer(),
            ],
            [
                'json' => new JsonEncoder(),
            ]
        );

        $this->assertEquals(
            $expectedData,
            $serializer->deserialize($jsonData, __NAMESPACE__.'\Model[]', 'json')
        );
    }

    public function testNormalizerAware()
    {
        $normalizerAware = $this->getMockBuilder([NormalizerAwareInterface::class, NormalizerInterface::class])->getMock();
        $normalizerAware->expects($this->once())
            ->method('setNormalizer')
            ->with($this->isInstanceOf(NormalizerInterface::class));

        new Serializer([$normalizerAware]);
    }

    public function testDenormalizerAware()
    {
        $denormalizerAware = $this->getMockBuilder([DenormalizerAwareInterface::class, DenormalizerInterface::class])->getMock();
        $denormalizerAware->expects($this->once())
            ->method('setDenormalizer')
            ->with($this->isInstanceOf(DenormalizerInterface::class));

        new Serializer([$denormalizerAware]);
    }

    public function testDeserializeObjectConstructorWithObjectTypeHint()
    {
        $jsonData = '{"bar":{"value":"baz"}}';

        $serializer = new Serializer([new ObjectNormalizer()], ['json' => new JsonEncoder()]);

        $this->assertEquals(new Foo(new Bar('baz')), $serializer->deserialize($jsonData, Foo::class, 'json'));
    }

    public function testDeserializeAndSerializeAbstractObjectsWithTheClassMetadataDiscriminatorResolver()
    {
        $example = new AbstractDummyFirstChild('foo-value', 'bar-value');

        $loaderMock = $this->getMockBuilder(ClassMetadataFactoryInterface::class)->getMock();
        $loaderMock->method('hasMetadataFor')->will($this->returnValueMap([
            [
                AbstractDummy::class,
                true,
            ],
        ]));

        $loaderMock->method('getMetadataFor')->will($this->returnValueMap([
            [
                AbstractDummy::class,
                new ClassMetadata(
                    AbstractDummy::class,
                    new ClassDiscriminatorMapping('type', [
                        'first' => AbstractDummyFirstChild::class,
                        'second' => AbstractDummySecondChild::class,
                    ])
                ),
            ],
        ]));

        $discriminatorResolver = new ClassDiscriminatorFromClassMetadata($loaderMock);
        $serializer = new Serializer([new ObjectNormalizer(null, null, null, null, $discriminatorResolver)], ['json' => new JsonEncoder()]);

        $jsonData = '{"type":"first","bar":"bar-value","foo":"foo-value"}';

        $deserialized = $serializer->deserialize($jsonData, AbstractDummy::class, 'json');
        $this->assertEquals($example, $deserialized);

        $serialized = $serializer->serialize($deserialized, 'json');
        $this->assertEquals($jsonData, $serialized);
    }

    public function testDeserializeAndSerializeInterfacedObjectsWithTheClassMetadataDiscriminatorResolver()
    {
        $example = new DummyMessageNumberOne();
        $example->one = 1;

        $jsonData = '{"type":"one","one":1,"two":null}';

        $serializer = $this->serializerWithClassDiscriminator();
        $deserialized = $serializer->deserialize($jsonData, DummyMessageInterface::class, 'json');
        $this->assertEquals($example, $deserialized);

        $serialized = $serializer->serialize($deserialized, 'json');
        $this->assertEquals($jsonData, $serialized);
    }

    public function testDeserializeAndSerializeInterfacedObjectsWithTheClassMetadataDiscriminatorResolverAndGroups()
    {
        $example = new DummyMessageNumberOne();
        $example->two = 2;

        $serializer = $this->serializerWithClassDiscriminator();
        $deserialized = $serializer->deserialize('{"type":"one","one":1,"two":2}', DummyMessageInterface::class, 'json', [
            'groups' => ['two'],
        ]);

        $this->assertEquals($example, $deserialized);

        $serialized = $serializer->serialize($deserialized, 'json', [
            'groups' => ['two'],
        ]);

        $this->assertEquals('{"two":2,"type":"one"}', $serialized);
    }

    public function testDeserializeAndSerializeNestedInterfacedObjectsWithTheClassMetadataDiscriminator()
    {
        $nested = new DummyMessageNumberOne();
        $nested->one = 'foo';

        $example = new DummyMessageNumberTwo();
        $example->setNested($nested);

        $serializer = $this->serializerWithClassDiscriminator();

        $serialized = $serializer->serialize($example, 'json');
        $deserialized = $serializer->deserialize($serialized, DummyMessageInterface::class, 'json');

        $this->assertEquals($example, $deserialized);
    }

    /**
     * @expectedException \Symfony\Component\Serializer\Exception\RuntimeException
     * @expectedExceptionMessage The type "second" has no mapped class for the abstract object "Symfony\Component\Serializer\Tests\Fixtures\DummyMessageInterface"
     */
    public function testExceptionWhenTypeIsNotKnownInDiscriminator()
    {
        $this->serializerWithClassDiscriminator()->deserialize('{"type":"second","one":1}', DummyMessageInterface::class, 'json');
    }

    /**
     * @expectedException \Symfony\Component\Serializer\Exception\RuntimeException
     * @expectedExceptionMessage Type property "type" not found for the abstract object "Symfony\Component\Serializer\Tests\Fixtures\DummyMessageInterface"
     */
    public function testExceptionWhenTypeIsNotInTheBodyToDeserialiaze()
    {
        $this->serializerWithClassDiscriminator()->deserialize('{"one":1}', DummyMessageInterface::class, 'json');
    }

    private function serializerWithClassDiscriminator()
    {
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));

        return new Serializer([new ObjectNormalizer($classMetadataFactory, null, null, new ReflectionExtractor(), new ClassDiscriminatorFromClassMetadata($classMetadataFactory))], ['json' => new JsonEncoder()]);
    }
}

class Model
{
    private $title;
    private $numbers;

    public static function fromArray($array)
    {
        $model = new self();
        if (isset($array['title'])) {
            $model->setTitle($array['title']);
        }
        if (isset($array['numbers'])) {
            $model->setNumbers($array['numbers']);
        }

        return $model;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getNumbers()
    {
        return $this->numbers;
    }

    public function setNumbers($numbers)
    {
        $this->numbers = $numbers;
    }

    public function toArray()
    {
        return ['title' => $this->title, 'numbers' => $this->numbers];
    }
}

class Foo
{
    private $bar;

    public function __construct(Bar $bar)
    {
        $this->bar = $bar;
    }
}

class Bar
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }
}
