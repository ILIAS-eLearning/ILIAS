<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use ILIAS\DI\Container;

class ilRepositoryTreeTest extends TestCase
{
    protected Container $dic;

    protected function setUp(): void
    {
        $this->initRepositoryTreeDependencies();
        parent::setUp();
    }

    public function testTreeConstruct(): void
    {
        $tree = new ilTree(1);
        $this->assertTrue($tree instanceof ilTree);
    }

    public function testInitLanguage(): void
    {

        // no global user available
        $tree = new ilTree(1);
        $tree->initLangCode();
        $tree_reflection = new ReflectionProperty($tree, 'lang_code');
        $tree_reflection->setAccessible(true);
        $this->assertEquals('en', $tree_reflection->getValue($tree));

        // user getCurrentLanguage() from session is empty
        $tree = new ilTree(1);

        $user = $this->getMockBuilder(ilObjUser::class)
                     ->disableOriginalConstructor()
                     ->onlyMethods(['getCurrentLanguage'])
                     ->getMock();
        $user->method('getCurrentLanguage')->willReturn('');
        $this->setGlobalVariable('ilUser', $user);
        $tree->initLangCode();
        $tree_reflection = new ReflectionProperty($tree, 'lang_code');
        $tree_reflection->setAccessible(true);
        $this->assertEquals('en', $tree_reflection->getValue($tree));
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    protected function setGlobalVariable(string $name, $value): void
    {
        global $DIC;

        $GLOBALS[$name] = $value;
        unset($DIC[$name]);
        $DIC[$name] = static function (\ILIAS\DI\Container $c) use ($value) {
            return $value;
        };
    }

    protected function initRepositoryTreeDependencies(): void
    {
        $this->dic = new Container();
        $GLOBALS['DIC'] = $this->dic;

        $this->setGlobalVariable('ilDB', $this->createMock(ilDBInterface::class));
        $this->setGlobalVariable('ilAppEventHandler', $this->createMock(ilAppEventHandler::class));

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
