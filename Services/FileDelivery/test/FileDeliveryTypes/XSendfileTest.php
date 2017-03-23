<?php

namespace ILIAS\FileDelivery\FileDeliveryTypes;

require_once('./libs/composer/vendor/autoload.php');

use ILIAS\DI\HTTPServices;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

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
class XSendfileTest extends MockeryTestCase {

	/**
	 * @var \Mockery\MockInterface
	 */
	private $httpServiceMock;
	/**
	 * @var \Mockery\MockInterface
	 */
	private $containerMock;


	/**
	 * @inheritDoc
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->httpServiceMock = Mockery::mock(HTTPServices::class);
		$this->httpServiceMock->shouldIgnoreMissing();

		//init request and response handling
		$this->containerMock = Mockery::mock('\ILIAS\DI\Container');

		$this->containerMock->shouldIgnoreMissing();
		$GLOBALS["DIC"] = $this->containerMock;

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
		$this->containerMock->shouldReceive("http")->times(1)->andReturn($this->httpServiceMock);

		$response = Mockery::mock(\Psr\Http\Message\ResponseInterface::class);
		$response->shouldIgnoreMissing()->shouldReceive("withHeader")->times(1)
		         ->withArgs([ $expectedHeader, $filePath ])->andReturnSelf();

		$this->httpServiceMock->shouldReceive("response")->times(1)->withNoArgs()
		                      ->andReturn($response)->getMock()->shouldReceive("saveResponse")
		                      ->times(1)->withArgs([ $response ])->getMock()
		                      ->shouldReceive("renderResponse")->times(1)->withNoArgs();

		$fileDeliveryType = new XSendfile();
		$fileDeliveryOk = $fileDeliveryType->deliver($filePath);

		$this->assertTrue($fileDeliveryOk);
	}
}