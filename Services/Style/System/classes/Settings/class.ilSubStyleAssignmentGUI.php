<?php declare(strict_types=1);

class ilSubStyleAssignmentGUI
{
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilTemplate $tpl;
    protected ilToolbarGUI $toolbar;
    protected ilSystemStyleSettingsGUI $parent_gui;
    protected ilTree $tree;

    public function __construct(ilSystemStyleSettingsGUI $parent_gui)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->toolbar = $DIC->toolbar();
        $this->tpl = $DIC["tpl"];
        $this->parent_gui = $parent_gui;
        $this->tree = $DIC["tree"];
    }

    /**
     * Assign styles to categories
     *
     * @throws ilSystemStyleException
     */
    public function assignStyle(ilSkinXML $skin, ilSkinStyleXML $substyle) : void
    {
        $style = $skin->getStyle($substyle->getSubstyleOf());

        $this->toolbar->addFormButton($this->lng->txt("sty_add_assignment"), "addAssignment");
        $this->toolbar->setFormAction($this->ctrl->getFormAction($this->getParentGui()));

        $tab = new ilSysStyleCatAssignmentTableGUI(
            $this->getParentGui(),
            "assignStyleToCat",
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
        $exp->setExpand($_GET["search_root_expand"] ? $_GET["search_root_expand"] : $this->tree->readRootId());
        $exp->setExpandTarget($this->ctrl->getLinkTarget($this->getParentGui(), 'addAssignment'));
        $exp->setTargetClass(get_class($this->getParentGui()));
        $exp->setCmd('saveAssignment');
        $exp->setClickableTypes(["cat"]);

        $exp->setOutput(0);
        $this->tpl->setContent($exp->getOutput());
    }


    /**
     * Save style category assignment
     */
    public function saveAssignment(ilSkinXML $skin, ilSkinStyleXML $substyle) : void
    {
        $style = $skin->getStyle($substyle->getSubstyleOf());
        try {
            ilSystemStyleSettings::writeSystemStyleCategoryAssignment(
                $skin->getId(),
                $style->getId(),
                $substyle->getId(),
                $_GET["root_id"]
            );
            ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
        } catch (ilSystemStyleException $e) {
            ilUtil::sendFailure($this->lng->txt("msg_assignment_failed") . $e->getMessage(), true);
        }


        $this->ctrl->redirect($this->getParentGui(), "assignStyle");
    }

    /**
     * Delete system style to category assignments
     */
    public function deleteAssignments(ilSkinXML $skin, ilSkinStyleXML $substyle) : void
    {
        $style = $skin->getStyle($substyle->getSubstyleOf());


        if (is_array($_POST["id"])) {
            foreach ($_POST["id"] as $id) {
                $id_arr = explode(":", $id);
                ilSystemStyleSettings::deleteSystemStyleCategoryAssignment(
                    $skin->getId(),
                    $style->getId(),
                    $substyle->getId(),
                    $id_arr[1]
                );
            }
            ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
        } else {
            ilUtil::sendFailure($this->lng->txt("no_style_selected"), true);
        }

        $this->ctrl->redirect($this->getParentGui(), "assignStyle");
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
