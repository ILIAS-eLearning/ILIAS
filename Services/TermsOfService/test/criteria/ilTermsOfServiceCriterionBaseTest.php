<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceCriterionBaseTest
 * @author Michael Jansen <mjansen@databay.de>
 */
abstract class ilTermsOfServiceCriterionBaseTest extends \ilTermsOfServiceBaseTest
{
    /**
     * @return PHPUnit_Framework_MockObject_MockObject|\ilRbacReview
     */
    protected function getRbacReviewMock() : \ilRbacReview
    {
        $rbacReview = $this
            ->getMockBuilder(\ilRbacReview::class)
            ->disableOriginalConstructor()
            ->setMethods(['isGlobalRole', 'isAssigned', 'getGlobalRoles'])
            ->getMock();

        $rbacReview
            ->expects($this->any())
            ->method('getGlobalRoles')
            ->willReturn([2, 4]);

        return $rbacReview;
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|\ilObjectDataCache
     */
    protected function getObjectDataCacheMock() : \ilObjectDataCache
    {
        $objectDataCache = $this
            ->getMockBuilder(\ilObjectDataCache::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $objectDataCache;
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|\ilRadioGroupInputGUI
     */
    protected function getRadioGroupMock() : \ilRadioGroupInputGUI
    {
        $radioGroup = $this
            ->getMockBuilder(\ilRadioGroupInputGUI::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPostVar'])
            ->getMock();

        return $radioGroup;
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|\ilPropertyFormGUI
     */
    protected function getFormMock() : \ilPropertyFormGUI
    {
        $form = $this
            ->getMockBuilder(\ilPropertyFormGUI::class)
            ->disableOriginalConstructor()
            ->setMethods(['getInput'])
            ->getMock();

        return $form;
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|\ilObjUser
     */
    protected function getUserMock() : \ilObjUser
    {
        $user = $this
            ->getMockBuilder(\ilObjUser::class)
            ->disableOriginalConstructor()
            ->setMethods(['getLanguage', 'getId', 'getLogin'])
            ->getMock();

        $user
            ->expects($this->any())
            ->method('getId')
            ->willReturn(-1);

        $user
            ->expects($this->any())
            ->method('getLogin')
            ->willReturn('phpunit');

        return $user;
    }
}
