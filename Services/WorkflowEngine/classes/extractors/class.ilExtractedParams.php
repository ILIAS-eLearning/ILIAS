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
    protected string $subject_type;
    
    protected int $subject_id = 0;
    
    protected string $context_type = '';
    
    protected int $context_id = 0;

    /**
     * @return string
     */
    public function getSubjectType() : string
    {
        return $this->subject_type;
    }

    /**
     * @param string $subject_type
     */
    public function setSubjectType(string $subject_type) : void
    {
        $this->subject_type = $subject_type;
    }

    /**
     * @return int
     */
    public function getSubjectId() : int
    {
        return $this->subject_id;
    }

    /**
     * @param int $subject_id
     */
    public function setSubjectId(int $subject_id) : void
    {
        $this->subject_id = $subject_id;
    }

    /**
     * @return string
     */
    public function getContextType() : string
    {
        return $this->context_type;
    }

    /**
     * @param string $context_type
     */
    public function setContextType(string $context_type) : void
    {
        $this->context_type = $context_type;
    }

    /**
     * @return int
     */
    public function getContextId() : int
    {
        return $this->context_id;
    }

    /**
     * @param int $context_id
     */
    public function setContextId(int $context_id) : void
    {
        $this->context_id = $context_id;
    }
}
