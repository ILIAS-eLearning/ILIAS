<?php

namespace ILIAS\FileUpload;

use PHPUnit\Framework\TestCase;

require_once './libs/composer/vendor/autoload.php';

/**
 * Class ScalarTypeCheckAwareTest
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState    disabled
 * @backupGlobals          disabled
 * @backupStaticAttributes disabled
 */
class ScalarTypeCheckAwareTest extends TestCase
{

    /**
     * @var ScalarTypeCheckAware $subject
     */
    private $subject;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->subject = $this->getMockForTrait(ScalarTypeCheckAware::class);
    }

    /**
     * @Test
     */
    public function testStringTypeCheckWithIntTypeArgumentWhichShouldFail()
    {
        $testInt = 1010101;
        $name = 'testInt';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("The $name must be of type string but integer was given.");

        $this->callPrivateMethod($this->subject, 'stringTypeCheck', [$testInt, $name]);
    }

    /**
     * @Test
     */
    public function testIntTypeCheckWithStringTypeArgumentWhichShouldFail()
    {
        $testString = 'Hello World';
        $name = 'testInt';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("The $name must be of type integer but string was given.");

        $this->callPrivateMethod($this->subject, 'intTypeCheck', [$testString, $name]);
    }


    /**
     * Reflection hack to access the private methods of the trait.
     *
     * @param object $object         The object which should be used.
     * @param string $methodName     The name of the method which should be called.
     * @param array  $arguments      The arguments which should be passed to the method.
     */
    private function callPrivateMethod($object, $methodName, array $arguments)
    {
        $class = new \ReflectionClass($object);
        $method = $class->getMethod($methodName);
        $method->setAccessible(true);
        $method->invokeArgs($object, $arguments);
    }
}
