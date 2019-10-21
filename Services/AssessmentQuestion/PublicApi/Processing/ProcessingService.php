<?php
declare(strict_types=1);

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Processing;

use ilAsqQuestionProcessingGUI;
use ILIAS\AssessmentQuestion\Application\ProcessingApplicationService;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\AssessmentEntityId;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\QuestionConfig;

/**
 * Class ServiceFactory
 *
 * @package ILIAS\Services\AssessmentQuestion\PublicApi\Authoring
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ProcessingService
{
    /**
     * @var int
     */
    protected $container_obj_id;
    /**
     * @var int
     */
    protected $actor_user_id;
    /**
     * @var QuestionConfig
     */
    protected $question_config;
    /**
     * @var string
     */
    protected $lng_key;

    /**
     * ProcessingService constructor.
     *
     * @param int            $container_obj_id
     * @param int            $actor_user_id
     * @param QuestionConfig $question_config
     */
    public function __construct(int $container_obj_id, int $actor_user_id)
    {
        global $DIC;

        $this->container_obj_id = $container_obj_id;
        $this->actor_user_id = $actor_user_id;

        //The lng_key could be used in future as parameter in the constructor
        $this->lng_key = $DIC->language()->getDefaultLanguage();
    }


    /**
     * @param string         $question_revision_id
     *
     * @return ProcessingQuestion
     */
    public function question(string $question_revision_id) : ProcessingQuestion
    {
        return new ProcessingQuestion($question_revision_id, $this->container_obj_id, $this->actor_user_id, $this->lng_key);
    }

    /**
     * @return ProcessingQuestionList
     */
    public function questionList() : ProcessingQuestionList
    {
        return new ProcessingQuestionList($this->container_obj_id, $this->actor_user_id, $this->lng_key);
    }
}
