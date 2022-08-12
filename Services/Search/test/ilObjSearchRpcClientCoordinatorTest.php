<?php declare(strict_types=1);

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

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for class ilObjSearchRpcClientCoordinator
 * @author Tim Schmitz <schmitz@leifos.de>
 */
class ilObjSearchRpcClientCoordinatorTest extends TestCase
{
    public function testRefreshLuceneSettings() : void
    {
        $src_logger = $this->getMockBuilder(ilLogger::class)
                       ->disableOriginalConstructor()
                       ->onlyMethods(['error'])
                       ->getMock();
        $src_logger->expects($this->never())
                   ->method('error');

        $settings = $this->getMockBuilder(ilSetting::class)
                         ->disableOriginalConstructor()
                         ->onlyMethods(['get'])
                         ->getMock();
        $settings->expects($this->once())
                 ->method('get')
                 ->with('inst_id', '0')
                 ->willReturn('test');

        $rpc_client = $this->getMockBuilder(ilRpcClient::class)
                           ->disableOriginalConstructor()
                           ->addMethods(['refreshSettings'])
                           ->getMock();
        $rpc_client->expects($this->once())
                   ->method('refreshSettings')
                   ->with('id_test');

        $coord = $this->getMockBuilder(ilObjSearchRpcClientCoordinator::class)
                      ->setConstructorArgs([$settings, $src_logger])
                      ->onlyMethods(['getRpcClient', 'getClientId'])
                      ->getMock();
        $coord->expects($this->once())
              ->method('getClientId')
              ->willReturn('id');
        $coord->expects($this->once())
              ->method('getRpcClient')
              ->willReturn($rpc_client);

        $this->assertSame(true, $coord->refreshLuceneSettings());
    }

    public function testRefreshLuceneSettingsException() : void
    {
        $src_logger = $this->getMockBuilder(ilLogger::class)
                           ->disableOriginalConstructor()
                           ->onlyMethods(['error'])
                           ->getMock();
        $src_logger->expects($this->once())
                   ->method('error')
                   ->with(
                       'Refresh of lucene server settings ' .
                       'failed with message: message'
                   );

        $settings = $this->getMockBuilder(ilSetting::class)
                         ->disableOriginalConstructor()
                         ->onlyMethods(['get'])
                         ->getMock();
        $settings->expects($this->once())
                 ->method('get')
                 ->with('inst_id', '0')
                 ->willReturn('test');

        $rpc_client = $this->getMockBuilder(ilRpcClient::class)
                           ->disableOriginalConstructor()
                           ->addMethods(['refreshSettings'])
                           ->getMock();
        $rpc_client->expects($this->once())
                   ->method('refreshSettings')
                   ->with('id_test')
                   ->willThrowException(new Exception('message'));

        $coord = $this->getMockBuilder(ilObjSearchRpcClientCoordinator::class)
                      ->setConstructorArgs([$settings, $src_logger])
                      ->onlyMethods(['getRpcClient', 'getClientId'])
                      ->getMock();
        $coord->expects($this->once())
              ->method('getClientId')
              ->willReturn('id');
        $coord->expects($this->once())
              ->method('getRpcClient')
              ->willReturn($rpc_client);

        $this->expectException(Exception::class);
        $coord->refreshLuceneSettings();
    }
}
