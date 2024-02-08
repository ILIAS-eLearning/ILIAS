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

namespace ILIAS\Tests\FileDelivery\FileDeliveryTypes;

use ilException;
use ILIAS\HTTP\Services;
use PHPUnit\Framework\TestCase;
use ILIAS\FileDelivery\FileDeliveryTypes\DeliveryMethod;
use ILIAS\FileDelivery\FileDeliveryTypes\FileDeliveryTypeFactory;
use ILIAS\FileDelivery\FileDeliveryTypes\PHP;
use ILIAS\FileDelivery\FileDeliveryTypes\PHPChunked;

/**
 * Class FileDeliveryTypeFactoryTest
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 *
 * @runInSeparateProcess
 * @preserveGlobalState    disabled
 * @backupGlobals          disabled
 * @backupStaticAttributes disabled
 */
class FileDeliveryTypeFactoryTest extends TestCase
{
    private \ILIAS\FileDelivery\FileDeliveryTypes\FileDeliveryTypeFactory $subject;
    /**
     * @var Services|\PHPUnit\Framework\MockObject\MockObject
     */
    private Services $http;

    protected function setUp(): void
    {
        parent::setUp();

        $this->http = $this->getMockBuilder(Services::class)->disableOriginalConstructor()->getMock();
        $this->subject = new FileDeliveryTypeFactory($this->http);
    }


    /**
     * @Test
     */
    public function testCreatePHPFileDeliveryWhichShouldSucceed(): void
    {
        $result = $this->subject->getInstance(DeliveryMethod::PHP);

        $this->assertInstanceOf(PHP::class, $result);
    }

    /**
     * @Test
     */
    public function testCreatePHPChunkedFileDeliveryWhichShouldSucceed(): void
    {
        $result = $this->subject->getInstance(DeliveryMethod::PHP_CHUNKED);

        $this->assertInstanceOf(PHPChunked::class, $result);
    }


    /**
     * @Test
     */
    public function testCreatePHPFileDeliveryTypeWhichShouldYieldTheSameInstance(): void
    {
        //fetch the php file delivery type two times to check that only one instance is created.
        $firstResult = $this->subject->getInstance(DeliveryMethod::PHP);
        $secondResult = $this->subject->getInstance(DeliveryMethod::PHP);

        $this->assertEquals($firstResult, $secondResult);
    }

    /**
     * @Test
     */
    public function testCreateAnUnknownFileDeliveryTypeWhichShouldFail(): void
    {
        //get instance should throw an exception if the file delivery type is not known.
        $type = 'unknown file delivery type';
        $this->expectException(ilException::class);
        $this->expectExceptionMessage("Unknown file delivery type \"$type\"");

        $this->subject->getInstance('unknown file delivery type');
    }
}
