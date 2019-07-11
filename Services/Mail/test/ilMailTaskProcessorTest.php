<?php declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\BackgroundTasks\Implementation\TaskManager\BasicTaskManager;
use ILIAS\BackgroundTasks\Task\TaskFactory;
use ILIAS\DI\Container;

/**
 * Class ilMailTaskProcessorTest
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilMailTaskProcessorTest extends ilMailBaseTest
{
    /** @var ilLanguage */
    private $languageMock;

    /** @var Container */
    private $dicMock;

    /** @var ilLogger */
    private $loggerMock;

    /**
     * @throws ReflectionException
     */
    public function setUp() : void
    {
        $this->languageMock = $this->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->dicMock = $this->getMockBuilder(Container::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->loggerMock = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @throws ilException
     * @throws ReflectionException
     */
    public function testOneTask() : void
    {
        $taskManager = $this->getMockBuilder(BasicTaskManager::class)
            ->setMethods(['run'])
            ->disableOriginalConstructor()
            ->getMock();

        $taskManager
            ->expects($this->exactly(1))
            ->method('run');

        $taskFactory = $this->getMockBuilder(ILIAS\BackgroundTasks\Task\TaskFactory::class)
            ->setMethods(['createTask'])
            ->disableOriginalConstructor()
            ->getMock();

        $backgroundTask = $this->getMockbuilder(ilMailDeliveryJob::class)
            ->disableOriginalConstructor()
            ->getMock();

        $backgroundTask->expects($this->any())->method('unfoldTask')
            ->willReturn([]);

        $taskFactory
            ->expects($this->exactly(2))
            ->method('createTask')
            ->willReturn($backgroundTask);


        $worker = new ilMassMailTaskProcessor(
            $taskManager,
            $taskFactory,
            $this->languageMock,
            $this->loggerMock,
            $this->dicMock,
            new ilMailValueObjectJsonService(),
            'SomeAnonymousUserId'
        );

        $mailValueObject = new ilMailValueObject(
            'ilias@server.com',
            'somebody@iliase.de',
            '',
            '',
            'That is awesome!',
            'Dear Steve, great!',
            null
        );

        $mailValueObjects = [
            $mailValueObject
        ];

        $userId = 100;
        $contextId = '5';
        $contextParameters = [];

        $worker->run(
            $mailValueObjects,
            $userId,
            $contextId,
            $contextParameters
        );
    }

    /**
     * @throws ilException
     * @throws ReflectionException
     */
    public function testRunTwoTasks() : void
    {
        $taskManager = $this->getMockBuilder(BasicTaskManager::class)
            ->setMethods(['run'])
            ->disableOriginalConstructor()
            ->getMock();

        $taskManager
            ->expects($this->exactly(1))
            ->method('run');

        $taskFactory = $this->getMockBuilder(TaskFactory::class)
            ->setMethods(['createTask'])
            ->disableOriginalConstructor()
            ->getMock();

        $backgroundTask = $this->getMockbuilder(ilMailDeliveryJob::class)
            ->disableOriginalConstructor()
            ->getMock();

        $backgroundTask
            ->expects($this->any())
            ->method('unfoldTask')
            ->willReturn([]);

        $taskFactory
            ->expects($this->exactly(2))
            ->method('createTask')
            ->willReturn($backgroundTask);

        $worker = new ilMassMailTaskProcessor(
            $taskManager,
            $taskFactory,
            $this->languageMock,
            $this->loggerMock,
            $this->dicMock,
            new ilMailValueObjectJsonService(),
            'SomeAnonymousUserId'
        );

        $mailValueObjects = [];

        $mailValueObjects[] = new ilMailValueObject(
            'ilias@server.com',
            'somebody@iliase.de',
            '',
            '',
            'That is awesome!',
            'Dear Steve, great!',
            null
        );

        $mailValueObjects[] = new ilMailValueObject(
            'ilias@server.com',
            'somebodyelse@iliase.de',
            '',
            '',
            'Greate',
            'Steve, Steve, Steve. Wait that is not Steve',
            null
        );

        $userId = 100;
        $contextId = '5';
        $contextParameters = [];

        $worker->run(
            $mailValueObjects,
            $userId,
            $contextId,
            $contextParameters
        );
    }

    /**
     * @throws ilException
     * @throws ReflectionException
     */
    public function testRunThreeTasksInDifferentBuckets() : void
    {
        $taskManager = $this->getMockBuilder(BasicTaskManager::class)
            ->setMethods(['run'])
            ->disableOriginalConstructor()
            ->getMock();

        $taskManager
            ->expects($this->exactly(2))
            ->method('run');

        $taskFactory = $this->getMockBuilder(TaskFactory::class)
            ->setMethods(['createTask'])
            ->disableOriginalConstructor()
            ->getMock();

        $backgroundTask = $this->getMockbuilder(ilMailDeliveryJob::class)
            ->disableOriginalConstructor()
            ->getMock();

        $backgroundTask
            ->expects($this->any())
            ->method('unfoldTask')
            ->willReturn([]);

        $taskFactory
            ->expects($this->exactly(4))
            ->method('createTask')
            ->willReturn($backgroundTask);

        $worker = new ilMassMailTaskProcessor(
            $taskManager,
            $taskFactory,
            $this->languageMock,
            $this->loggerMock,
            $this->dicMock,
            new ilMailValueObjectJsonService(),
            'SomeAnonymousUserId'
        );

        $mailValueObjects = [];

        $mailValueObjects[] = new ilMailValueObject(
            'ilias@server.com',
            'somebody@iliase.de',
            '',
            '',
            'That is awesome!',
            'Dear Steve, great!',
            null
        );

        $mailValueObjects[] = new ilMailValueObject(
            'ilias@server.com',
            'somebodyelse@iliase.de',
            '',
            '',
            'Greate',
            'Steve, Steve, Steve. Wait that is not Steve',
            null
        );

        $mailValueObjects[] = new ilMailValueObject(
            'ilias@server.com',
            'somebody@iliase.de',
            '',
            '',
            'That is awesome!',
            'Hey Alan! Alan! Alan!',
            null
        );

        $userId = 100;
        $contextId = '5';
        $contextParameters = [];

        $worker->run(
            $mailValueObjects,
            $userId,
            $contextId,
            $contextParameters,
            2
        );
    }

    /**
     * @throws ReflectionException
     * @throws ilException
     */
    public function testRunHasWrongTypeAndWillResultInException() : void
    {
        $this->expectException(ilException::class);

        $taskManager = $this->getMockBuilder(BasicTaskManager::class)
            ->setMethods(['run'])
            ->disableOriginalConstructor()
            ->getMock();

        $taskManager
            ->expects($this->never())
            ->method('run');

        $taskFactory = $this->getMockBuilder(TaskFactory::class)
            ->setMethods(['createTask'])
            ->disableOriginalConstructor()
            ->getMock();

        $backgroundTask = $this->getMockbuilder(ilMailDeliveryJob::class)
            ->disableOriginalConstructor()
            ->getMock();

        $backgroundTask
            ->expects($this->any())
            ->method('unfoldTask')
            ->willReturn([]);

        $taskFactory
            ->expects($this->never())
            ->method('createTask')
            ->willReturn($backgroundTask);

        $worker = new ilMassMailTaskProcessor(
            $taskManager,
            $taskFactory,
            $this->languageMock,
            $this->loggerMock,
            $this->dicMock,
            new ilMailValueObjectJsonService(),
            'SomeAnonymousUserId'
        );

        $mailValueObjects = [];

        $mailValueObjects[] = new ilMailValueObject(
            'ilias@server.com',
            'somebody@iliase.de',
            '',
            '',
            'That is awesome!',
            'Dear Steve, great!',
            null
        );

        $mailValueObjects[] = new ilMailValueObject(
            'ilias@server.com',
            'somebodyelse@iliase.de',
            '',
            '',
            'Greate',
            'Steve, Steve, Steve. Wait that is not Steve',
            null
        );

        $mailValueObjects[] = 'This should fail';

        $userId = 100;
        $contextId = '5';
        $contextParameters = [];

        $worker->run(
            $mailValueObjects,
            $userId,
            $contextId,
            $contextParameters,
            2
        );
    }
}
