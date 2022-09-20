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

/**
 * TableGUI class for page layouts
 *
 * @author Hendrik Holtmann <holtmann@me.com>
 */
class ilPageLayoutTableGUI extends ilTable2GUI
{
    protected \ILIAS\DI\UIServices $ui;
    protected array $all_mods;
    protected ilRbacSystem $rbacsystem;
    protected ilCtrl $ctrl;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->rbacsystem = $DIC->rbac()->system();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $this->ui = $DIC->ui();

        $lng->loadLanguageModule("content");

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->addColumn("", "", "2%");

        $this->addColumn($lng->txt("active"));
        $this->addColumn($lng->txt("thumbnail"));
        $this->addColumn($lng->txt("title"));
        $this->addColumn($lng->txt("description"));
        $this->addColumn($lng->txt("modules"));
        $this->addColumn($lng->txt("actions"));

        // show command buttons, if write permission is given
        if ($a_parent_obj->checkPermission("sty_write_page_layout", false)) {
            $this->addMultiCommand("activate", $lng->txt("activate"));
            $this->addMultiCommand("deactivate", $lng->txt("deactivate"));
            $this->addMultiCommand("deletePgl", $lng->txt("delete"));
        }

        $this->getPageLayouts();

        $this->setSelectAllCheckbox("pglayout");
        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate(
            "tpl.stys_pglayout_table_row.html",
            "Services/COPage/Layout"
        );
        $this->setTitle($lng->txt("page_layouts"));
    }

    /**
     * Get a List of all Page Layouts
     */
    public function getPageLayouts(): void
    {
        $this->setData(ilPageLayout::getLayoutsAsArray());
        $this->all_mods = ilPageLayout::getAvailableModules();
    }

    protected function fillRow(array $a_set): void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $ilCtrl->setParameter($this->parent_obj, "layout_id", "");

        // modules
        $this->tpl->setCurrentBlock("mod");
        foreach ($this->all_mods as $mod_id => $mod_caption) {
            if (($mod_id == ilPageLayout::MODULE_SCORM && $a_set["mod_scorm"]) ||
                ($mod_id == ilPageLayout::MODULE_PORTFOLIO && $a_set["mod_portfolio"]) ||
                ($mod_id == ilPageLayout::MODULE_LM && $a_set["mod_lm"])) {
                $this->tpl->setVariable("MOD_NAME", $mod_caption);
                $this->tpl->parseCurrentBlock();
            }
        }

        if ($a_set['active']) {
            $this->tpl->setVariable("IMG_ACTIVE", ilUtil::getImagePath("icon_ok.svg"));
        } else {
            $this->tpl->setVariable("IMG_ACTIVE", ilUtil::getImagePath("icon_not_ok.svg"));
        }
        $this->tpl->setVariable("VAL_TITLE", $a_set['title']);
        $this->tpl->setVariable("VAL_DESCRIPTION", $a_set['description']);
        $this->tpl->setVariable("CHECKBOX_ID", $a_set['layout_id']);

        $ilCtrl->setParameter($this->parent_obj, "obj_id", $a_set['layout_id']);

        if ($this->parent_obj->checkPermission("sty_write_page_layout", false)) {
            $links[] = $this->ui->factory()->link()->standard(
                $this->lng->txt("edit"),
                $ilCtrl->getLinkTarget($this->parent_obj, "editPg")
            );
            $links[] = $this->ui->factory()->link()->standard(
                $this->lng->txt("settings"),
                $ilCtrl->getLinkTargetByClass("ilpagelayoutgui", "properties")
            );
            $links[] = $this->ui->factory()->link()->standard(
                $this->lng->txt("export"),
                $ilCtrl->getLinkTarget($this->parent_obj, "exportLayout")
            );
            $dd = $this->ui->factory()->dropdown()->standard($links);

            $this->tpl->setVariable(
                "ACTIONS",
                $this->ui->renderer()->render($dd)
            );
        }

        $pgl_obj = new ilPageLayout($a_set['layout_id']);
        $this->tpl->setVariable("VAL_PREVIEW_HTML", $pgl_obj->getPreview());
    }
}
