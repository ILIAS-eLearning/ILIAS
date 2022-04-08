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
    private $rbacadmin;
    /**
     * @var ilRbacReview|mixed|MockObject
     */
    private $rbacreview;
    /**
     * @var ilObjUser|mixed|MockObject
     */
    private $obj_user;

    protected function setUp() : void
    {
        $this->iass_object = $this->createMock(ilObjIndividualAssessment::class);
        $this->access_handler = $this->createMock(ilAccessHandler::class);
        $this->rbacadmin = $this->createMock(ilRbacAdmin::class);
        $this->rbacreview = $this->createMock(ilRbacReview::class);
        $this->obj_user = $this->createMock(ilObjUser::class);
    }

    public function testObjectCreation() : void
    {
        $obj = new ilIndividualAssessmentAccessHandler(
            $this->iass_object,
            $this->access_handler,
            $this->rbacadmin,
            $this->rbacreview,
            $this->obj_user
        );

        $this->assertInstanceOf(ilIndividualAssessmentAccessHandler::class, $obj);
    }
}
