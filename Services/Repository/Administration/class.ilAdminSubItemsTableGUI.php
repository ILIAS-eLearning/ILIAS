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

use ILIAS\Repository\Clipboard\ClipboardManager;

/**
 * TableGUI class for sub items listed in repository administration
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilAdminSubItemsTableGUI extends ilTable2GUI
{
    protected ilAccessHandler $access;
    protected ilRbacSystem $rbacsystem;
    protected ilObjectDefinition $obj_definition;
    protected ilTree $tree;
    protected bool $editable = false;
    protected int $ref_id;
    protected ClipboardManager $clipboard;

    public function __construct(
        ilObjectGUI $a_parent_obj,
        string $a_parent_cmd,
        int $a_ref_id,
        bool $editable = false
    ) {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $this->editable = $editable;
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->obj_definition = $DIC["objDefinition"];
        $this->tree = $DIC->repositoryTree();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $this->clipboard = $DIC
            ->repository()
            ->internal()
            ->domain()
            ->clipboard();

        $this->ref_id = $a_ref_id;

        $this->setId('recf_' . $a_ref_id);
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setSelectAllCheckbox("id[]");

        $this->addColumn("", "", "1", true);
        $this->addColumn($this->lng->txt("type"), "", "1");
        $this->addColumn($this->lng->txt("title"), "title");
        $this->addColumn($this->lng->txt("last_change"), "last_update", "25%");

        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.admin_sub_items_row.html", "Services/Repository");
        //$this->disable("footer");
        $this->setEnableTitle(true);
        $this->setDefaultOrderField("title");
        $this->setDefaultOrderDirection("asc");

        // TODO: Needs other solution
        if (ilObject::_lookupType($a_ref_id, true) === 'chac') {
            $this->getItems();
            return;
        }

        if (ilObject::_lookupType($a_ref_id, true) !== "recf") {
            if ($this->clipboard->hasEntries()) {
                if ($this->isEditable()) {
                    $this->addCommandButton("paste", $lng->txt("paste"));
                    $this->addCommandButton("clear", $lng->txt("clear"));
                }
            } elseif ($this->isEditable()) {
                $this->addMultiCommand("cut", $lng->txt("cut"));
                $this->addMultiCommand("delete", $lng->txt("delete"));
                $this->addMultiCommand("link", $lng->txt("link"));
            }
        } elseif ($this->clipboard->hasEntries()) {
            if ($this->isEditable()) {
                $this->addCommandButton("clear", $lng->txt("clear"));
            }
        } elseif ($this->isEditable()) {
            $this->addMultiCommand("cut", $lng->txt("cut"));
            $this->addMultiCommand("removeFromSystem", $lng->txt("btn_remove_system"));
        }
        $this->getItems();
    }

    public function isEditable(): bool
    {
        return $this->editable;
    }

    public function getItems(): void
    {
        $rbacsystem = $this->rbacsystem;
        $objDefinition = $this->obj_definition;
        $tree = $this->tree;

        $items = [];
        $childs = $tree->getChilds($this->ref_id);
        foreach ($childs as $key => $val) {
            // visible
            if (!$rbacsystem->checkAccess("visible", $val["ref_id"])) {
                continue;
            }

            // hide object types in devmode
            if ($objDefinition->getDevMode($val["type"])) {
                continue;
            }

            // don't show administration in root node list
            if ($val["type"] === "adm") {
                continue;
            }
            if (!$this->parent_obj->isVisible($val["ref_id"], $val["type"])) {
                continue;
            }
            $items[] = $val;
        }
        $this->setData($items);
    }

    protected function fillRow(array $a_set): void
    {
        $lng = $this->lng;
        $objDefinition = $this->obj_definition;
        $ilCtrl = $this->ctrl;

        // surpress checkbox for particular object types AND the system role
        if (!$objDefinition->hasCheckbox($a_set["type"]) ||
            (int) $a_set["obj_id"] === SYSTEM_ROLE_ID ||
            (int) $a_set["obj_id"] === SYSTEM_USER_ID ||
            (int) $a_set["obj_id"] === ANONYMOUS_ROLE_ID) {
            $this->tpl->touchBlock("no_checkbox");
        } else {
            $this->tpl->setCurrentBlock("checkbox");
            $this->tpl->setVariable("ID", $a_set["ref_id"]);
            $this->tpl->parseCurrentBlock();
        }

        //build link
        $class_name = $objDefinition->getClassName($a_set["type"]);
        $class = strtolower("ilObj" . $class_name . "GUI");
        $ilCtrl->setParameterByClass($class, "ref_id", $a_set["ref_id"]);
        $this->tpl->setVariable("HREF_TITLE", $ilCtrl->getLinkTargetByClass($class, "view"));
        $ilCtrl->setParameterByClass($class, "ref_id", $this->ref_id);

        // TODO: broken! fix me
        $title = $a_set["title"];
        if ($this->clipboard->hasEntries() && in_array($a_set["ref_id"], $this->clipboard->getRefIds())) {
            switch ($this->clipboard->getCmd()) {
                case "cut":
                    $title = "<del>" . $title . "</del>";
                    break;

                case "copy":
                    $title = "<font color=\"green\">+</font>  " . $title;
                    break;

                case "link":
                    $title = "<font color=\"black\"><</font> " . $title;
                    break;
            }
        }
        $this->tpl->setVariable("VAL_TITLE", $title);
        $this->tpl->setVariable("VAL_DESC", ilStr::shortenTextExtended($a_set["desc"], ilObject::DESC_LENGTH, true));
        $this->tpl->setVariable("VAL_LAST_CHANGE", ilDatePresentation::formatDate(new ilDateTime($a_set["last_update"], IL_CAL_DATETIME)));
        $alt = ($objDefinition->isPlugin($a_set["type"]))
            ? $lng->txt("icon") . " " . ilObjectPlugin::lookupTxtById($a_set["type"], "obj_" . $a_set["type"])
            : $lng->txt("icon") . " " . $lng->txt("obj_" . $a_set["type"]);
        $this->tpl->setVariable(
            "IMG_TYPE",
            ilUtil::img(ilObject::_getIcon((int) $a_set["obj_id"], "small"), $alt, "", "", "", "", "ilIcon")
        );
    }
}
