<?php declare(strict_types=1);

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\DI\Container;
use ILIAS\DI\LoggingServices;
use ILIAS\HTTP\Services;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ilTermsOfServiceRequestTargetAdjustmentCaseTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceRequestTargetAdjustmentCaseTest extends ilTermsOfServiceBaseTest
{
    public function testUserShouldBeForcedToAcceptTermsOfServiceWhenNotDoingItYetInCurrentRequest() : void
    {
        $dic = new Container();

        $ctrl = $this
            ->getMockBuilder(ilCtrl::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['redirectToURL', 'getCmdClass', 'getCmd'])
            ->getMock();

        $ctrl
            ->method('getCmdClass')
            ->willReturn('ilDashboardGUI');

        $ctrl
            ->method('getCmd')
            ->willReturn('');

        $ctrl
            ->expects($this->once())
            ->method('redirectToURL');
        $dic['ilCtrl'] = static function () use ($ctrl) : ilCtrlInterface {
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
        $dic['ilUser'] = static function () use ($user) : ilObjUser {
            return $user;
        };

        $request = $this
            ->getMockBuilder(ServerRequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $http = $this
            ->getMockBuilder(Services::class)
            ->disableOriginalConstructor()
            ->getMock();

        $http
            ->method('request')
            ->willReturn($request);
        $dic['http'] = static function () use ($http) : Services {
            return $http;
        };

        $evaluator = $this
            ->getMockBuilder(ilTermsOfServiceDocumentEvaluation::class)
            ->getMock();
        $dic['tos.document.evaluator'] = static function () use ($evaluator) : ilTermsOfServiceDocumentEvaluation {
            return $evaluator;
        };

        $criterionFactory = $this
            ->getMockBuilder(ilTermsOfServiceCriterionTypeFactoryInterface::class)
            ->getMock();
        $dic['tos.criteria.type.factory'] = static function () use (
            $criterionFactory
        ) : ilTermsOfServiceCriterionTypeFactoryInterface {
            return $criterionFactory;
        };

        $requestInterceptor = new ilTermsOfServiceAcceptanceStartUpStep($dic);

        $this->assertTrue($requestInterceptor->shouldInterceptRequest());
        $this->assertTrue($requestInterceptor->shouldStoreRequestTarget());
        $requestInterceptor->execute();
    }

    public function testUserShouldNotBeForcedToAcceptTermsOfServiceWhenDoingItAlreadyInCurrentRequest() : void
    {
        $dic = new Container();

        $ctrl = $this
            ->getMockBuilder(ilCtrl::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCmdClass', 'getCmd'])
            ->getMock();

        $ctrl
            ->expects($this->atLeast(1))
            ->method('getCmdClass')
            ->willReturn('ilstartupgui');

        $ctrl
            ->expects($this->atLeast(1))
            ->method('getCmd')
            ->willReturn('getacceptance');
        $dic['ilCtrl'] = static function () use ($ctrl) : iLCtrl {
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
        $dic['ilUser'] = static function () use ($user) : ilObjUser {
            return $user;
        };

        $request = $this
            ->getMockBuilder(ServerRequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $http = $this
            ->getMockBuilder(Services::class)
            ->disableOriginalConstructor()
            ->getMock();

        $http
            ->method('request')
            ->willReturn($request);
        $dic['http'] = static function () use ($http) : Services {
            return $http;
        };

        $evaluator = $this
            ->getMockBuilder(ilTermsOfServiceDocumentEvaluation::class)
            ->getMock();
        $dic['tos.document.evaluator'] = static function () use ($evaluator) : ilTermsOfServiceDocumentEvaluation {
            return $evaluator;
        };

        $criterionFactory = $this
            ->getMockBuilder(ilTermsOfServiceCriterionTypeFactoryInterface::class)
            ->getMock();
        $dic['tos.criteria.type.factory'] = static function () use (
            $criterionFactory
        ) : ilTermsOfServiceCriterionTypeFactoryInterface {
            return $criterionFactory;
        };

        $requestInterceptor = new ilTermsOfServiceAcceptanceStartUpStep($dic);

        $this->assertFalse($requestInterceptor->shouldInterceptRequest());
    }

    public function userProvider() : array
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
     * @param ilObjUser $user
     */
    public function testUserShouldNotBeForcedToAcceptTermsOfServiceWhenAlreadyDone(ilObjUser $user) : void
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

        $dic = new class($loggingServices) extends Container {
            private LoggingServices $loggingServices;

            public function __construct(LoggingServices $loggingServices)
            {
                $this->loggingServices = $loggingServices;
                parent::__construct();
            }

            public function logger() : LoggingServices
            {
                return $this->loggingServices;
            }
        };

        $ctrl = $this
            ->getMockBuilder(ilCtrl::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCmdClass', 'getCmd'])
            ->getMock();

        $ctrl
            ->method('getCmdClass')
            ->willReturn('ilDashboardGUI');

        $ctrl
            ->method('getCmd')
            ->willReturn('');
        $dic['ilCtrl'] = static function () use ($ctrl) : ilCtrlInterface {
            return $ctrl;
        };

        $request = $this
            ->getMockBuilder(ServerRequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $http = $this
            ->getMockBuilder(Services::class)
            ->disableOriginalConstructor()
            ->getMock();

        $http
            ->method('request')
            ->willReturn($request);
        $dic['http'] = static function () use ($http) : Services {
            return $http;
        };

        $evaluator = $this
            ->getMockBuilder(ilTermsOfServiceDocumentEvaluation::class)
            ->getMock();
        $dic['tos.document.evaluator'] = static function () use ($evaluator) : ilTermsOfServiceDocumentEvaluation {
            return $evaluator;
        };

        $criterionFactory = $this
            ->getMockBuilder(ilTermsOfServiceCriterionTypeFactoryInterface::class)
            ->getMock();
        $dic['tos.criteria.type.factory'] = static function () use (
            $criterionFactory
        ) : ilTermsOfServiceCriterionTypeFactoryInterface {
            return $criterionFactory;
        };

        $service = $this
            ->getMockBuilder(ilTermsOfServiceHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $service
            ->method('hasToResignAcceptance')
            ->willReturn(false);
        $dic['tos.service'] = static function () use ($service) : ilTermsOfServiceHelper {
            return $service;
        };

        $dic['ilUser'] = static function () use ($user) : ilObjUser {
            return $user;
        };

        $requestInterceptor = new ilTermsOfServiceAcceptanceStartUpStep($dic);

        $this->assertFalse($requestInterceptor->shouldInterceptRequest());
    }

    /**
     * @dataProvider userProvider
     */
    public function testUserShouldBeForcedToResignTermsOfService() : void
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

        $dic = new class($loggingServices) extends Container {
            private LoggingServices $loggingServices;

            public function __construct(LoggingServices $loggingServices)
            {
                $this->loggingServices = $loggingServices;
                parent::__construct();
            }

            public function logger() : LoggingServices
            {
                return $this->loggingServices;
            }
        };

        $ctrl = $this
            ->getMockBuilder(ilCtrl::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCmdClass', 'getCmd'])
            ->getMock();

        $ctrl
            ->method('getCmdClass')
            ->willReturn('ilDashboardGUI');

        $ctrl
            ->method('getCmd')
            ->willReturn('');
        $dic['ilCtrl'] = static function () use ($ctrl) : ilCtrlInterface {
            return $ctrl;
        };

        $request = $this
            ->getMockBuilder(ServerRequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $http = $this
            ->getMockBuilder(Services::class)
            ->disableOriginalConstructor()
            ->getMock();

        $http
            ->method('request')
            ->willReturn($request);
        $dic['http'] = static function () use ($http) : Services {
            return $http;
        };

        $evaluator = $this
            ->getMockBuilder(ilTermsOfServiceDocumentEvaluation::class)
            ->getMock();
        $dic['tos.document.evaluator'] = static function () use ($evaluator) : ilTermsOfServiceDocumentEvaluation {
            return $evaluator;
        };

        $criterionFactory = $this
            ->getMockBuilder(ilTermsOfServiceCriterionTypeFactoryInterface::class)
            ->getMock();
        $dic['tos.criteria.type.factory'] = static function () use (
            $criterionFactory
        ) : ilTermsOfServiceCriterionTypeFactoryInterface {
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
        $dic['tos.service'] = static function () use ($service) : ilTermsOfServiceHelper {
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
        $dic['ilUser'] = static function () use ($user) : ilObjUser {
            return $user;
        };

        $requestInterceptor = new ilTermsOfServiceAcceptanceStartUpStep($dic);

        $this->assertTrue($requestInterceptor->shouldInterceptRequest());
    }
}
