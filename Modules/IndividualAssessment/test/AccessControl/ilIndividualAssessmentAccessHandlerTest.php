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

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class ilIndividualAssessmentAccessHandlerTest extends TestCase
{
    /**
     * @var ilObjIndividualAssessment|mixed|MockObject
     */
    private $iass_object;
    /**
     * @var ilAccessHandler|mixed|MockObject
     */
    private $access_handler;
    /**
     * @var ilRbacAdmin|mixed|MockObject
     */
    private $rbac_admin;
    /**
     * @var ilRbacReview|mixed|MockObject
     */
    private $rbac_review;
    /**
     * @var ilObjUser|mixed|MockObject
     */
    private $obj_user;

    protected function setUp(): void
    {
        $this->iass_object = $this->createMock(ilObjIndividualAssessment::class);
        $this->access_handler = $this->createMock(ilAccessHandler::class);
        $this->rbac_admin = $this->createMock(ilRbacAdmin::class);
        $this->rbac_review = $this->createMock(ilRbacReview::class);
        $this->obj_user = $this->createMock(ilObjUser::class);
    }

    public function testObjectCreation(): void
    {
        $obj = new ilIndividualAssessmentAccessHandler(
            $this->iass_object,
            $this->access_handler,
            $this->rbac_admin,
            $this->rbac_review,
            $this->obj_user
        );

        $this->assertInstanceOf(ilIndividualAssessmentAccessHandler::class, $obj);
    }
}
