<?php declare(strict_types=1);

/* Copyright (c) 2021 - Daniel Weise <daniel.weise@concepts-and-training.de> - Extended GPL, see LICENSE */

namespace ILIAS\Tests\Setup\CLI;

use ILIAS\Setup;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\Factory as DataFactory;
use Symfony\Component\Console\Input\InputInterface;
use ILIAS\Setup\Config;
use ILIAS\Setup\Agent;

class TestConfig implements Config
{
    public function getConfig(string $name)
    {
        return ["a" => "b"];
    }

    public function getKeys()
    {
        return ["a"];
    }
}

class TestObject extends Setup\CLI\AchieveCommand
{
    public function tryParseAgentMethod(string $agent_method) : ?array
    {
        return $this->parseAgentMethod($agent_method);
    }

    public function readAgentConfig(Agent $agent, InputInterface $input) : ?Config
    {
        return new Setup\ConfigCollection(["Test" => new TestConfig()]);
    }
}

class AchieveCommandTest extends TestCase
{
    /**
     * @var Setup\CLI\ConfigReader|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $config_reader;

    /**
     * @var Setup\AgentFinder|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $agent_finder;

    /**
     * @var Refinery
     */
    protected $refinery;

    /**
     * @var Setup\CLI\AchieveCommand
     */
    protected $command;

    public function setUp() : void
    {
        $this->config_reader = $this->createMock(Setup\CLI\ConfigReader::class);
        $this->agent_finder = $this->createMock(Setup\AgentFinder::class);
        $this->refinery = new Refinery($this->createMock(DataFactory::class), $this->createMock(\ilLanguage::class));
        $this->command = new Setup\CLI\AchieveCommand($this->agent_finder, $this->config_reader, [], $this->refinery);
    }

    public function testExecuteWithWrongFormattedCommandString() : void
    {
        $wrong_command = "ilTest:Method";

        $tester = new CommandTester($this->command);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Wrong input format for command.");
        $tester->execute([
            "agent_method" => $wrong_command
        ]);
    }

    public function testExecuteWithAgentWithoutMethod() : void
    {
        $agent = $this->createMock(Setup\AgentCollection::class);
        $command = "ilTest::foo";

        $this->agent_finder
            ->expects($this->once())
            ->method("getAgentByClassName")
            ->with("ilTest")
            ->willReturn($agent)
        ;

        $tester = new CommandTester($this->command);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Method 'foo' not found for 'ilTest'.");
        $tester->execute([
            "agent_method" => $command
        ]);
    }

    public function testExecuteWithoutConfig() : void
    {
        $agent = $this->getMockBuilder(Setup\AgentCollection::class)
                      ->addMethods(["foo"])
                      ->setMethods(["hasConfig"])
                      ->disableOriginalConstructor()
                      ->getMock()
        ;

        $objective = $this->createMock(Setup\Objective::class);
        $env = $this->createMock(Setup\Environment::class);

        $command = "ilTest::foo";

        $this->agent_finder
            ->expects($this->once())
            ->method("getAgentByClassName")
            ->with("ilTest")
            ->willReturn($agent)
        ;

        $agent
            ->expects($this->once())
            ->method("hasConfig")
            ->willReturn(false)
        ;
        $agent
            ->expects(($this->once()))
            ->method("foo")
            ->willReturn($objective)
        ;

        $objective
            ->expects($this->once())
            ->method("getPreconditions")
            ->willReturn([])
        ;
        $objective
            ->expects($this->once())
            ->method("achieve")
            ->willReturn($env)
        ;
        $objective
            ->expects($this->once())
            ->method("isApplicable")
            ->willReturn(true)
        ;

        $tester = new CommandTester($this->command);
        $tester->execute([
            "agent_method" => $command
        ]);
    }

    public function testExecuteWithNoConfig() : void
    {
        $agent = $this->getMockBuilder(Setup\AgentCollection::class)
                      ->addMethods(["foo"])
                      ->setMethods(["hasConfig"])
                      ->disableOriginalConstructor()
                      ->getMock()
        ;

        $command = "ilTest::foo";

        $this->agent_finder
            ->expects($this->once())
            ->method("getAgentByClassName")
            ->with("ilTest")
            ->willReturn($agent)
        ;

        $agent
            ->expects($this->any())
            ->method("hasConfig")
            ->willReturn(true)
        ;

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Agent 'ilTest' needs a config file.");
        $tester = new CommandTester($this->command);
        $tester->execute([
            "agent_method" => $command
        ]);
    }

    public function testExecuteWithConfig() : void
    {
        $agent = $this->getMockBuilder(Setup\AgentCollection::class)
                      ->addMethods(["foo"])
                      ->setMethods(["hasConfig"])
                      ->disableOriginalConstructor()
                      ->getMock()
        ;
        $objective = $this->createMock(Setup\Objective::class);
        $env = $this->createMock(Setup\Environment::class);

        $command = "ilTest::foo";
        $config_file = "config_file";

        $this->agent_finder
            ->expects($this->once())
            ->method("getAgentByClassName")
            ->with("ilTest")
            ->willReturn($agent)
        ;
        $this->agent_finder
            ->expects($this->once())
            ->method("getAgentNameByClassName")
            ->willReturn("Test")
        ;

        $agent
            ->expects($this->any())
            ->method("hasConfig")
            ->willReturn(true)
        ;
        $agent
            ->expects(($this->once()))
            ->method("foo")
            ->with(new TestConfig())
            ->willReturn($objective)
        ;

        $objective
            ->expects($this->once())
            ->method("getPreconditions")
            ->willReturn([])
        ;
        $objective
            ->expects($this->once())
            ->method("achieve")
            ->willReturn($env)
        ;
        $objective
            ->expects($this->once())
            ->method("isApplicable")
            ->willReturn(true)
        ;

        $obj = new TestObject($this->agent_finder, $this->config_reader, [], $this->refinery);
        $tester = new CommandTester($obj);
        $tester->execute([
            "agent_method" => $command,
            "config" => $config_file
        ]);
    }

    public function testParseCommandString() : void
    {
        $cases = [
            ":ilTest::Method" => null,
            "ilTest:Method" => null,
            "ilTest::Method" => ["ilTest", "Method"],
            "ilTestMethod::" => null,
            "::ilTest::Method" => null,
            "ilTest&&Method" => null,
            "123Test::Method" => ["123Test", "Method"]
        ];

        $obj = new TestObject($this->agent_finder, $this->config_reader, [], $this->refinery);

        foreach ($cases as $method => $expected) {
            $result = $obj->tryParseAgentMethod($method);
            $this->assertEquals($expected, $result);
        }
    }
}
