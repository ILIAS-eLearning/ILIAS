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
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Tests\FileDelivery\FileDeliveryTypes;

use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\BackupStaticProperties;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use ILIAS\HTTP\Services;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use ILIAS\FileDelivery\FileDeliveryTypes\XSendfile;

/**
 * Class XSendfile
 *
 * @author                 Nicolas Schäfli <ns@studer-raimann.ch>
 */
#[BackupGlobals(false)]
#[BackupStaticProperties(false)]
#[PreserveGlobalState(false)]
class XSendfileTest extends TestCase
{
    /**
     * @var Services|MockObject
     */
    public Services $httpServiceMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->httpServiceMock = $this->getMockBuilder(Services::class)
                                      ->disableOriginalConstructor()
                                      ->getMock();
    }

    #[Test]
    public function testSendFileWithXSendHeaderWhichShouldSucceed(): void
    {
        $expectedHeader = 'X-Sendfile';
        $filePath = __FILE__;

        $response = $this->getMockBuilder(ResponseInterface::class)
                         ->disableOriginalConstructor()
                         ->getMock();

        $response->expects($this->once())
                 ->method('withHeader')
                 ->with($expectedHeader, $filePath)
                 ->willReturnSelf();

        $this->httpServiceMock->expects($this->once())
                              ->method('response')
                              ->willReturn($response);

        $this->httpServiceMock->expects($this->once())
                              ->method('saveResponse')
                              ->with($response);

        $this->httpServiceMock->expects($this->once())
                              ->method('sendResponse');

        $fileDeliveryType = new XSendfile($this->httpServiceMock);
        $fileDeliveryType->deliver($filePath, false);
    }
}
