<?php
/**
 * Class XAccelTest
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 */

namespace ILIAS\FileDelivery\FileDeliveryTypes;

require_once('./libs/composer/vendor/autoload.php');

use ILIAS\DI\Container;
use ILIAS\DI\HTTPServices;
use ILIAS\HTTP\Response\ResponseHeader;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
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
class XAccelTest extends MockeryTestCase {

	/**
	 * @var \Mockery\MockInterface
	 */
	private $httpServiceMock;
	/**
	 * @var \Mockery\MockInterface
	 */
	private $containerMock;


	protected function setUp()
	{
		parent::setUp();

		$this->httpServiceMock = Mockery::mock(HTTPServices::class);
		$this->httpServiceMock->shouldIgnoreMissing();

		//init request and response handling
		$this->containerMock = Mockery::mock(Container::class);

		$this->containerMock->shouldIgnoreMissing();
		$GLOBALS["DIC"] = $this->containerMock;

		//set remote address to localhost
		//$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

		require_once './Services/FileDelivery/classes/FileDeliveryTypes/XAccel.php';
	}


	/**
	 * @Test
	 */
	public function testPrepareWhichShouldSucceed()
	{

		$expectedContentValue = '';

		$this->containerMock->shouldReceive("http")->times(1)->andReturn($this->httpServiceMock);

		$response = Mockery::mock(ResponseInterface::class);
		$response->shouldIgnoreMissing()->shouldReceive("withHeader")->times(1)
		         ->withArgs([ ResponseHeader::CONTENT_TYPE, $expectedContentValue ])
		         ->andReturnSelf();

		$this->httpServiceMock->shouldReceive("response")->times(1)->withNoArgs()
		                      ->andReturn($response)->getMock()->shouldReceive("saveResponse")
		                      ->times(1)->withArgs([ $response ]);

		$xAccel = new XAccel();
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

		$this->containerMock->shouldReceive("http")->times(1)->andReturn($this->httpServiceMock);

		$response = Mockery::mock(\Psr\Http\Message\ResponseInterface::class);
		$response->shouldIgnoreMissing()->shouldReceive("withHeader")->times(1)
		         ->withArgs([ $expectedHeader, $path ])->andReturnSelf();

		$this->httpServiceMock->shouldReceive("response")->times(1)->withNoArgs()
		                      ->andReturn($response)->getMock()->shouldReceive("saveResponse")
		                      ->times(1)->withArgs([ $response ])->getMock()
		                      ->shouldReceive("renderResponse")->times(1)->withNoArgs();

		$xAccel = new XAccel();
		$xAccel->deliver($path);
	}


	/**
	 * @Test
	 */
	public function testDeliverWithDataPathWhichShouldSucceed()
	{

		$expectedHeader = 'X-Accel-Redirect';
		$path = './data/path/to/what/ever';
		$expectedPath = '/secured-data/path/to/what/ever';

		$this->containerMock->shouldReceive("http")->times(1)->andReturn($this->httpServiceMock);

		$response = Mockery::mock(ResponseInterface::class);
		$response->shouldIgnoreMissing()->shouldReceive("withHeader")->times(1)
		         ->withArgs([ $expectedHeader, $expectedPath ])->andReturnSelf();

		$this->httpServiceMock->shouldReceive("response")->times(1)->withNoArgs()
		                      ->andReturn($response)->getMock()->shouldReceive("saveResponse")
		                      ->times(1)->withArgs([ $response ])->getMock()
		                      ->shouldReceive("renderResponse")->times(1)->withNoArgs();

		$xAccel = new XAccel();
		$xAccel->deliver($path);
	}
}
