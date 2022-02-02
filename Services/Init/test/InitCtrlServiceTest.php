<?php declare(strict_types=1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\HTTP\Response\Sender\DefaultResponseSenderStrategy;
use ILIAS\HTTP\Services as HttpService;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\DI\Container;

/**
 * Class InitCtrlServiceTest
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
final class InitCtrlServiceTest extends TestCase
{
    public function testCtrlServiceInitializationWithoutRefinery() : void
    {
        $dic = new Container();
        // $dic['ilDB'] = $this->createMock(ilDBInterface::class);
        $dic['http'] = $this->createMock(HttpService::class);

        $this->expectException(ilCtrlException::class);
        $this->expectExceptionMessage("Cannot initialize ilCtrl if Refinery Factory is not yet available.");
        (new InitCtrlService())->init($dic);
    }

    public function testCtrlServiceInitializationWithoutHttpServices() : void
    {
        $dic = new Container();
        // $dic['ilDB'] = $this->createMock(ilDBInterface::class);
        $dic['refinery'] = $this->createMock(Refinery::class);

        $this->expectException(ilCtrlException::class);
        $this->expectExceptionMessage("Cannot initialize ilCtrl if HTTP Services are not yet available.");
        (new InitCtrlService())->init($dic);
    }

    // public function testCtrlServiceInitializationWithoutDatabase() : void
    // {
    //     $dic = new Container();
    //     $dic['refinery'] = $this->createMock(Refinery::class);
    //     $dic['http'] = $this->createMock(HttpService::class);
    //
    //     $this->expectException(ilCtrlException::class);
    //     $this->expectExceptionMessage("Cannot initialize ilCtrl if Database is not yet available.");
    //     (new InitCtrlService())->init($dic);
    // }

    public function testCtrlServiceInitializationSuccess() : void
    {
        $dic = new Container();
        $dic['refinery'] = $this->createMock(Refinery::class);
        // $dic['ilDB'] = $this->createMock(ilDBInterface::class);
        $dic['http.response_sender_strategy'] = $this->createMock(DefaultResponseSenderStrategy::class);
        $dic['http'] = $this->createMock(HttpService::class);
        $dic['http']
            ->method('request')
            ->willReturn(
                $this->createMock(ServerRequestInterface::class)
            );
        $dic['component.factory'] = $this->createMock(ilComponentFactory::class);

        $this->assertFalse(isset($dic['ilCtrl']));

        (new InitCtrlService())->init($dic);

        $this->assertTrue(isset($dic['ilCtrl']));
        $this->assertInstanceOf(
            ilCtrlInterface::class,
            $dic->ctrl()
        );
    }
}
