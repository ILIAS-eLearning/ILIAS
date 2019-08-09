<?php
declare(strict_types=1);

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Authoring;

use ilAsqQuestionAuthoringGUI;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\AssessmentEntityId;
use ILIAS\UI\Component\Button\Button;
use ILIAS\UI\Component\Link\Link;

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
     * QuestionAuthoring constructor.
     *
     * @param int    $container_obj_id
     * @param string $question_uuid
     * @param int    $actor_user_id
     * @param Link   $container_backlink
     */
    public function __construct(int $container_obj_id, AssessmentEntityId $question_uuid, int $actor_user_id, Link $container_backlink)
    {
        // TODO
    }


    public function widthAdditionalConfigSection(AdditionalConfigSection $additional_config_section) : Question
    {
        //TODO
    }


    public function getCreationLink(array $ctrl_stack) :Link
    {
        global $DIC;

        array_push($ctrl_stack,ilAsqQuestionAuthoringGUI::class);

        //TODO Lang
        return $DIC->ui()->factory()->link()->standard('create by asq',$DIC->ctrl()->getLinkTargetByClass($ctrl_stack,ilAsqQuestionAuthoringGUI::CMD_CREATE_QUESTION));
    }


    public function getAuthoringGUI() : ilAsqQuestionAuthoringGUI
    {
        return new ilAsqQuestionAuthoringGUI();
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
    public function getEditLink() : Link
    {
        // TODO: Implement GetEditConfigLink() method.
    }


    /**
     * @return Link
     */
    public function getPreviewLink() : Link
    {
        // TODO: Implement getPreviewLink() method.
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
        // TODO: Implement publishNewRevision() method.
    }


    public function changeQuestionContainer(int $container_obj_id) : void
    {
        // TODO: Implement changeQuestionContainer() method.
    }
}