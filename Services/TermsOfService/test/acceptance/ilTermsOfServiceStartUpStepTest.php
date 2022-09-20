<?php

declare(strict_types=1);

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

use ILIAS\DI\Container;
use ILIAS\DI\LoggingServices;
use ILIAS\HTTP\GlobalHttpState;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ilTermsOfServiceStartUpStepTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceStartUpStepTest extends ilTermsOfServiceBaseTest
{
    public function testUserShouldBeForcedToAcceptTermsOfServiceWhenNotDoingItYetInCurrentRequest(): void
    {
        $dic = new Container();

        $ctrl = $this->createMock(ilCtrlInterface::class);

        $ctrl
            ->method('getCmdClass')
            ->willReturn(ilDashboardGUI::class);

        $ctrl
            ->method('getCmd')
            ->willReturn('');

        $ctrl
            ->expects($this->once())
            ->method('redirectToURL');
        $dic['ilCtrl'] = static function () use ($ctrl): ilCtrlInterface {
            return $ctrl;
        };

        $user = $this
            ->getMockBuilder(ilObjUser::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['hasToAcceptTermsOfService', 'checkTimeLimit', 'hasToAcceptTermsOfServiceInSession'])
            ->getMock();

        $user
            ->expects($this->atLeast(1))
            ->method('hasToAcceptTermsOfService')
            ->willReturn(true);

        $user
            ->method('checkTimeLimit')
            ->willReturn(true);

        $user
            ->expects($this->atLeast(1))
            ->method('hasToAcceptTermsOfServiceInSession')
            ->willReturn(true);
        $dic['ilUser'] = static function () use ($user): ilObjUser {
            return $user;
        };

        $request = $this->createMock(ServerRequestInterface::class);
        $http = $this->createMock(GlobalHttpState::class);

        $http
            ->method('request')
            ->willReturn($request);
        $dic['http'] = static function () use ($http): GlobalHttpState {
            return $http;
        };

        $evaluator = $this->createMock(ilTermsOfServiceDocumentEvaluation::class);
        $dic['tos.document.evaluator'] = static function () use ($evaluator): ilTermsOfServiceDocumentEvaluation {
            return $evaluator;
        };

        $criterionFactory = $this->createMock(ilTermsOfServiceCriterionTypeFactoryInterface::class);
        $dic['tos.criteria.type.factory'] = static function () use (
            $criterionFactory
        ): ilTermsOfServiceCriterionTypeFactoryInterface {
            return $criterionFactory;
        };

        $requestInterceptor = new ilTermsOfServiceAcceptanceStartUpStep($dic);

        $this->assertTrue($requestInterceptor->shouldInterceptRequest());
        $this->assertTrue($requestInterceptor->shouldStoreRequestTarget());
        $requestInterceptor->execute();
    }

    public function testUserShouldNotBeForcedToAcceptTermsOfServiceWhenDoingItAlreadyInCurrentRequest(): void
    {
        $dic = new Container();

        $ctrl = $this->createMock(ilCtrlInterface::class);

        $ctrl
            ->expects($this->atLeast(1))
            ->method('getCmdClass')
            ->willReturn(ilStartUpGUI::class);

        $ctrl
            ->expects($this->atLeast(1))
            ->method('getCmd')
            ->willReturn('getacceptance');
        $dic['ilCtrl'] = static function () use ($ctrl): ilCtrlInterface {
            return $ctrl;
        };

        $user = $this
            ->getMockBuilder(ilObjUser::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['hasToAcceptTermsOfService', 'checkTimeLimit', 'hasToAcceptTermsOfServiceInSession'])
            ->getMock();

        $user
            ->method('hasToAcceptTermsOfService')
            ->willReturn(true);

        $user
            ->method('checkTimeLimit')
            ->willReturn(true);

        $user
            ->method('hasToAcceptTermsOfServiceInSession')
            ->willReturn(true);
        $dic['ilUser'] = static function () use ($user): ilObjUser {
            return $user;
        };

        $request = $this->createMock(ServerRequestInterface::class);
        $http = $this->createMock(GlobalHttpState::class);

        $http
            ->method('request')
            ->willReturn($request);
        $dic['http'] = static function () use ($http): GlobalHttpState {
            return $http;
        };

        $evaluator = $this->createMock(ilTermsOfServiceDocumentEvaluation::class);
        $dic['tos.document.evaluator'] = static function () use ($evaluator): ilTermsOfServiceDocumentEvaluation {
            return $evaluator;
        };

        $criterionFactory = $this->createMock(ilTermsOfServiceCriterionTypeFactoryInterface::class);
        $dic['tos.criteria.type.factory'] = static function () use (
            $criterionFactory
        ): ilTermsOfServiceCriterionTypeFactoryInterface {
            return $criterionFactory;
        };

        $requestInterceptor = new ilTermsOfServiceAcceptanceStartUpStep($dic);

        $this->assertFalse($requestInterceptor->shouldInterceptRequest());
    }

    public function userProvider(): array
    {
        $user1 = $this
            ->getMockBuilder(ilObjUser::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['hasToAcceptTermsOfService', 'checkTimeLimit', 'hasToAcceptTermsOfServiceInSession'])
            ->getMock();

        $user1
            ->method('hasToAcceptTermsOfService')
            ->willReturn(false);

        $user1
            ->method('checkTimeLimit')
            ->willReturn(true);

        $user1
            ->method('hasToAcceptTermsOfServiceInSession')
            ->willReturn(true);

        $user2 = $this
            ->getMockBuilder(ilObjUser::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['hasToAcceptTermsOfService', 'checkTimeLimit', 'hasToAcceptTermsOfServiceInSession'])
            ->getMock();

        $user2
            ->method('hasToAcceptTermsOfService')
            ->willReturn(true);

        $user2
            ->method('checkTimeLimit')
            ->willReturn(false);

        $user2
            ->method('hasToAcceptTermsOfServiceInSession')
            ->willReturn(true);

        $user3 = $this
            ->getMockBuilder(ilObjUser::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['hasToAcceptTermsOfService', 'checkTimeLimit', 'hasToAcceptTermsOfServiceInSession'])
            ->getMock();

        $user3
            ->method('hasToAcceptTermsOfService')
            ->willReturn(true);

        $user3
            ->method('checkTimeLimit')
            ->willReturn(true);

        $user3
            ->method('hasToAcceptTermsOfServiceInSession')
            ->willReturn(false);

        return [
            'User 1' => [$user1],
            'User 2' => [$user2],
            'User 3' => [$user3],
        ];
    }

    /**
     * @dataProvider userProvider
     */
    public function testUserShouldNotBeForcedToAcceptTermsOfServiceWhenAlreadyDone(ilObjUser $user): void
    {
        $logger = $this
            ->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $loggingServices = $this
            ->getMockBuilder(LoggingServices::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['root', '__call'])
            ->getMock();
        $loggingServices
            ->method('root')
            ->willReturn($logger);
        $loggingServices
            ->method('__call')
            ->willReturn($logger);

        $dic = new class ($loggingServices) extends Container {
            public function __construct(private LoggingServices $loggingServices)
            {
                parent::__construct();
            }

            public function logger(): LoggingServices
            {
                return $this->loggingServices;
            }
        };

        $ctrl = $this->createMock(ilCtrlInterface::class);

        $ctrl
            ->method('getCmdClass')
            ->willReturn(ilDashboardGUI::class);

        $ctrl
            ->method('getCmd')
            ->willReturn('');
        $dic['ilCtrl'] = static function () use ($ctrl): ilCtrlInterface {
            return $ctrl;
        };

        $request = $this->createMock(ServerRequestInterface::class);
        $http = $this->createMock(GlobalHttpState::class);

        $http
            ->method('request')
            ->willReturn($request);
        $dic['http'] = static function () use ($http): GlobalHttpState {
            return $http;
        };

        $evaluator = $this->createMock(ilTermsOfServiceDocumentEvaluation::class);
        $dic['tos.document.evaluator'] = static function () use ($evaluator): ilTermsOfServiceDocumentEvaluation {
            return $evaluator;
        };

        $criterionFactory = $this->createMock(ilTermsOfServiceCriterionTypeFactoryInterface::class);
        $dic['tos.criteria.type.factory'] = static function () use (
            $criterionFactory
        ): ilTermsOfServiceCriterionTypeFactoryInterface {
            return $criterionFactory;
        };

        $service = $this
            ->getMockBuilder(ilTermsOfServiceHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $service
            ->method('hasToResignAcceptance')
            ->willReturn(false);
        $dic['tos.service'] = static function () use ($service): ilTermsOfServiceHelper {
            return $service;
        };

        $dic['ilUser'] = static function () use ($user): ilObjUser {
            return $user;
        };

        $requestInterceptor = new ilTermsOfServiceAcceptanceStartUpStep($dic);

        $this->assertFalse($requestInterceptor->shouldInterceptRequest());
    }

    /**
     * @dataProvider userProvider
     */
    public function testUserShouldBeForcedToResignTermsOfService(): void
    {
        $logger = $this
            ->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $loggingServices = $this
            ->getMockBuilder(LoggingServices::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['root', '__call'])
            ->getMock();
        $loggingServices
            ->method('root')
            ->willReturn($logger);
        $loggingServices
            ->method('__call')
            ->willReturn($logger);

        $dic = new class ($loggingServices) extends Container {
            public function __construct(private LoggingServices $loggingServices)
            {
                parent::__construct();
            }

            public function logger(): LoggingServices
            {
                return $this->loggingServices;
            }
        };

        $ctrl = $this->createMock(ilCtrlInterface::class);

        $ctrl
            ->method('getCmdClass')
            ->willReturn(ilDashboardGUI::class);

        $ctrl
            ->method('getCmd')
            ->willReturn('');
        $dic['ilCtrl'] = static function () use ($ctrl): ilCtrlInterface {
            return $ctrl;
        };

        $request = $this->createMock(ServerRequestInterface::class);
        $http = $this->createMock(GlobalHttpState::class);

        $http
            ->method('request')
            ->willReturn($request);
        $dic['http'] = static function () use ($http): GlobalHttpState {
            return $http;
        };

        $evaluator = $this->createMock(ilTermsOfServiceDocumentEvaluation::class);
        $dic['tos.document.evaluator'] = static function () use ($evaluator): ilTermsOfServiceDocumentEvaluation {
            return $evaluator;
        };

        $criterionFactory = $this->createMock(ilTermsOfServiceCriterionTypeFactoryInterface::class);
        $dic['tos.criteria.type.factory'] = static function () use (
            $criterionFactory
        ): ilTermsOfServiceCriterionTypeFactoryInterface {
            return $criterionFactory;
        };

        $service = $this
            ->getMockBuilder(ilTermsOfServiceHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $service->expects($this->once())
            ->method('hasToResignAcceptance')
            ->willReturn(true);
        $service->expects($this->once())
            ->method('resetAcceptance');
        $dic['tos.service'] = static function () use ($service): ilTermsOfServiceHelper {
            return $service;
        };

        $user = $this
            ->getMockBuilder(ilObjUser::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['hasToAcceptTermsOfService', 'checkTimeLimit', 'hasToAcceptTermsOfServiceInSession'])
            ->getMock();
        $user
            ->method('hasToAcceptTermsOfService')
            ->willReturn(false);
        $user
            ->method('checkTimeLimit')
            ->willReturn(true);
        $user
            ->method('hasToAcceptTermsOfServiceInSession')
            ->willReturn(true);
        $dic['ilUser'] = static function () use ($user): ilObjUser {
            return $user;
        };

        $requestInterceptor = new ilTermsOfServiceAcceptanceStartUpStep($dic);

        $this->assertTrue($requestInterceptor->shouldInterceptRequest());
    }
}
