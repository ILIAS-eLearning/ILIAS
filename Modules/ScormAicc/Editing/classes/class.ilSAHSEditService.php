<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Main service init and factory
 *
 * @author killing@leifos.de
 */
class ilSAHSEditService
{

    /**
     * @var ilSAHSEditRequest
     */
    protected $request;

    /**
     * @var ilObjSCORM2004LearningModule
     */
    protected $lm;

    /**
     * Constructor
     */
    public function __construct(
        array $query_params
    )
    {
        $this->request = new ilSAHSEditRequest($query_params);
        $this->ref_id = $this->request->getRequestedRefId();
        $this->lm = new ilObjSCORM2004LearningModule($this->ref_id);
    }

    /**
     * Get request
     *
     * @return ilSAHSEditRequest
     */
    public function getRequest(): ilSAHSEditRequest
    {
        return $this->request;
    }

    /**
     * Get request
     *
     * @return ilObjSCORM2004LearningModule
     */
    public function getLearningModule(): ilObjSCORM2004LearningModule
    {
        return $this->lm;
    }



}