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

namespace ILIAS\LearningSequence\Content\Sequential;

use ILIAS\LearningSequence\Content\LSOContentController;
use ILIAS\LearningSequence\Content\LSOContentDeletion;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ilConfirmationGUI;
use ilLanguage;
use ilObject;
use ilObjLearningSequence;
use ilObjLearningSequenceActionData;
use ilObjLearningSequenceContentGUI;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Controls content management for sequential learning sequences.
 */
class LSOSequentialContent implements LSOContentController
{
    use LSOContentDeletion;

    /** @var \ilObjLearningSequenceContentGUI */
    protected \ilObjLearningSequenceContentGUI $parent_gui;
    /** @var Factory */
    protected Factory $ui_factory;
    /** @var Renderer */
    protected Renderer $ui_renderer;
    /** @var \ilLanguage */
    protected \ilLanguage $lng;
    /** @var \ilCtrl */
    protected \ilCtrl $ctrl;
    /** @var ServerRequestInterface */
    protected ServerRequestInterface $request;
    /** @var \ilGlobalTemplateInterface */
    protected \ilGlobalTemplateInterface $tpl;

    /** @var int */
    protected int $ref_id;
    /** @var int */
    protected int $obj_id;

    /**
     * Creates a controller for a sequential learning sequence.
     */
    public function __construct(
        \ilObjLearningSequenceContentGUI $parent_gui,
        Factory $ui_factory,
        Renderer $ui_renderer,
        ilLanguage $lng,
        \ilCtrl $ctrl,
        ServerRequestInterface $request,
        \ilGlobalTemplateInterface $tpl,
        int $ref_id,
        int $obj_id
    ) {
        $this->parent_gui = $parent_gui;
        $this->ui_factory = $ui_factory;
        $this->ui_renderer = $ui_renderer;
        $this->lng = $lng;
        $this->ctrl = $ctrl;
        $this->request = $request;
        $this->tpl = $tpl;
        $this->ref_id = $ref_id;
        $this->obj_id = $obj_id;
    }

    /**
     * Returns the commands supported by this controller.
     *
     * @return string[]
     */
    public function getSupportedCommands(): array
    {
        return [
            ilObjLearningSequenceContentGUI::CMD_MANAGE_CONTENT,
            ilObjLearningSequenceContentGUI::CMD_REORDER,
            ilObjLearningSequenceContentGUI::CMD_CONFIRM_DELETE,
            ilObjLearningSequenceContentGUI::CMD_DELETE,
        ];
    }

    /**
     * Renders a classic confirmation page for deleting the selected content
     * item(s) of the ordering table.
     *
     * The ordering table's delete action is a regular (non async) action, so
     * the selected row ids arrive as query parameters. They are listed on a
     * dedicated confirmation page whose confirm button submits them to the
     * delete command. This works for a single item as well as for a bulk
     * selection.
     */
    public function confirmDelete(): void
    {
        $ref_ids = $this->readSelectedRefIds();

        if ($ref_ids === []) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('no_checkbox_selected'), true);
            $this->ctrl->redirect($this->parent_gui, ilObjLearningSequenceContentGUI::CMD_MANAGE_CONTENT);
        }

        $confirmation = new ilConfirmationGUI();
        $confirmation->setFormAction($this->ctrl->getFormAction($this->parent_gui));
        $confirmation->setHeaderText($this->lng->txt('info_delete_sure'));
        $confirmation->setConfirm($this->lng->txt('confirm'), ilObjLearningSequenceContentGUI::CMD_DELETE);
        $confirmation->setCancel($this->lng->txt('cancel'), ilObjLearningSequenceContentGUI::CMD_MANAGE_CONTENT);

        foreach ($ref_ids as $ref_id) {
            $title = ilObject::_lookupTitle(ilObject::_lookupObjId($ref_id));
            $confirmation->addItem('id[]', (string) $ref_id, $title);
        }

        $this->parent_gui->setContent($confirmation->getHTML());
    }

    public function manageContent(): void
    {
        $this->parent_gui->showPossibleSubObjects();

        $items = ilObjLearningSequence::getInstanceByRefId($this->ref_id)->getLSItems();
        $target_url = $this->ctrl->getFormAction($this->parent_gui, ilObjLearningSequenceContentGUI::CMD_REORDER);

        $filter_gui = new LSOSequentialFilter($this->ui_factory, $this->lng, $this->ctrl, $this->parent_gui);
        $filter = $filter_gui->getFilter(ilObjLearningSequenceContentGUI::CMD_MANAGE_CONTENT)
            ->withRequest($this->request);
        $filter_data = $filter->getData();

        $table = new LSOSequentialTable(
            $this->parent_gui,
            $this->ui_factory,
            $this->ui_renderer,
            $this->lng,
            $this->ctrl,
            $items,
            $target_url,
            $this->lng->txt('content'),
            $this->request,
            $this->tpl,
            $this->ref_id,
            $this->obj_id,
            $filter_data
        );

        $this->parent_gui->setContent(
            $this->ui_renderer->render($filter)
            . $table->render()
        );
    }

    /**
     * Stores the order submitted by the ordering table.
     */
    public function reorder(): void
    {
        $items = ilObjLearningSequence::getInstanceByRefId($this->ref_id)->getLSItems();
        $target_url = $this->ctrl->getFormAction($this->parent_gui, ilObjLearningSequenceContentGUI::CMD_REORDER);

        $table = new LSOSequentialTable(
            $this->parent_gui,
            $this->ui_factory,
            $this->ui_renderer,
            $this->lng,
            $this->ctrl,
            $items,
            $target_url,
            $this->lng->txt('content'),
            $this->request,
            $this->tpl,
            $this->ref_id,
            $this->obj_id
        );

        $ordered_ref_ids = $table->getOrderedRefIds();
        if ($ordered_ref_ids !== []) {
            $new_positions = array_flip($ordered_ref_ids);

            $lso = ilObjLearningSequence::getInstanceByRefId($this->ref_id);
            $updated = [];
            foreach ($lso->getLSItems() as $it) {
                if (isset($new_positions[$it->getRefId()])) {
                    $it = $it->withOrderNumber($new_positions[$it->getRefId()]);
                }
                $updated[] = $it;
            }
            $lso->storeLSItems($updated);

            $this->ctrl->redirect($this->parent_gui, ilObjLearningSequenceContentGUI::CMD_MANAGE_CONTENT);
        }

        $this->parent_gui->setContent($table->render());
    }

    /**
     * Returns actions specific to a sequential content item.
     *
     * @return array<string, ilObjLearningSequenceActionData>
     */
    public function getSpecificActions(int $ref_id, string $current_op): array
    {
        $specific_actions = [];
        $specific_actions['condition_always'] = new ilObjLearningSequenceActionData(
            label: $this->lng->txt('table_may_proceed') . ': ' . $this->lng->txt('always'),
            link: ilObjLearningSequenceContentGUI::CMD_SET_CONDITION_ALWAYS
        );
        $specific_actions['condition_lp'] = new ilObjLearningSequenceActionData(
            label: $this->lng->txt('table_may_proceed') . ': ' . $this->lng->txt('condition_learning_progress'),
            link: ilObjLearningSequenceContentGUI::CMD_SET_CONDITION_LP
        );
        return $specific_actions;
    }
}
