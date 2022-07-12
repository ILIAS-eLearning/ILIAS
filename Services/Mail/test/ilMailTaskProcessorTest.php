<?php declare(strict_types=1);

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

use ILIAS\BackgroundTasks\Implementation\TaskManager\BasicTaskManager;
use ILIAS\BackgroundTasks\Task\TaskFactory;
use ILIAS\DI\Container;

/**
 * Class ilMailTaskProcessorTest
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilMailTaskProcessorTest extends ilMailBaseTest
{
    private ilLanguage $languageMock;
    private Container $dicMock;
    private ilLogger $loggerMock;
    protected const SOME_USER_ID = 113;

    /**
     * @throws ReflectionException
     */
    protected function setUp() : void
    {
        parent::setUp();

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
            ->onlyMethods(['run'])
            ->disableOriginalConstructor()
            ->getMock();

        $taskManager
            ->expects($this->once())
            ->method('run');

        $taskFactory = $this->getMockBuilder(ILIAS\BackgroundTasks\Task\TaskFactory::class)
            ->onlyMethods(['createTask'])
            ->disableOriginalConstructor()
            ->getMock();

        $backgroundTask = $this->getMockBuilder(ilMailDeliveryJob::class)
            ->disableOriginalConstructor()
            ->getMock();

        $backgroundTask->method('unfoldTask')
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
            self::SOME_USER_ID
        );

        $mailValueObject = new ilMailValueObject(
            'ilias@server.com',
            'somebody@iliase.de',
            '',
            '',
            'That is awesome!',
            'Dear Steve, great!',
            []
        );

        $mailValueObjects = [
            $mailValueObject,
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
            ->onlyMethods(['run'])
            ->disableOriginalConstructor()
            ->getMock();

        $taskManager
            ->expects($this->once())
            ->method('run');

        $taskFactory = $this->getMockBuilder(TaskFactory::class)
            ->onlyMethods(['createTask'])
            ->disableOriginalConstructor()
            ->getMock();

        $backgroundTask = $this->getMockBuilder(ilMailDeliveryJob::class)
            ->disableOriginalConstructor()
            ->getMock();

        $backgroundTask
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
            self::SOME_USER_ID
        );

        $mailValueObjects = [];

        $mailValueObjects[] = new ilMailValueObject(
            'ilias@server.com',
            'somebody@iliase.de',
            '',
            '',
            'That is awesome!',
            'Dear Steve, great!',
            []
        );

        $mailValueObjects[] = new ilMailValueObject(
            'ilias@server.com',
            'somebodyelse@iliase.de',
            '',
            '',
            'Greate',
            'Steve, Steve, Steve. Wait that is not Steve',
            []
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
            ->onlyMethods(['run'])
            ->disableOriginalConstructor()
            ->getMock();

        $taskManager
            ->expects($this->exactly(2))
            ->method('run');

        $taskFactory = $this->getMockBuilder(TaskFactory::class)
            ->onlyMethods(['createTask'])
            ->disableOriginalConstructor()
            ->getMock();

        $backgroundTask = $this->getMockBuilder(ilMailDeliveryJob::class)
            ->disableOriginalConstructor()
            ->getMock();

        $backgroundTask
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
            self::SOME_USER_ID
        );

        $mailValueObjects = [];

        $mailValueObjects[] = new ilMailValueObject(
            'ilias@server.com',
            'somebody@iliase.de',
            '',
            '',
            'That is awesome!',
            'Dear Steve, great!',
            []
        );

        $mailValueObjects[] = new ilMailValueObject(
            'ilias@server.com',
            'somebodyelse@iliase.de',
            '',
            '',
            'Greate',
            'Steve, Steve, Steve. Wait that is not Steve',
            []
        );

        $mailValueObjects[] = new ilMailValueObject(
            'ilias@server.com',
            'somebody@iliase.de',
            '',
            '',
            'That is awesome!',
            'Hey Alan! Alan! Alan!',
            []
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
            ->onlyMethods(['run'])
            ->disableOriginalConstructor()
            ->getMock();

        $taskManager
            ->expects($this->never())
            ->method('run');

        $taskFactory = $this->getMockBuilder(TaskFactory::class)
            ->onlyMethods(['createTask'])
            ->disableOriginalConstructor()
            ->getMock();

        $backgroundTask = $this->getMockBuilder(ilMailDeliveryJob::class)
            ->disableOriginalConstructor()
            ->getMock();

        $backgroundTask
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
            self::SOME_USER_ID
        );

        $mailValueObjects = [];

        $mailValueObjects[] = new ilMailValueObject(
            'ilias@server.com',
            'somebody@iliase.de',
            '',
            '',
            'That is awesome!',
            'Dear Steve, great!',
            []
        );

        $mailValueObjects[] = new ilMailValueObject(
            'ilias@server.com',
            'somebodyelse@iliase.de',
            '',
            '',
            'Greate',
            'Steve, Steve, Steve. Wait that is not Steve',
            []
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
