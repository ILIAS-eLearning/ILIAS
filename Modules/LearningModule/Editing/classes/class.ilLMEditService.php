<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Main service init and factory
 *
 * @author @leifos.de
 * @ingroup
 */
class ilLMEditService
{
    /**
     * @var ilObjLearningModule
     */
    protected $lm;

    /**
     * @var ilLMEditRequest
     */
    protected $request;

    /**
     * Constructor
     */
    public function __construct(
        array $query_params
    )
    {
        $this->request = new ilLMEditRequest($query_params);
        $this->ref_id = $this->request->getRequestedRefId();
        $this->lm = new ilObjLearningModule($this->ref_id);
    }

    /**
     * @return ilObjLearningModule
     */
    public function getLearningModule(): ilObjLearningModule
    {
        return $this->lm;
    }

    /**
     * Get request
     *
     * @return ilLMEditRequest
     */
    public function getRequest(): ilLMEditRequest
    {
        return $this->request;
    }

}