<?php declare(strict_types=1);

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\DI\Container;
use ILIAS\DI\LoggingServices;
use ILIAS\HTTP\GlobalHttpState;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ilTermsOfServiceRequestTargetAdjustmentCaseTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceRequestTargetAdjustmentCaseTest extends ilTermsOfServiceBaseTest
{
    /**
     * @throws ReflectionException
     */
    public function testUserShouldBeForcedToAcceptTermsOfServiceWhenNotDoingItYetInCurrentRequest() : void
    {
        $dic = new Container();

        $ctrl = $this
            ->getMockBuilder(ilCtrl::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['redirectToURL', 'getCmdClass', 'getCmd'])
            ->getMock();

        $ctrl
            ->expects($this->any())
            ->method('getCmdClass')
            ->willReturn('ilDashboardGUI');

        $ctrl
            ->expects($this->any())
            ->method('getCmd')
            ->willReturn('');

        $ctrl
            ->expects($this->once())
            ->method('redirectToURL');
        $dic['ilCtrl'] = function () use ($ctrl) {
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
            ->expects($this->any())
            ->method('checkTimeLimit')
            ->willReturn(true);

        $user
            ->expects($this->atLeast(1))
            ->method('hasToAcceptTermsOfServiceInSession')
            ->willReturn(true);
        $dic['ilUser'] = function () use ($user) {
            return $user;
        };

        $request = $this
            ->getMockBuilder(ServerRequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $http = $this
            ->getMockBuilder(GlobalHttpState::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $http->expects($this->any())
            ->method('request')
            ->willReturn($request);
        $dic['http'] = function () use ($http) {
            return $http;
        };

        $evaluator = $this
            ->getMockBuilder(ilTermsOfServiceDocumentEvaluation::class)
            ->getMock();
        $dic['tos.document.evaluator'] = function () use ($evaluator) {
            return $evaluator;
        };

        $criterionFactory = $this
            ->getMockBuilder(ilTermsOfServiceCriterionTypeFactoryInterface::class)
            ->getMock();
        $dic['tos.criteria.type.factory'] = function () use ($criterionFactory) {
            return $criterionFactory;
        };

        $requestInterceptor = new ilTermsOfServiceRequestTargetAdjustmentCase($dic);

        $this->assertTrue($requestInterceptor->shouldAdjustRequest());
        $this->assertTrue($requestInterceptor->shouldStoreRequestTarget());
        $requestInterceptor->adjust();
    }

    /**
     * @throws ReflectionException
     */
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
        $dic['ilCtrl'] = function () use ($ctrl) {
            return $ctrl;
        };

        $user = $this
            ->getMockBuilder(ilObjUser::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['hasToAcceptTermsOfService', 'checkTimeLimit', 'hasToAcceptTermsOfServiceInSession'])
            ->getMock();

        $user
            ->expects($this->any())
            ->method('hasToAcceptTermsOfService')
            ->willReturn(true);

        $user
            ->expects($this->any())
            ->method('checkTimeLimit')
            ->willReturn(true);

        $user
            ->expects($this->any())
            ->method('hasToAcceptTermsOfServiceInSession')
            ->willReturn(true);
        $dic['ilUser'] = function () use ($user) {
            return $user;
        };

        $request = $this
            ->getMockBuilder(ServerRequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $http = $this
            ->getMockBuilder(GlobalHttpState::class)
            ->disableOriginalConstructor()
            ->getMock();

        $http->expects($this->any())
            ->method('request')
            ->willReturn($request);
        $dic['http'] = function () use ($http) {
            return $http;
        };

        $evaluator = $this
            ->getMockBuilder(ilTermsOfServiceDocumentEvaluation::class)
            ->getMock();
        $dic['tos.document.evaluator'] = function () use ($evaluator) {
            return $evaluator;
        };

        $criterionFactory = $this
            ->getMockBuilder(ilTermsOfServiceCriterionTypeFactoryInterface::class)
            ->getMock();
        $dic['tos.criteria.type.factory'] = function () use ($criterionFactory) {
            return $criterionFactory;
        };

        $requestInterceptor = new ilTermsOfServiceRequestTargetAdjustmentCase($dic);

        $this->assertFalse($requestInterceptor->shouldAdjustRequest());
    }

    /**
     * @return array
     * @throws ReflectionException
     */
    public function userProvider() : array
    {
        $user1 = $this
            ->getMockBuilder(ilObjUser::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['hasToAcceptTermsOfService', 'checkTimeLimit', 'hasToAcceptTermsOfServiceInSession'])
            ->getMock();

        $user1
            ->expects($this->any())
            ->method('hasToAcceptTermsOfService')
            ->willReturn(false);

        $user1
            ->expects($this->any())
            ->method('checkTimeLimit')
            ->willReturn(true);

        $user1
            ->expects($this->any())
            ->method('hasToAcceptTermsOfServiceInSession')
            ->willReturn(true);

        $user2 = $this
            ->getMockBuilder(ilObjUser::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['hasToAcceptTermsOfService', 'checkTimeLimit', 'hasToAcceptTermsOfServiceInSession'])
            ->getMock();

        $user2
            ->expects($this->any())
            ->method('hasToAcceptTermsOfService')
            ->willReturn(true);

        $user2
            ->expects($this->any())
            ->method('checkTimeLimit')
            ->willReturn(false);

        $user2
            ->expects($this->any())
            ->method('hasToAcceptTermsOfServiceInSession')
            ->willReturn(true);

        $user3 = $this
            ->getMockBuilder(ilObjUser::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['hasToAcceptTermsOfService', 'checkTimeLimit', 'hasToAcceptTermsOfServiceInSession'])
            ->getMock();

        $user3
            ->expects($this->any())
            ->method('hasToAcceptTermsOfService')
            ->willReturn(true);

        $user3
            ->expects($this->any())
            ->method('checkTimeLimit')
            ->willReturn(true);

        $user3
            ->expects($this->any())
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
     * @throws ReflectionException
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
            ->expects($this->any())
            ->method('root')
            ->willReturn($logger);
        $loggingServices->expects($this->any())
            ->method('__call')
            ->willReturn($logger);

        $dic = new class($loggingServices) extends Container {
            /** @var LoggingServices */
            private $loggingServices;

            /**
             *  constructor.
             * @param LoggingServices $loggingServices
             */
            public function __construct(LoggingServices $loggingServices)
            {
                $this->loggingServices = $loggingServices;
                parent::__construct();
            }

            /**
             * @inheritDoc
             */
            public function logger()
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
            ->expects($this->any())
            ->method('getCmdClass')
            ->willReturn('ilDashboardGUI');

        $ctrl
            ->expects($this->any())
            ->method('getCmd')
            ->willReturn('');
        $dic['ilCtrl'] = function () use ($ctrl) {
            return $ctrl;
        };

        $request = $this
            ->getMockBuilder(ServerRequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $http = $this
            ->getMockBuilder(GlobalHttpState::class)
            ->disableOriginalConstructor()
            ->getMock();

        $http->expects($this->any())
            ->method('request')
            ->willReturn($request);
        $dic['http'] = function () use ($http) {
            return $http;
        };

        $evaluator = $this
            ->getMockBuilder(ilTermsOfServiceDocumentEvaluation::class)
            ->getMock();
        $dic['tos.document.evaluator'] = function () use ($evaluator) {
            return $evaluator;
        };

        $criterionFactory = $this
            ->getMockBuilder(ilTermsOfServiceCriterionTypeFactoryInterface::class)
            ->getMock();
        $dic['tos.criteria.type.factory'] = function () use ($criterionFactory) {
            return $criterionFactory;
        };

        $service = $this
            ->getMockBuilder(ilTermsOfServiceHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $service->expects($this->any())
            ->method('hasToResignAcceptance')
            ->willReturn(false);
        $dic['tos.service'] = function () use ($service) {
            return $service;
        };

        $dic['ilUser'] = function () use ($user) {
            return $user;
        };

        $requestInterceptor = new ilTermsOfServiceRequestTargetAdjustmentCase($dic);

        $this->assertFalse($requestInterceptor->shouldAdjustRequest());
    }

    /**
     * @dataProvider userProvider
     * @throws ReflectionException
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
            ->expects($this->any())
            ->method('root')
            ->willReturn($logger);
        $loggingServices->expects($this->any())
            ->method('__call')
            ->willReturn($logger);

        $dic = new class($loggingServices) extends Container {
            /** @var LoggingServices */
            private $loggingServices;

            /**
             *  constructor.
             * @param LoggingServices $loggingServices
             */
            public function __construct(LoggingServices $loggingServices)
            {
                $this->loggingServices = $loggingServices;
                parent::__construct();
            }

            /**
             * @inheritDoc
             */
            public function logger()
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
            ->expects($this->any())
            ->method('getCmdClass')
            ->willReturn('ilDashboardGUI');

        $ctrl
            ->expects($this->any())
            ->method('getCmd')
            ->willReturn('');
        $dic['ilCtrl'] = function () use ($ctrl) {
            return $ctrl;
        };

        $request = $this
            ->getMockBuilder(ServerRequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $http = $this
            ->getMockBuilder(GlobalHttpState::class)
            ->disableOriginalConstructor()
            ->getMock();

        $http->expects($this->any())
            ->method('request')
            ->willReturn($request);
        $dic['http'] = function () use ($http) {
            return $http;
        };

        $evaluator = $this
            ->getMockBuilder(ilTermsOfServiceDocumentEvaluation::class)
            ->getMock();
        $dic['tos.document.evaluator'] = function () use ($evaluator) {
            return $evaluator;
        };

        $criterionFactory = $this
            ->getMockBuilder(ilTermsOfServiceCriterionTypeFactoryInterface::class)
            ->getMock();
        $dic['tos.criteria.type.factory'] = function () use ($criterionFactory) {
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
        $dic['tos.service'] = function () use ($service) {
            return $service;
        };

        $user = $this
            ->getMockBuilder(ilObjUser::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['hasToAcceptTermsOfService', 'checkTimeLimit', 'hasToAcceptTermsOfServiceInSession'])
            ->getMock();
        $user
            ->expects($this->any())
            ->method('hasToAcceptTermsOfService')
            ->willReturn(false);
        $user
            ->expects($this->any())
            ->method('checkTimeLimit')
            ->willReturn(true);
        $user
            ->expects($this->any())
            ->method('hasToAcceptTermsOfServiceInSession')
            ->willReturn(true);
        $dic['ilUser'] = function () use ($user) {
            return $user;
        };

        $requestInterceptor = new ilTermsOfServiceRequestTargetAdjustmentCase($dic);

        $this->assertTrue($requestInterceptor->shouldAdjustRequest());
    }
}
