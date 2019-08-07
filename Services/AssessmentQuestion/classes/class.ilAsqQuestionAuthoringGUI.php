<?php
declare(strict_types=1);

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Services\AssessmentQuestion\PublicApi\Common\AssessmentEntityId;

/**
 * Class ilAssessmentQuestionExporter
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ilAsqQuestionAuthoringGUI
{

    /**
     * @var AssessmentEntityId
     */
    protected $question_uuid;


    /**
     * ilAsqQuestionAuthoringGUI constructor.
     *
     * @param AssessmentEntityId $question_uuid
     */
    public function __construct(AssessmentEntityId $question_uuid)
    {
        //TODO
    }


    public function executeCommand()
    {

    }
}