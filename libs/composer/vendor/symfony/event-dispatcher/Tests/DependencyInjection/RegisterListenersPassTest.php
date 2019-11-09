<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\EventDispatcher\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;

class RegisterListenersPassTest extends TestCase
{
    /**
     * Tests that event subscribers not implementing EventSubscriberInterface
     * trigger an exception.
     */
    public function testEventSubscriberWithoutInterface()
    {
        $this->expectException('InvalidArgumentException');
        $builder = new ContainerBuilder();
        $builder->register('event_dispatcher');
        $builder->register('my_event_subscriber', 'stdClass')
            ->addTag('kernel.event_subscriber');

        $registerListenersPass = new RegisterListenersPass();
        $registerListenersPass->process($builder);
    }

    public function testValidEventSubscriber()
    {
        $services = [
            'my_event_subscriber' => [0 => []],
        ];

        $builder = new ContainerBuilder();
        $eventDispatcherDefinition = $builder->register('event_dispatcher');
        $builder->register('my_event_subscriber', 'Symfony\Component\EventDispatcher\Tests\DependencyInjection\SubscriberService')
            ->addTag('kernel.event_subscriber');

        $registerListenersPass = new RegisterListenersPass();
        $registerListenersPass->process($builder);

        $expectedCalls = [
            [
                'addListener',
                [
                    'event',
                    [new ServiceClosureArgument(new Reference('my_event_subscriber')), 'onEvent'],
                    0,
                ],
            ],
        ];
        $this->assertEquals($expectedCalls, $eventDispatcherDefinition->getMethodCalls());
    }

    public function testAbstractEventListener()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('The service "foo" tagged "kernel.event_listener" must not be abstract.');
        $container = new ContainerBuilder();
        $container->register('foo', 'stdClass')->setAbstract(true)->addTag('kernel.event_listener', []);
        $container->register('event_dispatcher', 'stdClass');

        $registerListenersPass = new RegisterListenersPass();
        $registerListenersPass->process($container);
    }

    public function testAbstractEventSubscriber()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('The service "foo" tagged "kernel.event_subscriber" must not be abstract.');
        $container = new ContainerBuilder();
        $container->register('foo', 'stdClass')->setAbstract(true)->addTag('kernel.event_subscriber', []);
        $container->register('event_dispatcher', 'stdClass');

        $registerListenersPass = new RegisterListenersPass();
        $registerListenersPass->process($container);
    }

    public function testEventSubscriberResolvableClassName()
    {
        $container = new ContainerBuilder();

        $container->setParameter('subscriber.class', 'Symfony\Component\EventDispatcher\Tests\DependencyInjection\SubscriberService');
        $container->register('foo', '%subscriber.class%')->addTag('kernel.event_subscriber', []);
        $container->register('event_dispatcher', 'stdClass');

        $registerListenersPass = new RegisterListenersPass();
        $registerListenersPass->process($container);

        $definition = $container->getDefinition('event_dispatcher');
        $expectedCalls = [
            [
                'addListener',
                [
                    'event',
                    [new ServiceClosureArgument(new Reference('foo')), 'onEvent'],
                    0,
                ],
            ],
        ];
        $this->assertEquals($expectedCalls, $definition->getMethodCalls());
    }

    public function testHotPathEvents()
    {
        $container = new ContainerBuilder();

        $container->register('foo', SubscriberService::class)->addTag('kernel.event_subscriber', []);
        $container->register('event_dispatcher', 'stdClass');

        (new RegisterListenersPass())->setHotPathEvents(['event'])->process($container);

        $this->assertTrue($container->getDefinition('foo')->hasTag('container.hot_path'));
    }

    public function testEventSubscriberUnresolvableClassName()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('You have requested a non-existent parameter "subscriber.class"');
        $container = new ContainerBuilder();
        $container->register('foo', '%subscriber.class%')->addTag('kernel.event_subscriber', []);
        $container->register('event_dispatcher', 'stdClass');

        $registerListenersPass = new RegisterListenersPass();
        $registerListenersPass->process($container);
    }
}

class SubscriberService implements \Symfony\Component\EventDispatcher\EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'event' => 'onEvent',
        ];
    }
}
