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

use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class ilTermsOfServiceCriterionBaseTest
 * @author Michael Jansen <mjansen@databay.de>
 */
abstract class ilTermsOfServiceCriterionBaseTest extends ilTermsOfServiceBaseTest
{
    /**
     * @return MockObject&ilRbacReview
     */
    protected function getRbacReviewMock() : ilRbacReview
    {
        $rbacReview = $this
            ->getMockBuilder(ilRbacReview::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isGlobalRole', 'isAssigned', 'getGlobalRoles'])
            ->getMock();

        $rbacReview
            ->method('getGlobalRoles')
            ->willReturn([2, 4]);

        return $rbacReview;
    }

    /**
     * @return MockObject&ilObjectDataCache
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
     * @return MockObject&ilRadioGroupInputGUI
     */
    protected function getRadioGroupMock() : ilRadioGroupInputGUI
    {
        $radioGroup = $this
            ->getMockBuilder(ilRadioGroupInputGUI::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPostVar'])
            ->getMock();

        return $radioGroup;
    }

    /**
     * @return MockObject&ilPropertyFormGUI
     */
    protected function getFormMock() : ilPropertyFormGUI
    {
        $form = $this
            ->getMockBuilder(ilPropertyFormGUI::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getInput'])
            ->getMock();

        return $form;
    }

    /**
     * @return MockObject&ilObjUser
     */
    protected function getUserMock() : ilObjUser
    {
        $user = $this
            ->getMockBuilder(ilObjUser::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getLanguage', 'getId', 'getLogin', 'getSelectedCountry'])
            ->getMock();

        $user
            ->method('getId')
            ->willReturn(-1);

        $user
            ->method('getLogin')
            ->willReturn('phpunit');

        return $user;
    }
}
