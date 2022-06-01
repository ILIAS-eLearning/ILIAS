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
 * TableGUI clipboard items
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilClipboardTableGUI extends ilTable2GUI
{
    protected ilAccessHandler $access;
    protected ilObjUser $user;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->user = $DIC->user();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $lng->loadLanguageModule("mep");

        $this->addColumn("", "", "1");	// checkbox
        $this->addColumn($lng->txt("mep_thumbnail"), "", "1");
        $this->addColumn($lng->txt("mep_title_and_description"), "", "100%");
        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate(
            "tpl.clipboard_tbl_row.html",
            "Modules/MediaPool/Clipboard"
        );
        $this->getItems();
        
        // title
        $this->setTitle($lng->txt("clipboard"));

        $this->setDefaultOrderField("title");
        
        // action commands
        if ($this->parent_obj->mode === "getObject") {
            $this->addMultiCommand("insert", $this->parent_obj->getInsertButtonTitle());
        }
        $this->addMultiCommand("remove", $lng->txt("remove"));
        
        $this->setSelectAllCheckbox("id");
    }

    /**
     * Get items from user clipboard
     */
    public function getItems() : void
    {
        $ilUser = $this->user;
        
        $objs = $ilUser->getClipboardObjects("mob");
        $objs2 = $ilUser->getClipboardObjects("incl");
        $objs = array_merge($objs, $objs2);

        $this->setData($objs);
    }
    
    protected function fillRow(array $a_set) : void
    {
        $ilCtrl = $this->ctrl;

        $mob = null;
        if ($a_set["type"] === "mob") {
            // output thumbnail
            $mob = new ilObjMediaObject($a_set["id"]);
            $med = $mob->getMediaItem("Standard");
            $target = $med->getThumbnailTarget();
            if ($target !== "") {
                $this->tpl->setCurrentBlock("thumbnail");
                $this->tpl->setVariable("IMG_THUMB", $target);
                $this->tpl->parseCurrentBlock();
            }
            if (ilUtil::deducibleSize($med->getFormat()) &&
                $med->getLocationType() === "Reference") {
                $size = getimagesize($med->getLocation());
                if ($size[0] > 0 && $size[1] > 0) {
                    $wr = $size[0] / 80;
                    $hr = $size[1] / 80;
                    $r = max($wr, $hr);
                    $w = (int) ($size[0] / $r);
                    $h = (int) ($size[1] / $r);
                    $this->tpl->setVariable(
                        "IMG",
                        ilUtil::img($med->getLocation(), "", $w, $h)
                    );
                }
            }
        } elseif ($a_set["type"] === "incl") {
            $this->tpl->setCurrentBlock("thumbnail");
            $this->tpl->setVariable(
                "IMG_THUMB",
                ilUtil::getImagePath("icon_pg.svg")
            );
            $this->tpl->parseCurrentBlock();
        }

        // allow editing of media objects
        if ($this->parent_obj->mode !== "getObject" && $a_set["type"] === "mob") {
            // output edit link
            $this->tpl->setCurrentBlock("edit");
            $ilCtrl->setParameter($this->parent_obj, "clip_item_id", $a_set["id"]);
            $ilCtrl->setParameterByClass("ilObjMediaObjectGUI", "clip_item_id", $a_set["id"]);
            $this->tpl->setVariable(
                "EDIT_LINK",
                $ilCtrl->getLinkTargetByClass(
                    "ilObjMediaObjectGUI",
                    "edit"
                )
            );
            $this->tpl->setVariable("TEXT_OBJECT", $a_set["title"] .
                " [" . $a_set["id"] . "]");
        } else {		// just list elements for selection
            $this->tpl->setCurrentBlock("show");
            $this->tpl->setVariable("TEXT_OBJECT2", $a_set["title"] .
                " [" . $a_set["id"] . "]");
        }
        $this->tpl->parseCurrentBlock();

        if ($a_set["type"] === "mob") {
            $this->tpl->setVariable(
                "MEDIA_INFO",
                ilObjMediaObjectGUI::_getMediaInfoHTML($mob)
            );
        }
        $this->tpl->setVariable("CHECKBOX_ID", $a_set["type"] . ":" . $a_set["id"]);
    }
}
