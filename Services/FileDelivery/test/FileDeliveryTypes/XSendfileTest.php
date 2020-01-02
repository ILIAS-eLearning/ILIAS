<?php

namespace ILIAS\FileDelivery\FileDeliveryTypes;

require_once('./libs/composer/vendor/autoload.php');

use ILIAS\DI\HTTPServices;
use ILIAS\HTTP\GlobalHttpState;
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
class XSendfileTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /**
     * @var \Mockery\MockInterface | GlobalHttpState
     */
    private $httpServiceMock;


    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->httpServiceMock = Mockery::mock(HTTPServices::class);
        $this->httpServiceMock->shouldIgnoreMissing();

        //set remote address to localhost
        //$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        require_once './Services/FileDelivery/classes/FileDeliveryTypes/XSendfile.php';
    }


    /**
     * @Test
     */
    public function testSendFileWithXSendHeaderWhichShouldSucceed()
    {
        $expectedHeader = 'X-Sendfile';
        $filePath = __FILE__;

        $response = Mockery::mock(ResponseInterface::class);
        $response->shouldIgnoreMissing()->shouldReceive("withHeader")->times(1)
                 ->withArgs([ $expectedHeader, $filePath ])->andReturnSelf();

        $this->httpServiceMock->shouldReceive("response")->times(1)->withNoArgs()
                              ->andReturn($response)->getMock()->shouldReceive("saveResponse")
                              ->times(1)->withArgs([ $response ])->getMock()
                              ->shouldReceive("sendResponse")->times(1)->withNoArgs();

        $fileDeliveryType = new XSendfile($this->httpServiceMock);
        $fileDeliveryOk = $fileDeliveryType->deliver($filePath, false);

        $this->assertTrue($fileDeliveryOk);
    }
}
