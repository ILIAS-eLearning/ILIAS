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

declare(strict_types=1);

use ILIAS\Questions\Presentation\Views\Edit;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\Renderer as UIRenderer;

/**
 * @ilCtrl_isCalledBy ilPCAnswerFormGUI: ilPageEditorGUI
 */
class ilPCAnswerFormGUI extends ilPageContentGUI
{
    private readonly ilTabsGUI $tabs;
    private readonly UIRenderer $ui_renderer;
    private readonly DataFactory $data_factory;
    private readonly ?Edit $edit_view;

    public function __construct(
        ilPageObject $pg_obj,
        ?ilPageContent $content_obj,
        string $hier_id,
        string $pc_id = ''
    ) {
        global $DIC;
        $this->tabs = $DIC['ilTabs'];
        $this->ui_renderer = $DIC['ui.renderer'];
        $this->data_factory = new DataFactory();

        $this->edit_view = $pg_obj->getEditView();

        parent::__construct($pg_obj, $content_obj, $hier_id, $pc_id);
    }

    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd() . 'Cmd';
        $this->$cmd();
    }

    public function insertCmd(): void
    {
        $content_obj = new ilPCAnswerForm($this->pg_obj);
        $content_obj->setHierId($this->hier_id);
        $this->tpl->setContent(
            $this->edit_view->createAnswerForm(
                $this->data_factory->uri(
                    ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTargetByClass(self::class, 'insert')
                ),
                $this->pg_obj->getParentId(),
                $this->pg_obj->getQuestion(),
                $content_obj
            )->render($this->ui_renderer)
        );
    }

    public function editCmd(): void
    {
        /** @var \ILIAS\Questions\Question\QuestionImplementation $question */
        $question = $this->pg_obj->getQuestion();
        $answer_form_properties = $question->getAnswerFormPropertiesByIdString(
            $this->getContentObject()->getAnswerFormIdStringFromAttribute()
        );

        $this->tpl->setContent(
            $this->edit_view->editAnswerForm(
                $this->data_factory->uri(
                    ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTargetByClass(self::class, 'edit')
                ),
                $this->pg_obj->getParentId(),
                $question,
                $answer_form_properties,
                $answer_form_properties->getDefinition()
            )->render($this->ui_renderer)
        );
    }
}
