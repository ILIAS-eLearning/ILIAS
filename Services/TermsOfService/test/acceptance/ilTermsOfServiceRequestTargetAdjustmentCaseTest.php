<?php declare(strict_types=1);

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

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
        $ctrl = $this
            ->getMockBuilder(ilCtrl::class)
            ->disableOriginalConstructor()
            ->setMethods(['redirectToURL', 'getCmdClass', 'getCmd'])
            ->getMock();

        $ctrl
            ->expects($this->any())
            ->method('getCmdClass')
            ->willReturn('ilPersonalDesktopGUI');

        $ctrl
            ->expects($this->any())
            ->method('getCmd')
            ->willReturn('');

        $ctrl
            ->expects($this->once())
            ->method('redirectToURL');

        $user = $this
            ->getMockBuilder(ilObjUser::class)
            ->disableOriginalConstructor()
            ->setMethods(['hasToAcceptTermsOfService', 'checkTimeLimit', 'hasToAcceptTermsOfServiceInSession'])
            ->getMock();

        $user
            ->expects($this->atLeast(1))
            ->method('hasToAcceptTermsOfService')
            ->willReturn(true);

        $user
            ->expects($this->atLeast(1))
            ->method('checkTimeLimit')
            ->willReturn(true);

        $user
            ->expects($this->atLeast(1))
            ->method('hasToAcceptTermsOfServiceInSession')
            ->willReturn(true);

        $request = $this
            ->getMockBuilder(ServerRequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $requestInterceptor = new ilTermsOfServiceRequestTargetAdjustmentCase($user, $ctrl, $request);

        $this->assertTrue($requestInterceptor->shouldAdjustRequest());
        $this->assertTrue($requestInterceptor->shouldStoreRequestTarget());
        $requestInterceptor->adjust();
    }

    /**
     * @throws ReflectionException
     */
    public function testUserShouldNotBeForcedToAcceptTermsOfServiceWhenDoingItAlreadyInCurrentRequest() : void
    {
        $ctrl = $this
            ->getMockBuilder(ilCtrl::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCmdClass', 'getCmd'])
            ->getMock();

        $ctrl
            ->expects($this->atLeast(1))
            ->method('getCmdClass')
            ->willReturn('ilstartupgui');

        $ctrl
            ->expects($this->atLeast(1))
            ->method('getCmd')
            ->willReturn('getacceptance');

        $user = $this
            ->getMockBuilder(ilObjUser::class)
            ->disableOriginalConstructor()
            ->setMethods(['hasToAcceptTermsOfService', 'checkTimeLimit', 'hasToAcceptTermsOfServiceInSession'])
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

        $request = $this
            ->getMockBuilder(ServerRequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $requestInterceptor = new ilTermsOfServiceRequestTargetAdjustmentCase($user, $ctrl, $request);

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
            ->setMethods(['hasToAcceptTermsOfService', 'checkTimeLimit', 'hasToAcceptTermsOfServiceInSession'])
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
            ->setMethods(['hasToAcceptTermsOfService', 'checkTimeLimit', 'hasToAcceptTermsOfServiceInSession'])
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
            ->setMethods(['hasToAcceptTermsOfService', 'checkTimeLimit', 'hasToAcceptTermsOfServiceInSession'])
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
            [$user1],
            [$user2],
            [$user3],
        ];
    }

    /**
     * @dataProvider  userProvider
     * @param ilObjUser $user
     * @throws ReflectionException
     */
    public function testUserShouldNotBeForcedToAcceptTermsOfServiceWhenAlreadyDone(ilObjUser $user) : void
    {
        $ctrl = $this
            ->getMockBuilder(ilCtrl::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCmdClass', 'getCmd'])
            ->getMock();

        $ctrl
            ->expects($this->any())
            ->method('getCmdClass')
            ->willReturn('ilPersonalDesktopGUI');

        $ctrl
            ->expects($this->any())
            ->method('getCmd')
            ->willReturn('');

        $request = $this
            ->getMockBuilder(ServerRequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $requestInterceptor = new ilTermsOfServiceRequestTargetAdjustmentCase($user, $ctrl, $request);

        $this->assertFalse($requestInterceptor->shouldAdjustRequest());
    }
}