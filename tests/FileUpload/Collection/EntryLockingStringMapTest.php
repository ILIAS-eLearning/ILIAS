<?php

namespace ILIAS\FileUpload\Collection;

use ILIAS\FileUpload\Collection\Exception\ElementAlreadyExistsException;
use ILIAS\FileUpload\Collection\Exception\NoSuchElementException;

require_once './libs/composer/vendor/autoload.php';

/**
 * Class EntryLockingStringMapTest
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState    disabled
 * @backupGlobals          disabled
 * @backupStaticAttributes disabled
 */
class EntryLockingStringMapTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var EntryLockingStringMap
     */
    private $subject;

    /**
     * @setup
     */
    public function setUp()
    {
        $this->subject = new EntryLockingStringMap();
    }

    /**
     * @Test
     */
    public function testPutValueWhichShouldSucceed()
    {
        $key = "hello";
        $value = "world";
        $this->subject->put($key, $value);
        $result = $this->subject->toArray();

        $this->assertArrayHasKey($key, $result);
        $this->assertEquals($value, $result[$key]);
    }

    /**
     * @Test
     */
    public function testPutValueTwiceWhichShouldFail()
    {
        $key = "hello";
        $value = "world";

        $this->subject->put($key, $value);

        $this->setExpectedException(ElementAlreadyExistsException::class, "Element $key can not be overwritten.");

        $this->subject->put($key, $value);
    }

    /**
     * @Test
     */
    public function testGetWhichShouldSucceed()
    {
        $key = "hello";
        $value = "world";

        $this->subject->put($key, $value);
        $result = $this->subject->get($key);

        $this->assertEquals($value, $result);
    }

    /**
     * @Test
     */
    public function testGetWithoutPutTheValueWhichShouldFail()
    {
        $key = "hello";

        $this->setExpectedException(NoSuchElementException::class, "No meta data associated with key \"$key\".");
        $this->subject->get($key);
    }
}
