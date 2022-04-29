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

use ILIAS\HTTP\Wrapper\WrapperFactory;
use ILIAS\Refinery\Factory as Refinery;

class ilSubStyleAssignmentGUI
{
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected ilToolbarGUI $toolbar;
    protected ilSystemStyleSettingsGUI $parent_gui;
    protected ilTree $tree;
    protected WrapperFactory $request_wrapper;
    protected Refinery $refinery;
    protected \ILIAS\UI\Factory $ui_factory;
    private ilSystemStyleMessageStack $message_stack;

    public function __construct(
        ilSystemStyleSettingsGUI $parent_gui,
        ilCtrl $ctrl,
        ilLanguage $lng,
        ilGlobalTemplateInterface $tpl,
        ilToolbarGUI $toolbar,
        ilTree $tree,
        WrapperFactory $request_wrapper,
        Refinery $refinery,
        \ILIAS\UI\Factory $ui_factory
    ) {
        $this->ctrl = $ctrl;
        $this->lng = $lng;
        $this->toolbar = $toolbar;
        $this->tpl = $tpl;
        $this->parent_gui = $parent_gui;
        $this->tree = $tree;
        $this->request_wrapper = $request_wrapper;
        $this->refinery = $refinery;
        $this->ui_factory = $ui_factory;
        $this->message_stack = new ilSystemStyleMessageStack($this->tpl);
    }

    /**
     * Assign styles to categories
     * @throws ilSystemStyleException
     */
    public function assignStyle(ilSkin $skin, ilSkinStyle $substyle) : void
    {
        $style = $skin->getStyle($substyle->getSubstyleOf());

        $this->toolbar->addComponent($this->ui_factory->button()->standard(
            $this->lng->txt('sty_add_assignment'),
            $this->ctrl->getLinkTarget($this->parent_gui, 'addAssignment')
        ));

        $tab = new ilSysStyleCatAssignmentTableGUI(
            $this->getParentGui(),
            'assignStyleToCat',
            $skin->getId(),
            $style->getId(),
            $substyle->getId()
        );

        $this->tpl->setContent($tab->getHTML());
    }

    /**
     * Add style category assignment
     */
    public function addAssignment() : void
    {
        include_once 'Services/Search/classes/class.ilSearchRootSelector.php';
        $exp = new ilSearchRootSelector(
            $this->ctrl->getLinkTarget($this->getParentGui(), 'addStyleCatAssignment')
        );
        $expand_id = $this->tree->readRootId();
        if ($this->request_wrapper->query()->has('search_root_expand')) {
            $expand_id = $this->request_wrapper->query()->retrieve(
                'search_root_expand',
                $this->refinery->kindlyTo()->string()
            );
        }
        $exp->setExpand($expand_id);
        $exp->setExpandTarget($this->ctrl->getLinkTarget($this->getParentGui(), 'addAssignment'));
        $exp->setTargetClass(get_class($this->getParentGui()));
        $exp->setCmd('saveAssignment');
        $exp->setClickableTypes(['cat']);

        $exp->setOutput(0);
        $this->tpl->setContent($exp->getOutput());
    }

    /**
     * Save style category assignment
     */
    public function saveAssignment(ilSkin $skin, ilSkinStyle $substyle) : void
    {
        $style = $skin->getStyle($substyle->getSubstyleOf());
        try {
            $root_id = $this->request_wrapper->query()->retrieve(
                'root_id',
                $this->refinery->kindlyTo()->string()
            );
            ilSystemStyleSettings::writeSystemStyleCategoryAssignment(
                $skin->getId(),
                $style->getId(),
                $substyle->getId(),
                $root_id
            );
            $this->message_stack->addMessage(new ilSystemStyleMessage($this->lng->txt('msg_obj_modified')));
        } catch (ilSystemStyleException $e) {
            $message = $this->lng->txt('msg_assignment_failed') . $e->getMessage();
            $this->message_stack->addMessage(new ilSystemStyleMessage($message, ilSystemStyleMessage::TYPE_ERROR));
        }
        $this->message_stack->sendMessages();
        $this->ctrl->redirect($this->getParentGui(), 'assignStyle');
    }

    /**
     * Delete system style to category assignments
     */
    public function deleteAssignments(ilSkin $skin, ilSkinStyle $substyle) : void
    {
        $style = $skin->getStyle($substyle->getSubstyleOf());

        if ($this->request_wrapper->post()->has('id')) {
            $ids = $this->request_wrapper->post()->retrieve('id', $this->refinery->identity());
            foreach ($ids as $id) {
                $id_arr = explode(':', $id);
                ilSystemStyleSettings::deleteSystemStyleCategoryAssignment(
                    $skin->getId(),
                    $style->getId(),
                    $substyle->getId(),
                    $id_arr[1]
                );
            }
            $this->message_stack->addMessage(new ilSystemStyleMessage($this->lng->txt('msg_obj_modified')));
        } else {
            $this->message_stack->addMessage(new ilSystemStyleMessage(
                $this->lng->txt('no_style_selected'),
                ilSystemStyleMessage::TYPE_ERROR
            ));
        }
        $this->message_stack->sendMessages();
        $this->ctrl->redirect($this->getParentGui(), 'assignStyle');
    }

    public function getParentGui() : ilSystemStyleSettingsGUI
    {
        return $this->parent_gui;
    }

    public function setParentGui(ilSystemStyleSettingsGUI $parent_gui) : void
    {
        $this->parent_gui = $parent_gui;
    }
}
