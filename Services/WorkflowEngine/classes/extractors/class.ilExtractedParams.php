<?php

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

/**
 * Class ilExtractedParams
 *
 * @author Maximilian Becker <mbecker@databay.de>
 */
class ilExtractedParams
{
    protected string $subject_type;
    
    protected int $subject_id = 0;
    
    protected string $context_type = '';
    
    protected int $context_id = 0;

    public function getSubjectType() : string
    {
        return $this->subject_type;
    }

    public function setSubjectType(string $subject_type) : void
    {
        $this->subject_type = $subject_type;
    }

    public function getSubjectId() : int
    {
        return $this->subject_id;
    }

    public function setSubjectId(int $subject_id) : void
    {
        $this->subject_id = $subject_id;
    }

    public function getContextType() : string
    {
        return $this->context_type;
    }

    public function setContextType(string $context_type) : void
    {
        $this->context_type = $context_type;
    }

    public function getContextId() : int
    {
        return $this->context_id;
    }

    public function setContextId($context_id) : void
    {
        $this->context_id = (int) $context_id;
    }
}
