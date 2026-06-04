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

use ILIAS\Questions\Legacy\LocalDIC;
use ILIAS\Questions\Presentation\Edit;
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
    private readonly Edit $edit_view;

    public function __construct(
        ilPageObject $a_pg_obj,
        ?ilPageContent $a_content_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ) {
        global $DIC;
        $this->tabs = $DIC['ilTabs'];
        $this->ui_renderer = $DIC['ui.renderer'];
        $this->data_factory = new DataFactory();
        $this->edit_view = LocalDIC::dic()[Edit::class];

        parent::__construct($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id);
    }

    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd() . 'Cmd';
        $this->$cmd();
    }

    public function insertCmd(): void
    {
        $this->setInsertTabs();
        $this->tpl->setContent(
            $this->ui_renderer->render(
                $this->edit_view->createAnswerForm(
                    $this->data_factory->uri(
                        ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTargetByClass(self::class, 'insert')
                    )
                )
            )
        );
    }

    public function editCmd(): void
    {
        $this->setInsertTabs();
        $this->tpl->setContent(
            $this->ui_renderer->render(
                $this->edit_view->editAnswerForm(
                    $this->data_factory->uri(
                        ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTargetByClass(self::class, 'insert')
                    )
                )
            )
        );
    }

    private function setInsertTabs(): void
    {
        $this->tabs->setBackTarget(
            $this->lng->txt('cancel'),
            $this->ctrl->getLinkTargetByClass(\QstsQuestionPageGUI::class, 'edit')
        );
    }

    public function setEditTabs(): void
    {
    }
}
