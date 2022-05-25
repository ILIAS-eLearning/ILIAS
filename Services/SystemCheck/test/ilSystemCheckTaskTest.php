<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use ILIAS\DI\Container;

/**
 * Unit tests for class ilDidacticTemplate
 * @author  Stefan Meyer <meyer@leifos.de>
 * @ingroup ServicesSystemCheck
 */
class ilSystemCheckTaskTest extends TestCase
{
    protected Container $dic;

    protected function setUp() : void
    {
        parent::setUp();
        $this->initDependencies();
    }

    public function testConstruct() : void
    {
        $task = new ilSCTask(0);
        $this->assertInstanceOf(ilSCTask::class, $task);
    }

    public function testLastUpdate() : void
    {
        $this->getMockBuilder(ilDateTime::class)
             ->disableOriginalConstructor()
             ->getMock();

        $task = new ilSCTask();
        $last_update = $task->getLastUpdate();
        $this->assertInstanceOf(ilDateTime::class, $last_update);
    }

    protected function setGlobalVariable(string $name, $value) : void
    {
        global $DIC;

        $GLOBALS[$name] = $value;
        unset($DIC[$name]);
        $DIC[$name] = static function (Container $c) use ($value) {
            return $value;
        };
    }

    protected function initDependencies() : void
    {
        $this->dic = new Container();
        $GLOBALS['DIC'] = $this->dic;
        $this->setGlobalVariable(
            'ilDB',
            $this->createMock(ilDBInterface::class)
        );
        $logger = $this->getMockBuilder(ilLogger::class)
                       ->disableOriginalConstructor()
                       ->getMock();

        $logger_factory = $this->getMockBuilder(ilLoggerFactory::class)
                               ->disableOriginalConstructor()
                               ->onlyMethods(['getComponentLogger'])
                               ->getMock();
        $logger_factory->method('getComponentLogger')->willReturn($logger);
        $this->setGlobalVariable('ilLoggerFactory', $logger_factory);
    }
}
