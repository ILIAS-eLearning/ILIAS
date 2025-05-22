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

declare(strict_types=1);

use ILIAS\BackgroundTasks\Implementation\TaskManager\BasicTaskManager;
use ILIAS\BackgroundTasks\Task\TaskFactory;
use ILIAS\DI\Container;

class ilMailTaskProcessorTest extends ilMailBaseTestCase
{
    protected const int SOME_USER_ID = 113;

    private ilLanguage $language_mock;
    private Container $dic_mock;
    private ilLogger $logger_mock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->language_mock = $this->getMockBuilder(ilLanguage::class)
                                    ->disableOriginalConstructor()
                                    ->getMock();

        $this->dic_mock = $this->getMockBuilder(Container::class)
                               ->disableOriginalConstructor()
                               ->getMock();

        $this->logger_mock = $this->getMockBuilder(ilLogger::class)
                                  ->disableOriginalConstructor()
                                  ->getMock();
    }

    public function testMailValueObjectCannotBeCreatedWithUnsupportedSubjectLength(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $value_object = new ilMailValueObject(
            'ilias@server.com',
            'somebody@iliase.de',
            '',
            '',
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce mollis posuere tincidunt. Phasellus et euismod ligula. Suspendisse dignissim eget dui nec imperdiet. Donec in pretium tellus. Maecenas lacinia eleifend erat ut euismod. Aenean eu malesuada est.',
            'Dear Steve, great!',
            []
        );
    }

    public function testOneTask(): void
    {
        $task_manager = $this->getMockBuilder(BasicTaskManager::class)
            ->onlyMethods(['run'])
            ->disableOriginalConstructor()
            ->getMock();

        $task_manager
            ->expects($this->once())
            ->method('run');

        $task_factory = $this->getMockBuilder(ILIAS\BackgroundTasks\Task\TaskFactory::class)
            ->onlyMethods(['createTask'])
            ->disableOriginalConstructor()
            ->getMock();

        $background_task = $this->getMockBuilder(ilMailDeliveryJob::class)
            ->disableOriginalConstructor()
            ->getMock();

        $background_task->method('unfoldTask')
            ->willReturn([]);

        $task_factory
            ->expects($this->exactly(2))
            ->method('createTask')
            ->willReturn($background_task);


        $worker = new ilMassMailTaskProcessor(
            self::SOME_USER_ID,
            $task_manager,
            $task_factory,
            $this->language_mock,
            $this->logger_mock,
            $this->dic_mock,
            new ilMailValueObjectJsonService()
        );

        $value_object = new ilMailValueObject(
            'ilias@server.com',
            'somebody@iliase.de',
            '',
            '',
            'That is awesome!',
            'Dear Steve, great!',
            []
        );

        $value_objects = [
            $value_object,
        ];

        $usr_id = 100;
        $context_id = '5';
        $context_parameters = [];

        $worker->run(
            $value_objects,
            $usr_id,
            $context_id,
            $context_parameters
        );
    }

    public function testRunTwoTasks(): void
    {
        $task_manager = $this->getMockBuilder(BasicTaskManager::class)
            ->onlyMethods(['run'])
            ->disableOriginalConstructor()
            ->getMock();

        $task_manager
            ->expects($this->once())
            ->method('run');

        $task_factory = $this->getMockBuilder(TaskFactory::class)
            ->onlyMethods(['createTask'])
            ->disableOriginalConstructor()
            ->getMock();

        $background_task = $this->getMockBuilder(ilMailDeliveryJob::class)
            ->disableOriginalConstructor()
            ->getMock();

        $background_task
            ->method('unfoldTask')
            ->willReturn([]);

        $task_factory
            ->expects($this->exactly(2))
            ->method('createTask')
            ->willReturn($background_task);

        $worker = new ilMassMailTaskProcessor(
            self::SOME_USER_ID,
            $task_manager,
            $task_factory,
            $this->language_mock,
            $this->logger_mock,
            $this->dic_mock,
            new ilMailValueObjectJsonService()
        );

        $value_objects = [];

        $value_objects[] = new ilMailValueObject(
            'ilias@server.com',
            'somebody@iliase.de',
            '',
            '',
            'That is awesome!',
            'Dear Steve, great!',
            []
        );

        $value_objects[] = new ilMailValueObject(
            'ilias@server.com',
            'somebodyelse@iliase.de',
            '',
            '',
            'Greate',
            'Steve, Steve, Steve. Wait that is not Steve',
            []
        );

        $usr_id = 100;
        $context_id = '5';
        $context_parameters = [];

        $worker->run(
            $value_objects,
            $usr_id,
            $context_id,
            $context_parameters
        );
    }

    public function testRunThreeTasksInDifferentBuckets(): void
    {
        $task_manager = $this->getMockBuilder(BasicTaskManager::class)
            ->onlyMethods(['run'])
            ->disableOriginalConstructor()
            ->getMock();

        $task_manager
            ->expects($this->exactly(2))
            ->method('run');

        $task_factory = $this->getMockBuilder(TaskFactory::class)
            ->onlyMethods(['createTask'])
            ->disableOriginalConstructor()
            ->getMock();

        $background_task = $this->getMockBuilder(ilMailDeliveryJob::class)
            ->disableOriginalConstructor()
            ->getMock();

        $background_task
            ->method('unfoldTask')
            ->willReturn([]);

        $task_factory
            ->expects($this->exactly(4))
            ->method('createTask')
            ->willReturn($background_task);

        $worker = new ilMassMailTaskProcessor(
            self::SOME_USER_ID,
            $task_manager,
            $task_factory,
            $this->language_mock,
            $this->logger_mock,
            $this->dic_mock,
            new ilMailValueObjectJsonService()
        );

        $value_objects = [];

        $value_objects[] = new ilMailValueObject(
            'ilias@server.com',
            'somebody@iliase.de',
            '',
            '',
            'That is awesome!',
            'Dear Steve, great!',
            []
        );

        $value_objects[] = new ilMailValueObject(
            'ilias@server.com',
            'somebodyelse@iliase.de',
            '',
            '',
            'Greate',
            'Steve, Steve, Steve. Wait that is not Steve',
            []
        );

        $value_objects[] = new ilMailValueObject(
            'ilias@server.com',
            'somebody@iliase.de',
            '',
            '',
            'That is awesome!',
            'Hey Alan! Alan! Alan!',
            []
        );

        $usr_id = 100;
        $context_id = '5';
        $context_parameters = [];

        $worker->run(
            $value_objects,
            $usr_id,
            $context_id,
            $context_parameters,
            2
        );
    }

    public function testRunHasWrongTypeAndWillResultInException(): void
    {
        $this->expectException(ilMailException::class);

        $task_manager = $this->getMockBuilder(BasicTaskManager::class)
            ->onlyMethods(['run'])
            ->disableOriginalConstructor()
            ->getMock();

        $task_manager
            ->expects($this->never())
            ->method('run');

        $task_factory = $this->getMockBuilder(TaskFactory::class)
            ->onlyMethods(['createTask'])
            ->disableOriginalConstructor()
            ->getMock();

        $background_task = $this->getMockBuilder(ilMailDeliveryJob::class)
            ->disableOriginalConstructor()
            ->getMock();

        $background_task
            ->method('unfoldTask')
            ->willReturn([]);

        $task_factory
            ->expects($this->never())
            ->method('createTask')
            ->willReturn($background_task);

        $worker = new ilMassMailTaskProcessor(
            self::SOME_USER_ID,
            $task_manager,
            $task_factory,
            $this->language_mock,
            $this->logger_mock,
            $this->dic_mock,
            new ilMailValueObjectJsonService()
        );

        $value_objects = [];

        $value_objects[] = new ilMailValueObject(
            'ilias@server.com',
            'somebody@iliase.de',
            '',
            '',
            'That is awesome!',
            'Dear Steve, great!',
            []
        );

        $value_objects[] = new ilMailValueObject(
            'ilias@server.com',
            'somebodyelse@iliase.de',
            '',
            '',
            'Greate',
            'Steve, Steve, Steve. Wait that is not Steve',
            []
        );

        $value_objects[] = 'This should fail';

        $usr_id = 100;
        $context_id = '5';
        $context_parameters = [];

        $worker->run(
            $value_objects,
            $usr_id,
            $context_id,
            $context_parameters,
            2
        );
    }
}
