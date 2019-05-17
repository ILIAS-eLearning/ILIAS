<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Messenger\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use Symfony\Component\DependencyInjection\Compiler\ResolveChildDefinitionsPass;
use Symfony\Component\DependencyInjection\Compiler\ResolveClassPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\Messenger\Command\ConsumeMessagesCommand;
use Symfony\Component\Messenger\Command\DebugCommand;
use Symfony\Component\Messenger\DataCollector\MessengerDataCollector;
use Symfony\Component\Messenger\DependencyInjection\MessengerPass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Tests\Fixtures\DummyCommand;
use Symfony\Component\Messenger\Tests\Fixtures\DummyCommandHandler;
use Symfony\Component\Messenger\Tests\Fixtures\DummyMessage;
use Symfony\Component\Messenger\Tests\Fixtures\DummyQuery;
use Symfony\Component\Messenger\Tests\Fixtures\DummyQueryHandler;
use Symfony\Component\Messenger\Tests\Fixtures\MultipleBusesMessage;
use Symfony\Component\Messenger\Tests\Fixtures\MultipleBusesMessageHandler;
use Symfony\Component\Messenger\Tests\Fixtures\SecondMessage;
use Symfony\Component\Messenger\Transport\AmqpExt\AmqpReceiver;
use Symfony\Component\Messenger\Transport\Receiver\ReceiverInterface;

class MessengerPassTest extends TestCase
{
    public function testProcess()
    {
        $container = $this->getContainerBuilder($busId = 'message_bus');
        $container
            ->register(DummyHandler::class, DummyHandler::class)
            ->addTag('messenger.message_handler')
        ;
        $container
            ->register(MissingArgumentTypeHandler::class, MissingArgumentTypeHandler::class)
            ->addTag('messenger.message_handler', ['handles' => SecondMessage::class])
        ;
        $container
            ->register(DummyReceiver::class, DummyReceiver::class)
            ->addTag('messenger.receiver')
        ;

        (new MessengerPass())->process($container);

        $this->assertFalse($container->hasDefinition('messenger.middleware.debug.logging'));

        $handlersLocatorDefinition = $container->getDefinition($busId.'.messenger.handlers_locator');
        $this->assertSame(HandlersLocator::class, $handlersLocatorDefinition->getClass());
        $this->assertEquals(
            [
                DummyMessage::class => new IteratorArgument([new Reference(DummyHandler::class)]),
                SecondMessage::class => new IteratorArgument([new Reference(MissingArgumentTypeHandler::class)]),
            ],
            $handlersLocatorDefinition->getArgument(0)
        );

        $this->assertEquals(
            [DummyReceiver::class => new Reference(DummyReceiver::class)],
            $container->getDefinition('messenger.receiver_locator')->getArgument(0)
        );
    }

    public function testProcessHandlersByBus()
    {
        $container = $this->getContainerBuilder($commandBusId = 'command_bus');
        $container->register($queryBusId = 'query_bus', MessageBusInterface::class)->setArgument(0, [])->addTag('messenger.bus');
        $container->register('messenger.middleware.handle_message', HandleMessageMiddleware::class)
            ->addArgument(null)
            ->setAbstract(true)
        ;

        $middlewareHandlers = [['id' => 'handle_message']];

        $container->setParameter($commandBusId.'.middleware', $middlewareHandlers);
        $container->setParameter($queryBusId.'.middleware', $middlewareHandlers);

        $container->register(DummyCommandHandler::class)->addTag('messenger.message_handler', ['bus' => $commandBusId]);
        $container->register(DummyQueryHandler::class)->addTag('messenger.message_handler', ['bus' => $queryBusId]);
        $container->register(MultipleBusesMessageHandler::class)
            ->addTag('messenger.message_handler', ['bus' => $commandBusId])
            ->addTag('messenger.message_handler', ['bus' => $queryBusId])
        ;

        (new ResolveClassPass())->process($container);
        (new MessengerPass())->process($container);

        $commandBusHandlersLocatorDefinition = $container->getDefinition($commandBusId.'.messenger.handlers_locator');
        $this->assertSame(HandlersLocator::class, $commandBusHandlersLocatorDefinition->getClass());
        $this->assertEquals(
            [
                MultipleBusesMessage::class => new IteratorArgument([new Reference(MultipleBusesMessageHandler::class)]),
                DummyCommand::class => new IteratorArgument([new Reference(DummyCommandHandler::class)]),
            ],
            $commandBusHandlersLocatorDefinition->getArgument(0)
        );

        $queryBusHandlersLocatorDefinition = $container->getDefinition($queryBusId.'.messenger.handlers_locator');
        $this->assertSame(HandlersLocator::class, $queryBusHandlersLocatorDefinition->getClass());
        $this->assertEquals(
            [
                DummyQuery::class => new IteratorArgument([new Reference(DummyQueryHandler::class)]),
                MultipleBusesMessage::class => new IteratorArgument([new Reference(MultipleBusesMessageHandler::class)]),
            ],
            $queryBusHandlersLocatorDefinition->getArgument(0)
        );
    }

    /**
     * @expectedException \Symfony\Component\DependencyInjection\Exception\RuntimeException
     * @expectedExceptionMessage Invalid handler service "Symfony\Component\Messenger\Tests\Fixtures\DummyCommandHandler": bus "unknown_bus" specified on the tag "messenger.message_handler" does not exist (known ones are: command_bus).
     */
    public function testProcessTagWithUnknownBus()
    {
        $container = $this->getContainerBuilder($commandBusId = 'command_bus');

        $container->register(DummyCommandHandler::class)->addTag('messenger.message_handler', ['bus' => 'unknown_bus']);

        (new ResolveClassPass())->process($container);
        (new MessengerPass())->process($container);
    }

    public function testGetClassesFromTheHandlerSubscriberInterface()
    {
        $container = $this->getContainerBuilder($busId = 'message_bus');
        $container
            ->register(HandlerWithMultipleMessages::class, HandlerWithMultipleMessages::class)
            ->addTag('messenger.message_handler')
        ;
        $container
            ->register(PrioritizedHandler::class, PrioritizedHandler::class)
            ->addTag('messenger.message_handler')
        ;

        (new MessengerPass())->process($container);

        $handlersMapping = $container->getDefinition($busId.'.messenger.handlers_locator')->getArgument(0);

        $this->assertArrayHasKey(DummyMessage::class, $handlersMapping);
        $this->assertEquals(new IteratorArgument([new Reference(HandlerWithMultipleMessages::class)]), $handlersMapping[DummyMessage::class]);

        $this->assertArrayHasKey(SecondMessage::class, $handlersMapping);
        $this->assertEquals(new IteratorArgument([new Reference(PrioritizedHandler::class), new Reference(HandlerWithMultipleMessages::class)]), $handlersMapping[SecondMessage::class]);
    }

    public function testGetClassesAndMethodsAndPrioritiesFromTheSubscriber()
    {
        $container = $this->getContainerBuilder($busId = 'message_bus');
        $container
            ->register(HandlerMappingMethods::class, HandlerMappingMethods::class)
            ->addTag('messenger.message_handler')
        ;
        $container
            ->register(PrioritizedHandler::class, PrioritizedHandler::class)
            ->addTag('messenger.message_handler')
        ;

        (new MessengerPass())->process($container);

        $handlersMapping = $container->getDefinition($busId.'.messenger.handlers_locator')->getArgument(0);

        $this->assertArrayHasKey(DummyMessage::class, $handlersMapping);
        $this->assertArrayHasKey(SecondMessage::class, $handlersMapping);

        $dummyHandlerReference = $handlersMapping[DummyMessage::class]->getValues()[0];
        $dummyHandlerDefinition = $container->getDefinition($dummyHandlerReference);
        $this->assertSame('callable', $dummyHandlerDefinition->getClass());
        $this->assertEquals([new Reference(HandlerMappingMethods::class), 'dummyMethod'], $dummyHandlerDefinition->getArgument(0));
        $this->assertSame(['Closure', 'fromCallable'], $dummyHandlerDefinition->getFactory());

        $secondHandlerReference = $handlersMapping[SecondMessage::class]->getValues()[1];
        $secondHandlerDefinition = $container->getDefinition($secondHandlerReference);
        $this->assertSame(PrioritizedHandler::class, $secondHandlerDefinition->getClass());
    }

    /**
     * @expectedException \Symfony\Component\DependencyInjection\Exception\RuntimeException
     * @expectedExceptionMessage Invalid service "NonExistentHandlerClass": class "NonExistentHandlerClass" does not exist.
     */
    public function testThrowsExceptionIfTheHandlerClassDoesNotExist()
    {
        $container = $this->getContainerBuilder();
        $container->register('message_bus', MessageBusInterface::class)->addTag('messenger.bus');
        $container
            ->register('NonExistentHandlerClass', 'NonExistentHandlerClass')
            ->addTag('messenger.message_handler')
        ;

        (new MessengerPass())->process($container);
    }

    /**
     * @expectedException \Symfony\Component\DependencyInjection\Exception\RuntimeException
     * @expectedExceptionMessage Invalid handler service "Symfony\Component\Messenger\Tests\DependencyInjection\HandlerMappingWithNonExistentMethod": method "Symfony\Component\Messenger\Tests\DependencyInjection\HandlerMappingWithNonExistentMethod::dummyMethod()" does not exist.
     */
    public function testThrowsExceptionIfTheHandlerMethodDoesNotExist()
    {
        $container = $this->getContainerBuilder();
        $container->register('message_bus', MessageBusInterface::class)->addTag('messenger.bus');
        $container
            ->register(HandlerMappingWithNonExistentMethod::class, HandlerMappingWithNonExistentMethod::class)
            ->addTag('messenger.message_handler')
        ;

        (new MessengerPass())->process($container);
    }

    public function testItRegistersReceivers()
    {
        $container = $this->getContainerBuilder();
        $container->register('message_bus', MessageBusInterface::class)->addTag('messenger.bus');
        $container->register(AmqpReceiver::class, AmqpReceiver::class)->addTag('messenger.receiver', ['alias' => 'amqp']);

        (new MessengerPass())->process($container);

        $this->assertEquals(['amqp' => new Reference(AmqpReceiver::class), AmqpReceiver::class => new Reference(AmqpReceiver::class)], $container->getDefinition('messenger.receiver_locator')->getArgument(0));
    }

    public function testItRegistersReceiversWithoutTagName()
    {
        $container = $this->getContainerBuilder();
        $container->register('message_bus', MessageBusInterface::class)->addTag('messenger.bus');
        $container->register(AmqpReceiver::class, AmqpReceiver::class)->addTag('messenger.receiver');

        (new MessengerPass())->process($container);

        $this->assertEquals([AmqpReceiver::class => new Reference(AmqpReceiver::class)], $container->getDefinition('messenger.receiver_locator')->getArgument(0));
    }

    public function testItRegistersMultipleReceiversAndSetsTheReceiverNamesOnTheCommand()
    {
        $container = $this->getContainerBuilder();
        $container->register('console.command.messenger_consume_messages', ConsumeMessagesCommand::class)->setArguments([
            null,
            new Reference('messenger.receiver_locator'),
            null,
            null,
            null,
        ]);

        $container->register(AmqpReceiver::class, AmqpReceiver::class)->addTag('messenger.receiver', ['alias' => 'amqp']);
        $container->register(DummyReceiver::class, DummyReceiver::class)->addTag('messenger.receiver', ['alias' => 'dummy']);

        (new MessengerPass())->process($container);

        $this->assertSame(['amqp', 'dummy'], $container->getDefinition('console.command.messenger_consume_messages')->getArgument(3));
        $this->assertSame(['message_bus'], $container->getDefinition('console.command.messenger_consume_messages')->getArgument(4));
    }

    public function testItShouldNotThrowIfGeneratorIsReturnedInsteadOfArray()
    {
        $container = $this->getContainerBuilder($busId = 'message_bus');
        $container
            ->register(HandlerWithGenerators::class, HandlerWithGenerators::class)
            ->addTag('messenger.message_handler')
        ;

        (new MessengerPass())->process($container);

        $handlersMapping = $container->getDefinition($busId.'.messenger.handlers_locator')->getArgument(0);

        $this->assertArrayHasKey(DummyMessage::class, $handlersMapping);
        $firstReference = $handlersMapping[DummyMessage::class]->getValues()[0];
        $this->assertEquals([new Reference(HandlerWithGenerators::class), 'dummyMethod'], $container->getDefinition($firstReference)->getArgument(0));

        $this->assertArrayHasKey(SecondMessage::class, $handlersMapping);
        $secondReference = $handlersMapping[SecondMessage::class]->getValues()[0];
        $this->assertEquals([new Reference(HandlerWithGenerators::class), 'secondMessage'], $container->getDefinition($secondReference)->getArgument(0));
    }

    public function testItRegistersHandlersOnDifferentBuses()
    {
        $container = $this->getContainerBuilder($eventsBusId = 'event_bus');
        $container->register($commandsBusId = 'command_bus', MessageBusInterface::class)->addTag('messenger.bus')->setArgument(0, []);

        $container
            ->register(HandlerOnSpecificBuses::class, HandlerOnSpecificBuses::class)
            ->addTag('messenger.message_handler')
        ;

        (new MessengerPass())->process($container);

        $eventsHandlerMapping = $container->getDefinition($eventsBusId.'.messenger.handlers_locator')->getArgument(0);

        $this->assertEquals([DummyMessage::class], array_keys($eventsHandlerMapping));
        $firstReference = $eventsHandlerMapping[DummyMessage::class]->getValues()[0];
        $this->assertEquals([new Reference(HandlerOnSpecificBuses::class), 'dummyMethodForEvents'], $container->getDefinition($firstReference)->getArgument(0));

        $commandsHandlerMapping = $container->getDefinition($commandsBusId.'.messenger.handlers_locator')->getArgument(0);

        $this->assertEquals([DummyMessage::class], array_keys($commandsHandlerMapping));
        $firstReference = $commandsHandlerMapping[DummyMessage::class]->getValues()[0];
        $this->assertEquals([new Reference(HandlerOnSpecificBuses::class), 'dummyMethodForCommands'], $container->getDefinition($firstReference)->getArgument(0));
    }

    /**
     * @expectedException \Symfony\Component\DependencyInjection\Exception\RuntimeException
     * @expectedExceptionMessage Invalid configuration returned by method "Symfony\Component\Messenger\Tests\DependencyInjection\HandlerOnUndefinedBus::getHandledMessages()" for message "Symfony\Component\Messenger\Tests\Fixtures\DummyMessage": bus "some_undefined_bus" does not exist.
     */
    public function testItThrowsAnExceptionOnUnknownBus()
    {
        $container = $this->getContainerBuilder();
        $container
            ->register(HandlerOnUndefinedBus::class, HandlerOnUndefinedBus::class)
            ->addTag('messenger.message_handler')
        ;

        (new MessengerPass())->process($container);
    }

    /**
     * @expectedException \Symfony\Component\DependencyInjection\Exception\RuntimeException
     * @expectedExceptionMessage Invalid handler service "Symfony\Component\Messenger\Tests\DependencyInjection\UndefinedMessageHandler": class or interface "Symfony\Component\Messenger\Tests\DependencyInjection\UndefinedMessage" used as argument type in method "Symfony\Component\Messenger\Tests\DependencyInjection\UndefinedMessageHandler::__invoke()" not found.
     */
    public function testUndefinedMessageClassForHandler()
    {
        $container = $this->getContainerBuilder();
        $container
            ->register(UndefinedMessageHandler::class, UndefinedMessageHandler::class)
            ->addTag('messenger.message_handler')
        ;

        (new MessengerPass())->process($container);
    }

    /**
     * @expectedException \Symfony\Component\DependencyInjection\Exception\RuntimeException
     * @expectedExceptionMessage Invalid handler service "Symfony\Component\Messenger\Tests\DependencyInjection\UndefinedMessageHandlerViaHandlerInterface": class or interface "Symfony\Component\Messenger\Tests\DependencyInjection\UndefinedMessage" used as argument type in method "Symfony\Component\Messenger\Tests\DependencyInjection\UndefinedMessageHandlerViaHandlerInterface::__invoke()" not found.
     */
    public function testUndefinedMessageClassForHandlerImplementingMessageHandlerInterface()
    {
        $container = $this->getContainerBuilder();
        $container
            ->register(UndefinedMessageHandlerViaHandlerInterface::class, UndefinedMessageHandlerViaHandlerInterface::class)
            ->addTag('messenger.message_handler')
        ;

        (new MessengerPass())->process($container);
    }

    /**
     * @expectedException \Symfony\Component\DependencyInjection\Exception\RuntimeException
     * @expectedExceptionMessage Invalid handler service "Symfony\Component\Messenger\Tests\DependencyInjection\UndefinedMessageHandlerViaSubscriberInterface": class or interface "Symfony\Component\Messenger\Tests\DependencyInjection\UndefinedMessage" returned by method "Symfony\Component\Messenger\Tests\DependencyInjection\UndefinedMessageHandlerViaSubscriberInterface::getHandledMessages()" not found.
     */
    public function testUndefinedMessageClassForHandlerImplementingMessageSubscriberInterface()
    {
        $container = $this->getContainerBuilder();
        $container
            ->register(UndefinedMessageHandlerViaSubscriberInterface::class, UndefinedMessageHandlerViaSubscriberInterface::class)
            ->addTag('messenger.message_handler')
        ;

        (new MessengerPass())->process($container);
    }

    /**
     * @expectedException \Symfony\Component\DependencyInjection\Exception\RuntimeException
     * @expectedExceptionMessage Invalid handler service "Symfony\Component\Messenger\Tests\DependencyInjection\NotInvokableHandler": class "Symfony\Component\Messenger\Tests\DependencyInjection\NotInvokableHandler" must have an "__invoke()" method.
     */
    public function testNotInvokableHandler()
    {
        $container = $this->getContainerBuilder();
        $container
            ->register(NotInvokableHandler::class, NotInvokableHandler::class)
            ->addTag('messenger.message_handler')
        ;

        (new MessengerPass())->process($container);
    }

    /**
     * @expectedException \Symfony\Component\DependencyInjection\Exception\RuntimeException
     * @expectedExceptionMessage Invalid handler service "Symfony\Component\Messenger\Tests\DependencyInjection\MissingArgumentHandler": method "Symfony\Component\Messenger\Tests\DependencyInjection\MissingArgumentHandler::__invoke()" must have exactly one argument corresponding to the message it handles.
     */
    public function testMissingArgumentHandler()
    {
        $container = $this->getContainerBuilder();
        $container
            ->register(MissingArgumentHandler::class, MissingArgumentHandler::class)
            ->addTag('messenger.message_handler')
        ;

        (new MessengerPass())->process($container);
    }

    /**
     * @expectedException \Symfony\Component\DependencyInjection\Exception\RuntimeException
     * @expectedExceptionMessage Invalid handler service "Symfony\Component\Messenger\Tests\DependencyInjection\MissingArgumentTypeHandler": argument "$message" of method "Symfony\Component\Messenger\Tests\DependencyInjection\MissingArgumentTypeHandler::__invoke()" must have a type-hint corresponding to the message class it handles.
     */
    public function testMissingArgumentTypeHandler()
    {
        $container = $this->getContainerBuilder();
        $container
            ->register(MissingArgumentTypeHandler::class, MissingArgumentTypeHandler::class)
            ->addTag('messenger.message_handler')
        ;

        (new MessengerPass())->process($container);
    }

    /**
     * @expectedException \Symfony\Component\DependencyInjection\Exception\RuntimeException
     * @expectedExceptionMessage Invalid handler service "Symfony\Component\Messenger\Tests\DependencyInjection\BuiltinArgumentTypeHandler": type-hint of argument "$message" in method "Symfony\Component\Messenger\Tests\DependencyInjection\BuiltinArgumentTypeHandler::__invoke()" must be a class , "string" given.
     */
    public function testBuiltinArgumentTypeHandler()
    {
        $container = $this->getContainerBuilder();
        $container
            ->register(BuiltinArgumentTypeHandler::class, BuiltinArgumentTypeHandler::class)
            ->addTag('messenger.message_handler')
        ;

        (new MessengerPass())->process($container);
    }

    /**
     * @expectedException \Symfony\Component\DependencyInjection\Exception\RuntimeException
     * @expectedExceptionMessage Invalid handler service "Symfony\Component\Messenger\Tests\DependencyInjection\HandleNoMessageHandler": method "Symfony\Component\Messenger\Tests\DependencyInjection\HandleNoMessageHandler::getHandledMessages()" must return one or more messages.
     */
    public function testNeedsToHandleAtLeastOneMessage()
    {
        $container = $this->getContainerBuilder();
        $container
            ->register(HandleNoMessageHandler::class, HandleNoMessageHandler::class)
            ->addTag('messenger.message_handler')
        ;

        (new MessengerPass())->process($container);
    }

    public function testRegistersTraceableBusesToCollector()
    {
        $dataCollector = $this->getMockBuilder(MessengerDataCollector::class)->getMock();

        $container = $this->getContainerBuilder($fooBusId = 'messenger.bus.foo');
        $container->register('data_collector.messenger', $dataCollector);
        $container->setParameter('kernel.debug', true);

        (new MessengerPass())->process($container);

        $this->assertTrue($container->hasDefinition($debuggedFooBusId = 'debug.traced.'.$fooBusId));
        $this->assertSame([$fooBusId, null, 0], $container->getDefinition($debuggedFooBusId)->getDecoratedService());
        $this->assertEquals([['registerBus', [$fooBusId, new Reference($debuggedFooBusId)]]], $container->getDefinition('data_collector.messenger')->getMethodCalls());
    }

    public function testRegistersMiddlewareFromServices()
    {
        $container = $this->getContainerBuilder($fooBusId = 'messenger.bus.foo');
        $container->register('middleware_with_factory', UselessMiddleware::class)->addArgument('some_default')->setAbstract(true);
        $container->register('middleware_with_factory_using_default', UselessMiddleware::class)->addArgument('some_default')->setAbstract(true);
        $container->register(UselessMiddleware::class, UselessMiddleware::class);

        $container->setParameter($middlewareParameter = $fooBusId.'.middleware', [
            ['id' => UselessMiddleware::class],
            ['id' => 'middleware_with_factory', 'arguments' => ['index_0' => 'foo', 'bar']],
            ['id' => 'middleware_with_factory_using_default'],
        ]);

        (new MessengerPass())->process($container);
        (new ResolveChildDefinitionsPass())->process($container);

        $this->assertTrue($container->hasDefinition($factoryChildMiddlewareId = $fooBusId.'.middleware.middleware_with_factory'));
        $this->assertEquals(
            ['foo', 'bar'],
            $container->getDefinition($factoryChildMiddlewareId)->getArguments(),
            'parent default argument is overridden, and next ones appended'
        );

        $this->assertTrue($container->hasDefinition($factoryWithDefaultChildMiddlewareId = $fooBusId.'.middleware.middleware_with_factory_using_default'));
        $this->assertEquals(
            ['some_default'],
            $container->getDefinition($factoryWithDefaultChildMiddlewareId)->getArguments(),
            'parent default argument is used'
        );

        $this->assertEquals([
            new Reference(UselessMiddleware::class),
            new Reference($factoryChildMiddlewareId),
            new Reference($factoryWithDefaultChildMiddlewareId),
        ], $container->getDefinition($fooBusId)->getArgument(0)->getValues());
        $this->assertFalse($container->hasParameter($middlewareParameter));
    }

    /**
     * @expectedException \Symfony\Component\DependencyInjection\Exception\RuntimeException
     * @expectedExceptionMessage Invalid middleware: service "not_defined_middleware" not found.
     */
    public function testCannotRegistersAnUndefinedMiddleware()
    {
        $container = $this->getContainerBuilder($fooBusId = 'messenger.bus.foo');
        $container->setParameter($middlewareParameter = $fooBusId.'.middleware', [
            ['id' => 'not_defined_middleware', 'arguments' => []],
        ]);

        (new MessengerPass())->process($container);
    }

    /**
     * @expectedException \Symfony\Component\DependencyInjection\Exception\RuntimeException
     * @expectedExceptionMessage Invalid middleware factory "not_an_abstract_definition": a middleware factory must be an abstract definition.
     */
    public function testMiddlewareFactoryDefinitionMustBeAbstract()
    {
        $container = $this->getContainerBuilder($fooBusId = 'messenger.bus.foo');
        $container->register('not_an_abstract_definition', UselessMiddleware::class);
        $container->setParameter($middlewareParameter = $fooBusId.'.middleware', [
            ['id' => 'not_an_abstract_definition', 'arguments' => ['foo']],
        ]);

        (new MessengerPass())->process($container);
    }

    public function testItRegistersTheDebugCommand()
    {
        $container = $this->getContainerBuilder($commandBusId = 'command_bus');
        $container->register($queryBusId = 'query_bus', MessageBusInterface::class)->setArgument(0, [])->addTag('messenger.bus');
        $container->register($emptyBus = 'empty_bus', MessageBusInterface::class)->setArgument(0, [])->addTag('messenger.bus');
        $container->register('messenger.middleware.handle_message', HandleMessageMiddleware::class)
            ->addArgument(null)
            ->setAbstract(true)
        ;

        $container->register('console.command.messenger_debug', DebugCommand::class)->addArgument([]);

        $middlewareHandlers = [['id' => 'handle_message']];

        $container->setParameter($commandBusId.'.middleware', $middlewareHandlers);
        $container->setParameter($queryBusId.'.middleware', $middlewareHandlers);

        $container->register(DummyCommandHandler::class)->addTag('messenger.message_handler', ['bus' => $commandBusId]);
        $container->register(DummyQueryHandler::class)->addTag('messenger.message_handler', ['bus' => $queryBusId]);
        $container->register(MultipleBusesMessageHandler::class)
            ->addTag('messenger.message_handler', ['bus' => $commandBusId])
            ->addTag('messenger.message_handler', ['bus' => $queryBusId])
        ;

        (new ResolveClassPass())->process($container);
        (new MessengerPass())->process($container);

        $this->assertEquals([
            $commandBusId => [
                DummyCommand::class => [DummyCommandHandler::class],
                MultipleBusesMessage::class => [MultipleBusesMessageHandler::class],
            ],
            $queryBusId => [
                DummyQuery::class => [DummyQueryHandler::class],
                MultipleBusesMessage::class => [MultipleBusesMessageHandler::class],
            ],
            $emptyBus => [],
        ], $container->getDefinition('console.command.messenger_debug')->getArgument(0));
    }

    private function getContainerBuilder(string $busId = 'message_bus'): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.debug', true);

        $container->register($busId, MessageBusInterface::class)->addTag('messenger.bus')->setArgument(0, []);
        if ('message_bus' !== $busId) {
            $container->setAlias('message_bus', $busId);
        }

        $container->register('messenger.receiver_locator', ServiceLocator::class)
            ->addArgument(new Reference('service_container'))
        ;

        return $container;
    }
}

class DummyHandler
{
    public function __invoke(DummyMessage $message): void
    {
    }
}

class DummyReceiver implements ReceiverInterface
{
    public function receive(callable $handler): void
    {
        for ($i = 0; $i < 3; ++$i) {
            $handler(new Envelope(new DummyMessage("Dummy $i")));
        }
    }

    public function stop(): void
    {
    }
}

class InvalidReceiver
{
}

class InvalidSender
{
}

class UndefinedMessageHandler
{
    public function __invoke(UndefinedMessage $message)
    {
    }
}

class UndefinedMessageHandlerViaHandlerInterface implements MessageHandlerInterface
{
    public function __invoke(UndefinedMessage $message)
    {
    }
}

class UndefinedMessageHandlerViaSubscriberInterface implements MessageSubscriberInterface
{
    public static function getHandledMessages(): iterable
    {
        return [UndefinedMessage::class];
    }

    public function __invoke()
    {
    }
}

class NotInvokableHandler
{
}

class MissingArgumentHandler
{
    public function __invoke()
    {
    }
}

class MissingArgumentTypeHandler
{
    public function __invoke($message)
    {
    }
}

class BuiltinArgumentTypeHandler
{
    public function __invoke(string $message)
    {
    }
}

class HandlerWithMultipleMessages implements MessageSubscriberInterface
{
    public static function getHandledMessages(): iterable
    {
        return [
            DummyMessage::class,
            SecondMessage::class,
        ];
    }

    public function __invoke()
    {
    }
}

class PrioritizedHandler implements MessageSubscriberInterface
{
    public static function getHandledMessages(): iterable
    {
        yield SecondMessage::class => ['priority' => 10];
    }

    public function __invoke()
    {
    }
}

class HandlerMappingMethods implements MessageSubscriberInterface
{
    public static function getHandledMessages(): iterable
    {
        yield DummyMessage::class => 'dummyMethod';
        yield SecondMessage::class => ['method' => 'secondMessage', 'priority' => 20];
    }

    public function dummyMethod()
    {
    }

    public function secondMessage()
    {
    }
}

class HandlerMappingWithNonExistentMethod implements MessageSubscriberInterface
{
    public static function getHandledMessages(): iterable
    {
        return [
            DummyMessage::class => 'dummyMethod',
        ];
    }
}

class HandleNoMessageHandler implements MessageSubscriberInterface
{
    public static function getHandledMessages(): iterable
    {
        return [];
    }

    public function __invoke()
    {
    }
}

class HandlerWithGenerators implements MessageSubscriberInterface
{
    public static function getHandledMessages(): iterable
    {
        yield DummyMessage::class => 'dummyMethod';
        yield SecondMessage::class => 'secondMessage';
    }

    public function dummyMethod()
    {
    }

    public function secondMessage()
    {
    }
}

class HandlerOnSpecificBuses implements MessageSubscriberInterface
{
    public static function getHandledMessages(): iterable
    {
        yield DummyMessage::class => ['method' => 'dummyMethodForEvents', 'bus' => 'event_bus'];
        yield DummyMessage::class => ['method' => 'dummyMethodForCommands', 'bus' => 'command_bus'];
    }

    public function dummyMethodForEvents()
    {
    }

    public function dummyMethodForCommands()
    {
    }
}

class HandlerOnUndefinedBus implements MessageSubscriberInterface
{
    public static function getHandledMessages(): iterable
    {
        yield DummyMessage::class => ['method' => 'dummyMethodForSomeBus', 'bus' => 'some_undefined_bus'];
    }

    public function dummyMethodForSomeBus()
    {
    }
}

class UselessMiddleware implements MiddlewareInterface
{
    public function handle(Envelope $message, StackInterface $stack): Envelope
    {
        return $stack->next()->handle($message, $stack);
    }
}
