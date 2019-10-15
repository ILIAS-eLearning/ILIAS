<?php
/**
 * Class HTTPServicesTest
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 */

namespace ILIAS\DI;

use ILIAS\HTTP\Cookies\CookieJarFactory;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\HTTP\Request\RequestFactory;
use ILIAS\HTTP\Response\ResponseFactory;
use ILIAS\HTTP\Response\Sender\ResponseSenderStrategy;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class HTTPServicesTest
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
    private $mockRequestFactory;
    /**
     * @var ResponseFactory|MockObject $mockResponseFactory
     */
    private $mockResponseFactory;
    /**
     * @var CookieJarFactory|MockObject $mockCookieJarFactory
     */
    private $mockCookieJarFactory;
    /**
     * @var ResponseSenderStrategy|MockObject $mockSenderStrategy
     */
    private $mockSenderStrategy;
    /**
     * @var GlobalHttpState $httpState
     */
    private $httpState;


    protected function setUp() : void
    {
        parent::setUp();
        // $this->mockRequestFactory = \Mockery::mock('alias:' . RequestFactory::class);
        $this->mockRequestFactory = $this->getMockBuilder(RequestFactory::class)->setMethods(['create'])->getMock();

        // $this->mockResponseFactory = \Mockery::mock('alias:' . ResponseFactory::class);
        $this->mockResponseFactory = $this->getMockBuilder(ResponseFactory::class)->setMethods(['create'])->getMock();

        // $this->mockSenderStrategy = \Mockery::mock('alias:' . ResponseSenderStrategy::class);
        $this->mockSenderStrategy = $this->getMockBuilder(ResponseSenderStrategy::class)->getMock();

        // $this->mockCookieJarFactory = \Mockery::mock('alias:' . CookieJarFactory::class);
        $this->mockCookieJarFactory = $this->getMockBuilder(CookieJarFactory::class)->getMock();

        //setup http state
        $this->httpState = new HTTPServices($this->mockSenderStrategy, $this->mockCookieJarFactory, $this->mockRequestFactory, $this->mockResponseFactory);
    }


    /**
     * @Test
     */
    public function testRequestWhichShouldGenerateANewRequestOnce()
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
    public function testResponseWhichShouldGenerateANewResponseOnce()
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
