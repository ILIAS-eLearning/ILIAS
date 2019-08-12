<?php
declare(strict_types=1);

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Processing;

use JsonSerializable;

/**
 * Class UserAnswerSubmit
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 *
 * @package ILIAS\Services\AssessmentQuestion\PublicApi
 */
class UserAnswerSubmit
{

    /**
     * @var JsonSerializable
     */
    protected $user_answer;


    /**
     * UserAnswerDTO constructor.
     *
     * @param JsonSerializable $user_answer
     */
    public function __construct(JsonSerializable $user_answer)
    {
        $this->user_answer = $user_answer;
    }


    /**
     * @return JsonSerializable
     */
    public function getUserAnswer() : JsonSerializable
    {
        return $this->user_answer;
    }
}
