<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Messenger\Tests\Transport\Serialization;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\SerializerStamp;
use Symfony\Component\Messenger\Stamp\ValidationStamp;
use Symfony\Component\Messenger\Tests\Fixtures\DummyMessage;
use Symfony\Component\Messenger\Transport\Serialization\Serializer;
use Symfony\Component\Serializer as SerializerComponent;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface as SerializerComponentInterface;

class SerializerTest extends TestCase
{
    public function testEncodedIsDecodable()
    {
        $serializer = new Serializer();

        $envelope = new Envelope(new DummyMessage('Hello'));

        $this->assertEquals($envelope, $serializer->decode($serializer->encode($envelope)));
    }

    public function testEncodedWithStampsIsDecodable()
    {
        $serializer = new Serializer();

        $envelope = (new Envelope(new DummyMessage('Hello')))
            ->with(new SerializerStamp([ObjectNormalizer::GROUPS => ['foo']]))
            ->with(new ValidationStamp(['foo', 'bar']))
        ;

        $this->assertEquals($envelope, $serializer->decode($serializer->encode($envelope)));
    }

    public function testEncodedIsHavingTheBodyAndTypeHeader()
    {
        $serializer = new Serializer();

        $encoded = $serializer->encode(new Envelope(new DummyMessage('Hello')));

        $this->assertArrayHasKey('body', $encoded);
        $this->assertArrayHasKey('headers', $encoded);
        $this->assertArrayHasKey('type', $encoded['headers']);
        $this->assertEquals(DummyMessage::class, $encoded['headers']['type']);
    }

    public function testUsesTheCustomFormatAndContext()
    {
        $message = new DummyMessage('Foo');

        $serializer = $this->getMockBuilder(SerializerComponent\SerializerInterface::class)->getMock();
        $serializer->expects($this->once())->method('serialize')->with($message, 'csv', ['foo' => 'bar'])->willReturn('Yay');
        $serializer->expects($this->once())->method('deserialize')->with('Yay', DummyMessage::class, 'csv', ['foo' => 'bar'])->willReturn($message);

        $encoder = new Serializer($serializer, 'csv', ['foo' => 'bar']);

        $encoded = $encoder->encode(new Envelope($message));
        $decoded = $encoder->decode($encoded);

        $this->assertSame('Yay', $encoded['body']);
        $this->assertSame($message, $decoded->getMessage());
    }

    public function testEncodedWithSymfonySerializerForStamps()
    {
        $serializer = new Serializer(
            $symfonySerializer = $this->createMock(SerializerComponentInterface::class)
        );

        $envelope = (new Envelope($message = new DummyMessage('test')))
            ->with($serializerStamp = new SerializerStamp([ObjectNormalizer::GROUPS => ['foo']]))
            ->with($validationStamp = new ValidationStamp(['foo', 'bar']));

        $symfonySerializer
            ->expects($this->at(2))
            ->method('serialize')->with(
                $message,
                'json',
                [
                    ObjectNormalizer::GROUPS => ['foo'],
                ]
            )
        ;

        $encoded = $serializer->encode($envelope);

        $this->assertArrayHasKey('body', $encoded);
        $this->assertArrayHasKey('headers', $encoded);
        $this->assertArrayHasKey('type', $encoded['headers']);
        $this->assertArrayHasKey('X-Message-Stamp-'.SerializerStamp::class, $encoded['headers']);
        $this->assertArrayHasKey('X-Message-Stamp-'.ValidationStamp::class, $encoded['headers']);
    }

    public function testDecodeWithSymfonySerializerStamp()
    {
        $serializer = new Serializer(
            $symfonySerializer = $this->createMock(SerializerComponentInterface::class)
        );

        $symfonySerializer
            ->expects($this->at(0))
            ->method('deserialize')
            ->with('[{"context":{"groups":["foo"]}}]', SerializerStamp::class.'[]', 'json', [])
            ->willReturn([new SerializerStamp(['groups' => ['foo']])])
        ;

        $symfonySerializer
            ->expects($this->at(1))
            ->method('deserialize')->with(
                '{}',
                DummyMessage::class,
                'json',
                [
                    ObjectNormalizer::GROUPS => ['foo'],
                ]
            )
            ->willReturn(new DummyMessage('test'))
        ;

        $serializer->decode([
            'body' => '{}',
            'headers' => [
                'type' => DummyMessage::class,
                'X-Message-Stamp-'.SerializerStamp::class => '[{"context":{"groups":["foo"]}}]',
            ],
        ]);
    }
}
