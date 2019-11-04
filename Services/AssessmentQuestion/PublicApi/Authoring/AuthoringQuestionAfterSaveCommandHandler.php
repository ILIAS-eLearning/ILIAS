<?php
declare(strict_types=1);

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Authoring;

/**
 * Interface AuthoringQuestionAfterSaveCommandHandler
 *
 * @package ILIAS\Services\AssessmentQuestion\PublicApi
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
interface AuthoringQuestionAfterSaveCommandHandler
{

    /**
     * @param AuthoringQuestionAfterSaveCommand $command
     */
    public function handle(AuthoringQuestionAfterSaveCommand $command);
}