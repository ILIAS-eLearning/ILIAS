<?php
declare(strict_types=1);
/**
 * Class XAccelTest
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 */

namespace ILIAS\FileDelivery\FileDeliveryTypes;

use ILIAS\HTTP\Services;
use ILIAS\HTTP\Response\ResponseHeader;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
 * Class XSendfile
 *
 * @author                 Nicolas Schäfli <ns@studer-raimann.ch>
 *
 * @runInSeparateProcess
 * @preserveGlobalState    disabled
 * @backupGlobals          disabled
 * @backupStaticAttributes disabled
 */
class XAccelTest extends TestCase
{
    /**
     * @var Services|\PHPUnit\Framework\MockObject\MockObject
     */
    public Services $httpServiceMock;

    /**
     * @inheritDoc
     */
    protected function setUp() : void
    {
        $this->httpServiceMock = $this->getMockBuilder(Services::class)
                                      ->disableOriginalConstructor()
                                      ->getMock();
    }

    /**
     * @Test
     */
    public function testPrepareWhichShouldSucceed() : void
    {
        $expectedContentValue = '';

        $response = $this->getMockBuilder(ResponseInterface::class)
                         ->disableOriginalConstructor()
                         ->getMock();

        $response->expects($this->once())
                 ->method('withHeader')
                 ->with(ResponseHeader::CONTENT_TYPE, $expectedContentValue)
                 ->willReturnSelf();

        $this->httpServiceMock->expects($this->once())
                              ->method('response')
                              ->willReturn($response);

        $this->httpServiceMock->expects($this->once())
                              ->method('saveResponse')
                              ->with($response);

        $this->httpServiceMock->expects($this->never())
                              ->method('sendResponse');

        $xAccel = new XAccel($this->httpServiceMock);
        $result = $xAccel->prepare("this path is never used in this method");

        $this->assertTrue($result);
    }

    /**
     * @Test
     */
    public function testDeliverWithNormalPathWhichShouldSucceed() : void
    {
        $expectedHeader = 'X-Accel-Redirect';
        $path = './normal/path';

        $response = $this->getMockBuilder(ResponseInterface::class)
                         ->disableOriginalConstructor()
                         ->getMock();

        $response->expects($this->once())
                 ->method('withHeader')
                 ->with($expectedHeader, $path)
                 ->willReturnSelf();

        $this->httpServiceMock->expects($this->once())
                              ->method('response')
                              ->willReturn($response);

        $this->httpServiceMock->expects($this->once())
                              ->method('saveResponse')
                              ->with($response);

        $this->httpServiceMock->expects($this->once())
                              ->method('sendResponse');


        $xAccel = new XAccel($this->httpServiceMock);
        $xAccel->deliver($path, false);
    }

    /**
     * @Test
     */
    public function testDeliverWithDataPathWhichShouldSucceed() : void
    {
        $expectedHeader = 'X-Accel-Redirect';
        $path = './data/path/to/what/ever';
        $expectedPath = '/secured-data/path/to/what/ever';

        $response = $this->getMockBuilder(ResponseInterface::class)
                         ->disableOriginalConstructor()
                         ->getMock();

        $response->expects($this->once())
                 ->method('withHeader')
                 ->with($expectedHeader, $expectedPath)
                 ->willReturnSelf();

        $this->httpServiceMock->expects($this->once())
                              ->method('response')
                              ->willReturn($response);

        $this->httpServiceMock->expects($this->once())
                              ->method('saveResponse')
                              ->with($response);

        $this->httpServiceMock->expects($this->once())
                              ->method('sendResponse');

        $xAccel = new XAccel($this->httpServiceMock);
        $xAccel->deliver($path, false);
    }
}
