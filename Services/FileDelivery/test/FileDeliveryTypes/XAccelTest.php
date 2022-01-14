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
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private \Mockery\LegacyMockInterface $httpServiceMock;


    protected function setUp() : void
    {
        parent::setUp();

        $this->httpServiceMock = Mockery::mock(Services::class);
        $this->httpServiceMock->shouldIgnoreMissing();
    }


    /**
     * @Test
     */
    public function testPrepareWhichShouldSucceed(): void
    {
        $expectedContentValue = '';

        $response = Mockery::mock(ResponseInterface::class);
        $response->shouldIgnoreMissing()->shouldReceive("withHeader")->times(1)
                 ->withArgs([ ResponseHeader::CONTENT_TYPE, $expectedContentValue ])
                 ->andReturnSelf();

        $this->httpServiceMock->shouldReceive("response")->times(1)->withNoArgs()
                              ->andReturn($response)->getMock()->shouldReceive("saveResponse")
                              ->times(1)->withArgs([ $response ]);

        $xAccel = new XAccel($this->httpServiceMock);
        $result = $xAccel->prepare("this path is never used in this method");

        $this->assertTrue($result);
    }


    /**
     * @Test
     */
    public function testDeliverWithNormalPathWhichShouldSucceed(): void
    {
        $expectedHeader = 'X-Accel-Redirect';
        $path = './normal/path';

        $response = Mockery::mock(ResponseInterface::class);
        $response->shouldIgnoreMissing()->shouldReceive("withHeader")->times(1)
                 ->withArgs([ $expectedHeader, $path ])->andReturnSelf();

        $this->httpServiceMock->shouldReceive("response")->times(1)->withNoArgs()
                              ->andReturn($response)->getMock()->shouldReceive("saveResponse")
                              ->times(1)->withArgs([ $response ])->getMock()
                              ->shouldReceive("sendResponse")->times(1)->withNoArgs();

        $xAccel = new XAccel($this->httpServiceMock);
        $xAccel->deliver($path, false);
    }


    /**
     * @Test
     */
    public function testDeliverWithDataPathWhichShouldSucceed(): void
    {
        $expectedHeader = 'X-Accel-Redirect';
        $path = './data/path/to/what/ever';
        $expectedPath = '/secured-data/path/to/what/ever';

        $response = Mockery::mock(ResponseInterface::class);
        $response->shouldIgnoreMissing()->shouldReceive("withHeader")->times(1)
                 ->withArgs([ $expectedHeader, $expectedPath ])->andReturnSelf();

        $this->httpServiceMock->shouldReceive("response")->times(1)->withNoArgs()
                              ->andReturn($response)->getMock()->shouldReceive("saveResponse")
                              ->times(1)->withArgs([ $response ])->getMock()
                              ->shouldReceive("sendResponse")->times(1)->withNoArgs();

        $xAccel = new XAccel($this->httpServiceMock);
        $xAccel->deliver($path, false);
    }
}
