<?php
declare(strict_types=1);

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Authoring;

use ilAsqQuestionAuthoringGUI;
use ILIAS\AssessmentQuestion\Application\PlayApplicationService;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\AssessmentEntityId;
use ILIAS\UI\Component\Button\Button;
use ILIAS\UI\Component\Link\Link;
use ILIS\AssessmentQuestion\Application\AuthoringApplicationService;

/**
 * Class QuestionAuthoring
 *
 * @package ILIAS\Services\AssessmentQuestion\PublicApi
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class Question
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
     * var string
     */
    protected $question_id;
    /**
     * AuthoringApplicationService
     */
    protected $authoring_application_service;

    /**
     * QuestionAuthoring constructor.
     *
     * @param int    $container_obj_id
     * @param string $question_uuid
     * @param int    $actor_user_id
     * @param Link   $container_backlink
     */
    public function __construct(int $container_obj_id, AssessmentEntityId $question_uuid, int $actor_user_id, Link $container_backlink)
    {
        $this->actor_user_id = $actor_user_id;
        $this->container_obj_id = $container_obj_id;
        $this->question_id = $question_uuid->getId();

        $this->authoring_application_service = new AuthoringApplicationService($container_obj_id, $actor_user_id);
    }


    public function widthAdditionalConfigSection(AdditionalConfigSection $additional_config_section) : Question
    {
        //TODO
    }


    public function getCreationLink(array $ctrl_stack) :Link
    {
        global $DIC;

        array_push($ctrl_stack,ilAsqQuestionAuthoringGUI::class);

        return $DIC->ui()->factory()->link()->standard('create by asq',$DIC->ctrl()->getLinkTargetByClass($ctrl_stack,ilAsqQuestionAuthoringGUI::CMD_CREATE_QUESTION));
    }


    public function getAuthoringGUI() : ilAsqQuestionAuthoringGUI
    {
        return new ilAsqQuestionAuthoringGUI($this->container_obj_id, $this->actor_user_id);
    }


    /**
     */
    public function deleteQuestion() : void
    {
        // TODO: Implement deleteQuestion() method.
    }


    /**
     * @return Link
     */
    public function getEditLink(array $ctrl_stack) :Link
    {
        global $DIC;
        array_push($ctrl_stack,ilAsqQuestionAuthoringGUI::class);

        $DIC->ctrl()->setParameterByClass(ilAsqQuestionAuthoringGUI::class,ilAsqQuestionAuthoringGUI::VAR_QUESTION_ID,$this->question_id);

        return $DIC->ui()->factory()->link()->standard('edit by asq',$DIC->ctrl()->getLinkTargetByClass($ctrl_stack,ilAsqQuestionAuthoringGUI::CMD_EDIT_QUESTION));
    }


    /**
     * @return Link
     */
    //TODO this will not be the way! Do not save questions,
    // only simulate and show the points directly after submitting
    // Therefore, to Save Command has to
    public function getPreviewLink(array $ctrl_stack) : Link
    {
        global $DIC;
        array_push($ctrl_stack,ilAsqQuestionAuthoringGUI::class);

        $DIC->ctrl()->setParameterByClass(ilAsqQuestionAuthoringGUI::class,ilAsqQuestionAuthoringGUI::VAR_QUESTION_ID,$this->question_id);

        return $DIC->ui()->factory()->link()->standard('preview by asq',$DIC->ctrl()->getLinkTargetByClass($ctrl_stack,ilAsqQuestionAuthoringGUI::CMD_PREVIEW_QUESTION));
    }

    //TODO this will not be the way - see above
    public function getScoringOfPreviewedQuestion():float {
        global $DIC;
        $DIC->ctrl()->setParameterByClass(ilAsqQuestionAuthoringGUI::class,ilAsqQuestionAuthoringGUI::VAR_QUESTION_ID,$this->question_id);

        $player = new PlayApplicationService($this->container_obj_id,$this->actor_user_id);
        return $player->GetPointsByUser($this->question_id,$this->actor_user_id, ilAsqQuestionAuthoringGUI::DEBUG_TEST_ID);

    }

    /**
     * @return Link
     */
    public function getDisplayLink(array $ctrl_stack) : Link
    {
        global $DIC;
        array_push($ctrl_stack,ilAsqQuestionAuthoringGUI::class);
        
        $DIC->ctrl()->setParameterByClass(ilAsqQuestionAuthoringGUI::class,ilAsqQuestionAuthoringGUI::VAR_QUESTION_ID,$this->question_id);
        
        return $DIC->ui()->factory()->link()->standard('play by asq',$DIC->ctrl()->getLinkTargetByClass($ctrl_stack,ilAsqQuestionAuthoringGUI::CMD_DISPLAY_QUESTION));
    }

    /**
     * @return Link
     */
    public function getEditPageLink() : Link
    {
        // TODO: Implement getEdiPageLink() method.
    }


    /**
     * @return Link
     */
    public function getEditFeedbacksLink() : Link
    {
        // TODO: Implement getEditFeedbacksLink() method.
    }


    /**
     * @return Link
     */
    public function getEditHintsLink() : Link
    {
        // TODO: Implement getEditHintsLink() method.
    }


    /**
     * @return Link
     */
    public function getStatisticLink() : Link
    {
        // TODO: Implement getStatisticLink() method.
    }


    /**
     *
     */
    public function publishNewRevision() : void
    {
        $this->authoring_application_service->projectQuestion($this->question_id);
    }


    public function changeQuestionContainer(int $container_obj_id) : void
    {
        // TODO: Implement changeQuestionContainer() method.
    }
}