<?php
/* Copyright (c) 2019 Extended GPL, see docs/LICENSE */

namespace ILIAS\AssessmentQuestion\Infrastructure\Persistence\Projection;

interface ProjectionAr
{
    /**
     * @return int
     */
    public function getId() : int;

    /**
     * @return mixed
     */
    public function getCreated();

    /**
     * @return string
     */
    public function getQuestionId() : string;

    /**
     * @return int
     */
    public function getquestionIntId(): int;

    /**
     * @return string
     */
    public function getRevisionId() : string;

    /**
     * @return int
     */
    public function getContainerObjId() : int;

    /**
     *
     */
    public function create();
}