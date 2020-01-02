<?php
/**
 * Class XAccelTest
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 */

namespace ILIAS\FileDelivery\FileDeliveryTypes;

require_once('./libs/composer/vendor/autoload.php');

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\HTTP\Response\ResponseHeader;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

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

    /**
     * @var \Mockery\MockInterface | GlobalHttpState
     */
    private $httpServiceMock;


    protected function setUp()
    {
        parent::setUp();

        $this->httpServiceMock = Mockery::mock(GlobalHttpState::class);
        $this->httpServiceMock->shouldIgnoreMissing();

        //set remote address to localhost
        // $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        require_once './Services/FileDelivery/classes/FileDeliveryTypes/XAccel.php';
    }


    /**
     * @Test
     */
    public function testPrepareWhichShouldSucceed()
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
    public function testDeliverWithNormalPathWhichShouldSucceed()
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
    public function testDeliverWithDataPathWhichShouldSucceed()
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
