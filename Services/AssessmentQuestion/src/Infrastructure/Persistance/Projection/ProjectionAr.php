<?php
/* Copyright (c) 2019 Extended GPL, see docs/LICENSE */

namespace ILIAS\AssessmentQuestion\Infrastructure\Persistence\Projection;

use ilException;

interface ProjectionAr
{

    /**
     * @return int
     */
    public function getId() : int;


    /**
     * @param int $id
     */
    public function setId(int $id) : void;


    /**
     * @return mixed
     */
    public function getCreated();


    /**
     * @param mixed $created
     */
    public function setCreated($created) : void;


    /**
     * @return string
     */
    public function getQuestionId() : string;


    /**
     * @param string $question_id
     */
    public function setQuestionId(string $question_id) : void;


    /**
     * @return string
     */
    public function getRevisionId() : string;


    /**
     * @param string $revision_id
     */
    public function setRevisionId(string $revision_id) : void;


    /**
     * @return int
     */
    public function getContainerObjId() : int;


    /**
     * @param int $container_obj_id
     */
    public function setContainerObjId(int $container_obj_id) : void;


    /**
     * @return int
     */
    public function getIsCurrentContainerRevision() : int;


    /**
     * @param int $is_current_container_revision
     */
    public function setIsCurrentContainerRevision(int $is_current_container_revision) : void;


    /**
     * @param int $is_current_container_revision
     */
    public function updateIsCurrentContainerRevisionToNo() : void;


    /**
     *
     */
    public function create();
}