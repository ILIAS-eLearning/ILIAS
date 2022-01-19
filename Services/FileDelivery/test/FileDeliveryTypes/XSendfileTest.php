<?php
declare(strict_types=1);

namespace ILIAS\FileDelivery\FileDeliveryTypes;

use ILIAS\HTTP\Services;
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
class XSendfileTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private \Mockery\LegacyMockInterface $httpServiceMock;


    /**
     * @inheritDoc
     */
    protected function setUp() : void
    {
        parent::setUp();

        $this->httpServiceMock = Mockery::mock(Services::class);
        $this->httpServiceMock->shouldIgnoreMissing();
    }


    /**
     * @Test
     */
    public function testSendFileWithXSendHeaderWhichShouldSucceed(): void
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
