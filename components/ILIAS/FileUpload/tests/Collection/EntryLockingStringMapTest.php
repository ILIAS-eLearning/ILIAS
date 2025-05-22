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

namespace ILIAS\FileUpload\Collection;

use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\BackupStaticProperties;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\Test;
use ILIAS\FileUpload\Collection\Exception\ElementAlreadyExistsException;
use ILIAS\FileUpload\Collection\Exception\NoSuchElementException;
use PHPUnit\Framework\TestCase;

require_once './vendor/composer/vendor/autoload.php';

/**
 * Class EntryLockingStringMapTest
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 */
#[BackupGlobals(false)]
#[BackupStaticProperties(false)]
#[PreserveGlobalState(false)]
#[RunTestsInSeparateProcesses]
class EntryLockingStringMapTest extends TestCase
{
    /**
     * @var EntryLockingStringMap
     */
    private EntryLockingStringMap $subject;

    /**
     * @setup
     */
    public function setUp(): void
    {
        $this->subject = new EntryLockingStringMap();
    }

    #[Test]
    public function testPutValueWhichShouldSucceed(): void
    {
        $key = "hello";
        $value = "world";
        $this->subject->put($key, $value);
        $result = $this->subject->toArray();

        $this->assertArrayHasKey($key, $result);
        $this->assertEquals($value, $result[$key]);
    }

    #[Test]
    public function testPutValueTwiceWhichShouldFail(): void
    {
        $key = "hello";
        $value = "world";

        $this->subject->put($key, $value);

        $this->expectException(ElementAlreadyExistsException::class);
        $this->expectExceptionMessage("Element $key can not be overwritten.");

        $this->subject->put($key, $value);
    }

    #[Test]
    public function testGetWhichShouldSucceed(): void
    {
        $key = "hello";
        $value = "world";

        $this->subject->put($key, $value);
        $result = $this->subject->get($key);

        $this->assertEquals($value, $result);
    }

    #[Test]
    public function testGetWithoutPutTheValueWhichShouldFail(): void
    {
        $key = "hello";

        $this->expectException(NoSuchElementException::class);
        $this->expectExceptionMessage("No meta data associated with key \"$key\".");
        $this->subject->get($key);
    }
}
