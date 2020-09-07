<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @noinspection PhpIncludeInspection */
require_once './Services/WorkflowEngine/interfaces/ilExtractor.php';

/**
 * Class ilBaseExtractor
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 */
abstract class ilBaseExtractor implements ilExtractor
{
    /** @var ilExtractedParams $ilExtractedParams */
    protected $ilExtractedParams;

    /**
     * ilBaseExtractor constructor.
     *
     * @param \ilExtractedParams $ilExtractedParams
     */
    public function __construct(ilExtractedParams $ilExtractedParams)
    {
        $this->ilExtractedParams = $ilExtractedParams;
    }

    /**
     * @param string $event
     * @param array  $parameters
     *
     * @return mixed
     */
    abstract public function extract($event, $parameters);

    /**
     * @param array $parameters
     */
    protected function extractWithUser($parameters)
    {
        $this->ilExtractedParams->setSubjectId($parameters['obj_id']);
        $this->ilExtractedParams->setContextType('usr_id');
        $this->ilExtractedParams->setContextId($parameters['usr_id']);
    }

    /**
     * @param array $parameters
     */
    protected function extractWithoutUser($parameters)
    {
        $this->ilExtractedParams->setSubjectId($parameters['obj_id']);
        $this->ilExtractedParams->setContextType('null');
        $this->ilExtractedParams->setContextId(0);
    }
}
