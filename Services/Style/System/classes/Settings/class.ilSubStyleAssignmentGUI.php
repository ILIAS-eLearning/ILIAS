<?php

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
        $exp->setExpand($_GET['search_root_expand'] ? $_GET['search_root_expand'] : $this->tree->readRootId());
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
            ilSystemStyleSettings::writeSystemStyleCategoryAssignment(
                $skin->getId(),
                $style->getId(),
                $substyle->getId(),
                $_GET['root_id']
            );
            ilUtil::sendSuccess($this->lng->txt('msg_obj_modified'), true);
        } catch (ilSystemStyleException $e) {
            ilUtil::sendFailure($this->lng->txt('msg_assignment_failed') . $e->getMessage(), true);
        }

        $this->ctrl->redirect($this->getParentGui(), 'assignStyle');
    }

    /**
     * Delete system style to category assignments
     */
    public function deleteAssignments(ilSkin $skin, ilSkinStyle $substyle) : void
    {
        $style = $skin->getStyle($substyle->getSubstyleOf());

        if (is_array($_POST['id'])) {
            foreach ($_POST['id'] as $id) {
                $id_arr = explode(':', $id);
                ilSystemStyleSettings::deleteSystemStyleCategoryAssignment(
                    $skin->getId(),
                    $style->getId(),
                    $substyle->getId(),
                    $id_arr[1]
                );
            }
            ilUtil::sendSuccess($this->lng->txt('msg_obj_modified'), true);
        } else {
            ilUtil::sendFailure($this->lng->txt('no_style_selected'), true);
        }

        $this->ctrl->redirect($this->getParentGui(), 'assignStyle');
    }

    public function getParentGui() : ilSystemStyleSettingsGUI
    {
        return $this->parent_gui;
    }

    public function setParentGui(ilSystemStyleSettingsGUI $parent_gui)
    {
        $this->parent_gui = $parent_gui;
    }
}
