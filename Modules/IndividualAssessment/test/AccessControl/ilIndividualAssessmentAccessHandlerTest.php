<?php declare(strict_types=1);

/* Copyright (c) 2021 - Daniel Weise <daniel.weise@concepts-and-training.de> - Extended GPL, see LICENSE */

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

    protected function setUp() : void
    {
        $this->iass_object = $this->createMock(ilObjIndividualAssessment::class);
        $this->access_handler = $this->createMock(ilAccessHandler::class);
        $this->rbac_admin = $this->createMock(ilRbacAdmin::class);
        $this->rbac_review = $this->createMock(ilRbacReview::class);
        $this->obj_user = $this->createMock(ilObjUser::class);
    }

    public function testObjectCreation() : void
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
