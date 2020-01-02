<?php

/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilImportConfig.php");
/**
 * Import configuration for learning modules
 *
 * @author Jesús López <lopez@leifos.com>
 * @version $Id$
 * @ingroup ModulesSurvey
 */
class ilSurveyImportConfig extends ilImportConfig
{
    protected $svy_qpl_id = -1;

    /**
     * Set survey question pool id
     * @param integer $a_svy_qpl_id
     */
    public function setQuestionPoolID($a_svy_qpl_id)
    {
        $this->svy_qpl_id = $a_svy_qpl_id;
    }

    /**
     * Get survey question pool id
     * @return  integer survey pool id
     */
    public function getQuestionPoolID()
    {
        return $this->svy_qpl_id;
    }
}
