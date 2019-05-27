<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class ilTermsOfServiceCriterionBaseTest
 * @author Michael Jansen <mjansen@databay.de>
 */
abstract class ilTermsOfServiceCriterionBaseTest extends ilTermsOfServiceBaseTest
{
    /**
     * @return MockObject|ilRbacReview
     * @throws ReflectionException
     */
    protected function getRbacReviewMock() : ilRbacReview
    {
        $rbacReview = $this
            ->getMockBuilder(ilRbacReview::class)
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
     * @return MockObject|ilObjectDataCache
     * @throws ReflectionException
     */
    protected function getObjectDataCacheMock() : ilObjectDataCache
    {
        $objectDataCache = $this
            ->getMockBuilder(ilObjectDataCache::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $objectDataCache;
    }

    /**
     * @return MockObject|ilRadioGroupInputGUI
     * @throws ReflectionException
     */
    protected function getRadioGroupMock() : ilRadioGroupInputGUI
    {
        $radioGroup = $this
            ->getMockBuilder(ilRadioGroupInputGUI::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPostVar'])
            ->getMock();

        return $radioGroup;
    }

    /**
     * @return MockObject|ilPropertyFormGUI
     * @throws ReflectionException
     */
    protected function getFormMock() : ilPropertyFormGUI
    {
        $form = $this
            ->getMockBuilder(ilPropertyFormGUI::class)
            ->disableOriginalConstructor()
            ->setMethods(['getInput'])
            ->getMock();

        return $form;
    }

    /**
     * @return MockObject|ilObjUser
     * @throws ReflectionException
     */
    protected function getUserMock() : ilObjUser
    {
        $user = $this
            ->getMockBuilder(ilObjUser::class)
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