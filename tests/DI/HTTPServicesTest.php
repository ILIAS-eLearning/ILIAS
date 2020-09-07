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
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

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
class HTTPServicesTest extends MockeryTestCase
{

    /**
     * @var RequestFactory|MockInterface $mockRequestFactory
     */
    private $mockRequestFactory;
    /**
     * @var ResponseFactory|MockInterface $mockResponseFactory
     */
    private $mockResponseFactory;
    /**
     * @var CookieJarFactory|MockInterface $mockCookieJarFactory
     */
    private $mockCookieJarFactory;
    /**
     * @var ResponseSenderStrategy|MockInterface $mockSenderStrategy
     */
    private $mockSenderStrategy;
    /**
     * @var GlobalHttpState $httpState
     */
    private $httpState;


    protected function setUp()
    {
        parent::setUp();
        $this->mockRequestFactory = \Mockery::mock(RequestFactory::class);
        $this->mockResponseFactory = \Mockery::mock(ResponseFactory::class);
        $this->mockSenderStrategy = \Mockery::mock(ResponseSenderStrategy::class);
        $this->mockCookieJarFactory = \Mockery::mock(CookieJarFactory::class);

        //setup http state
        $this->httpState = new HTTPServices($this->mockSenderStrategy, $this->mockCookieJarFactory, $this->mockRequestFactory, $this->mockResponseFactory);
    }


    /**
     * @Test
     */
    public function testRequestWhichShouldGenerateANewRequestOnce()
    {
        $expectedRequest = \Mockery::mock(RequestInterface::class);
        $wrongRequest = \Mockery::mock(RequestInterface::class);

        $this->mockRequestFactory->shouldReceive("create")->withNoArgs()->once()->andReturnValues([ $expectedRequest, $wrongRequest ]);

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
        $expectedResponse = \Mockery::mock(ResponseInterface::class);
        $wrongResponse = \Mockery::mock(ResponseInterface::class);

        $this->mockResponseFactory->shouldReceive("create")->withNoArgs()->once()->andReturnValues([ $expectedResponse, $wrongResponse ]);

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
