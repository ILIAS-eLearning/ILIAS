<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilExtractedParams
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 */
class ilExtractedParams
{
    /** @var string $subject_type */
    protected $subject_type;

    /** @var integer $subject_id */
    protected $subject_id;

    /** @var string $context_type */
    protected $context_type;

    /** @var integer $context_id */
    protected $context_id;

    /**
     * @return string
     */
    public function getSubjectType()
    {
        return $this->subject_type;
    }

    /**
     * @param string $subject_type
     */
    public function setSubjectType($subject_type)
    {
        $this->subject_type = $subject_type;
    }

    /**
     * @return int
     */
    public function getSubjectId()
    {
        return $this->subject_id;
    }

    /**
     * @param int $subject_id
     */
    public function setSubjectId($subject_id)
    {
        $this->subject_id = $subject_id;
    }

    /**
     * @return string
     */
    public function getContextType()
    {
        return $this->context_type;
    }

    /**
     * @param string $context_type
     */
    public function setContextType($context_type)
    {
        $this->context_type = $context_type;
    }

    /**
     * @return int
     */
    public function getContextId()
    {
        return $this->context_id;
    }

    /**
     * @param int $context_id
     */
    public function setContextId($context_id)
    {
        $this->context_id = $context_id;
    }
}
