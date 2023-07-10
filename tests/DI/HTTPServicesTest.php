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
 */

namespace ILIAS\DI;

use ILIAS\HTTP\Cookies\CookieJarFactory;
use ILIAS\HTTP\Services;
use ILIAS\HTTP\Request\RequestFactory;
use ILIAS\HTTP\Response\ResponseFactory;
use ILIAS\HTTP\Response\Sender\ResponseSenderStrategy;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\HTTP\RawHTTPServices;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\HTTP\Duration\DurationFactory;

/**
 * Class HTTPServicesTest
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 *
 * @package                DI
 *
 * @runInSeparateProcess
 * @preserveGlobalState    disabled
 * @backupGlobals          disabled
 * @backupStaticAttributes disabled
 */
class HTTPServicesTest extends PHPUnitTestCase
{
    /**
     * @var RequestFactory|MockObject $mockRequestFactory
     */
    private RequestFactory $mockRequestFactory;
    /**
     * @var ResponseFactory|MockObject $mockResponseFactory
     */
    private ResponseFactory $mockResponseFactory;
    /**
     * @var CookieJarFactory|MockObject $mockCookieJarFactory
     */
    private CookieJarFactory $mockCookieJarFactory;
    /**
     * @var ResponseSenderStrategy|MockObject $mockSenderStrategy
     */
    private ResponseSenderStrategy $mockSenderStrategy;
    /**
     * @var DurationFactory|MockObject $mockSenderStrategy
     */
    private DurationFactory $mockDurationFactory;
    private GlobalHttpState $httpState;


    protected function setUp(): void
    {
        parent::setUp();
        $this->mockRequestFactory = $this->getMockBuilder(RequestFactory::class)->getMock();

        $this->mockResponseFactory = $this->getMockBuilder(ResponseFactory::class)->getMock();

        $this->mockSenderStrategy = $this->getMockBuilder(ResponseSenderStrategy::class)->getMock();

        $this->mockCookieJarFactory = $this->getMockBuilder(CookieJarFactory::class)->getMock();

        $this->mockDurationFactory = $this->createMock(DurationFactory::class);

        $this->httpState = new RawHTTPServices($this->mockSenderStrategy, $this->mockCookieJarFactory, $this->mockRequestFactory, $this->mockResponseFactory, $this->mockDurationFactory);
    }


    /**
     * @Test
     */
    public function testRequestWhichShouldGenerateANewRequestOnce(): void
    {
        $expectedRequest = $this->getMockBuilder(ServerRequestInterface::class)->getMock();
        $wrongRequest = $this->getMockBuilder(ServerRequestInterface::class)->getMock();

        $this->mockRequestFactory->expects($this->once())
            ->method('create')
            ->willReturn($expectedRequest, $wrongRequest);

        //test method

        //this call should call the expectedRequest factory
        $request1 = $this->httpState->request();

        //this call should not call the factory
        $request2 = $this->httpState->request();

        //make sure that all requests are the same.
        $this->assertEquals($expectedRequest, $request1);
        $this->assertEquals($expectedRequest, $request2);
    }


    /**
     * @Test
     */
    public function testResponseWhichShouldGenerateANewResponseOnce(): void
    {
        $expectedResponse = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $wrongResponse = $this->getMockBuilder(ResponseInterface::class)->getMock();

        $this->mockResponseFactory->expects($this->once())
            ->method('create')
            ->willReturn($expectedResponse, $wrongResponse);

        //test method

        //this call should call the expectedResponse factory
        $response1 = $this->httpState->response();

        //this call should not call the factory
        $response2 = $this->httpState->response();

        //make sure that all requests are the same.
        $this->assertEquals($expectedResponse, $response1);
        $this->assertEquals($expectedResponse, $response2);
    }
}
