<?php

use ILIAS\Services\AssessmentQuestion\PublicApi\Authoring\AuthoringService;
use ILIAS\Services\AssessmentQuestion\PublicApi\Processing\ProcessingService;
use ILIAS\UI\Component\Link\Link;

/**
 * Class asqDebugGUI
 *
 * @author studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author Adrian Lüthi <al@studer-raimann.ch>
 * @author Björn Heyser <bh@bjoernheyser.de>
 * @author Martin Studer <ms@studer-raimann.ch>
 * @author Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_Calls asqDebugGUI: ilAsqQuestionAuthoringGUI
 * @ilCtrl_IsCalledBy asqDebugGUI: ilObjTestGUI
 */
class asqDebugGUI {

    const CMD_SHOW_EDIT_LIST = "showEditList";
    const CMD_SET_ONLINE = "setOnline";


    const CMD_SHOW_PROCESSING_LIST = "showProcessingList";


    /**
     * @var AuthoringService
     */
    protected $authoring_service;
    /**
     * @var entityIdBuilder
     */
    protected $entity_id_builder;
    /**
     * @var Link
     */
    protected $back_link;


    /**
     * @var ProcessingService
     */
    protected $processing_service;

    public function __construct() {
        global $DIC;

        $this->renderSubTabs();

        $this->authoring_service = $DIC->assessment()->questionAuthoring($DIC->ctrl()->getContextObjId(), $DIC->user()->getId());
        $this->entity_id_builder = $DIC->assessment()->entityIdBuilder();
        $this->back_link = $DIC->ui()->factory()->link()->standard('Back',$DIC->ctrl()->getLinkTarget($this));


        $this->processing_service = $DIC->assessment()->questionProcessing($DIC->ctrl()->getContextObjId(), $DIC->user()->getId());
    }

    /**
     * execute command
     */
    function executeCommand()
    {
        global $DIC;

        switch(strtolower($DIC->ctrl()->getCmdClass())) {
            case strtolower(ilAsqQuestionAuthoringGUI::class):
                //Get the specific question authoring service
                $authoring_gui = $this->authoring_service->question($this->authoring_service->currentOrNewQuestionId(), $this->back_link)->getAuthoringGUI();
                $DIC->ctrl()->forwardCommand($authoring_gui);
                break;
            default:
                switch($DIC->ctrl()->getCmd()) {
                    case self::CMD_SET_ONLINE:
                        $this->setOnline();
                        break;
                    case self::CMD_SHOW_PROCESSING_LIST:
                        $this->showProcessingList();
                        break;
                    default:
                        $this->showEditList();
                        break;
                }
        }
    }


 ////////////////
    ///
    ///
    ///
    /**
     * Authoring
     */
    ///
    ///
    ///
////////////////
    protected function showEditList() {
        global $DIC;

        $this->renderEditToolbar();

        $html = "";

        /**
         * Example Assoc Array for using in Tables
         */
        $questions = $this->authoring_service->questionList()->getQuestionsOfContainerAsAssocArray();

         if(count($questions) > 0) {
             $html = "<ul>";
             foreach($questions as $question) {
                 $row = array();
                 $row[] = $question['id'];
                 $row[] = $question['revision_id'];
                 $row[] = $question['data_title'];
                 $row[] = $DIC->ui()->renderer()->render(
                     $this->authoring_service->question(
                         $this->entity_id_builder->fromString(
                             $question['id']),
                         $this->back_link
                     )->getEditLink([ilRepositoryGUI::class,ilObjTestGUI::class,asqDebugGUI::class])
                 );
                 $row[] = $DIC->ui()->renderer()->render(
                     $this->authoring_service->question(
                         $this->entity_id_builder->fromString(
                             $question['id']),
                         $this->back_link
                     )->getPreviewLink([ilRepositoryGUI::class,ilObjTestGUI::class,asqDebugGUI::class])
                 );
                 $html .= '<li>'.implode($row," | ").'</li>';
             }
             $html .= "<ul>";

         }


        $DIC->ui()->mainTemplate()->setContent($html);
    }

    protected function setOnline() {
        global $DIC;
        foreach($this->authoring_service->questionList()->getQuestionsOfContainerAsDtoList() as $question_dto) {

            $revision_id = $this->entity_id_builder->new();

            $this->authoring_service->question($this->entity_id_builder->fromString($question_dto->getId()),$this->back_link)->publishNewRevision($revision_id);
        }
        $DIC->ctrl()->redirect($this);
    }

    /*
     *
		$back_link = $DIC->ui()->factory()->link()->standard('TODO',"#");
        $authoring_service = $DIC->assessment()->questionAuthoring($DIC->ctrl()->getContextObjId(), $DIC->user()->getId());
        $question_component = $authoring_service->questionComponent($DIC->assessment()->entityIdBuilder()->fromString($question_id));

        return $question_component;
     */


    //TODO right Place?
    /**
     * Project all Quetions
     */
    /*
if($form->getItemByPostVar('online')->getChecked() &&
$this->testOBJ->getOfflineStatus() == 1) {

}

*/

    protected function renderEditToolbar() {
        global $DIC;

        //Create Button
        $creationLinkComponent = $this->authoring_service->question($this->authoring_service->currentOrNewQuestionId(), $this->back_link)->getCreationLink([ilRepositoryGUI::class,ilObjTestGUI::class,asqDebugGUI::class]);

        require_once 'Services/UIComponent/Button/classes/class.ilLinkButton.php';
        $btn = ilLinkButton::getInstance();
        $btn->setCaption($creationLinkComponent->getLabel());
        $btn->setUrl($creationLinkComponent->getAction());
        $btn->setPrimary(true);
        $DIC->toolbar()->addButtonInstance($btn);

        //Set Online Button
        $btn = ilLinkButton::getInstance();
        $btn->setCaption("Set Online (Publish - creates new revisions of all questions)");
        $btn->setUrl($DIC->ctrl()->getLinkTarget($this,'setOnline'));
        $DIC->toolbar()->addButtonInstance($btn);
    }

    ////////////////
    ///
    ///
    ///
    /**
     * Processing
     */
    ///
    ///
    ///
    ////////////////
    public function showProcessingList() {
        global $DIC;

        /**
         * Example as DTO List
         */
        $arr_questions = $this->processing_service->questionList()->getQuestionsOfContainerAsDtoList();

        if(count($arr_questions) > 0) {
            $html = "<ul>";
            foreach($arr_questions as $question) {
                $row = array();
                $row[] = $question->getId();
                $row[] = $question->getRevisionId();
                $row[] = $question->getData()->getTitle();

                /*
                $row[] = $DIC->ui()->renderer()->render(
                    $this->authoring_service->question(
                        $this->entity_id_builder->fromString(
                            $question['id']),
                        $this->back_link
                    )->getEditLink([ilRepositoryGUI::class,ilObjTestGUI::class,asqDebugGUI::class])
                );
                $row[] = $DIC->ui()->renderer()->render(
                    $this->authoring_service->question(
                        $this->entity_id_builder->fromString(
                            $question['id']),
                        $this->back_link
                    )->getPreviewLink([ilRepositoryGUI::class,ilObjTestGUI::class,asqDebugGUI::class])
                );*/
                $html .= '<li>'.implode($row," | ").'</li>';
            }
            $html .= "<ul>";

        }


        $DIC->ui()->mainTemplate()->setContent($html);


    }



    ////////////////
    ///
    ///
    ///
    /**
     * Common
     */
    ///
    ///
    ///
    ////////////////
   public function renderSubTabs() {
       global $DIC;
       $DIC->tabs()->addSubTab(self::CMD_SHOW_EDIT_LIST, self::CMD_SHOW_EDIT_LIST,$DIC->ctrl()->getLinkTarget($this, self::CMD_SHOW_EDIT_LIST));

       $DIC->tabs()->addSubTab(self::CMD_SHOW_PROCESSING_LIST, self::CMD_SHOW_PROCESSING_LIST,$DIC->ctrl()->getLinkTarget($this, self::CMD_SHOW_PROCESSING_LIST));

       $DIC->tabs()->activateSubTab($DIC->ctrl()->getCmd(self::CMD_SHOW_EDIT_LIST));
   }

}