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
 * Lists History entrys in chronological order
 *
 * @author Fabian Wolf <wolf@leifos.com>
 */
class ilHistoryTableGUI extends ilTable2GUI
{
    protected int $obj_id;
    protected string $obj_type;
    protected int $ref_id;
    protected ilCtrl $ilCtrl;
    protected bool $comment_visibility = false;


    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        int $a_obj_id,
        string $a_obj_type = null
    ) {
        global $DIC;

        $ilCtrl = $DIC->ctrl();
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setObjId($a_obj_id);
        $this->setObjType($a_obj_type);
        $this->ilCtrl = $ilCtrl;
    }

    public function getDataFromDb(): void
    {
        $entries = ilHistory::_getEntriesForObject($this->getObjId(), $this->getObjType());
        $this->setData($entries);
    }

    public function initTable(): void
    {
        $this->setRowTemplate("tpl.history_row.html", "Services/History");
        $this->setFormAction($this->ilCtrl->getFormAction($this->getParentObject()));

        $this->setTitle($this->lng->txt("history"));
        $this->addColumn($this->lng->txt("user"), "", "25%");
        $this->addColumn($this->lng->txt("date"), "", "25%");
        $this->addColumn($this->lng->txt("action"), "", "50%");

        $this->getDataFromDb();
    }

    protected function fillRow(array $a_set): void
    {
        $this->tpl->setVariable("TXT_USER", ilUserUtil::getNamePresentation($a_set["user_id"], false, false));
        $this->tpl->setVariable(
            'TXT_DATE',
            ilDatePresentation::formatDate(new ilDateTime($a_set["date"], IL_CAL_DATETIME))
        );
        $this->tpl->setVariable("TXT_ACTION", $this->createInfoText($a_set));

        if ($this->getObjType() == "lm") {
            $obj_arr = explode(":", $a_set["obj_type"]);
            switch ($obj_arr[1]) {
                case "st":
                    $img_type = "st";
                    $class = "ilstructureobjectgui";
                    $cmd = "view";
                    break;

                case "pg":
                    $img_type = "pg";
                    $class = "illmpageobjectgui";
                    $cmd = "edit";
                    break;

                default:
                    $img_type = $obj_arr[0];
                    $class = "";
                    $cmd = "view";
                    break;
            }

            $this->tpl->setCurrentBlock("item_icon");
            $this->tpl->setVariable("SRC_ICON", ilUtil::getImagePath("icon_" . $img_type . ".svg"));
            $this->tpl->parseCurrentBlock();

            if ($class != "") {
                $this->tpl->setCurrentBlock("item_link");
                $this->ilCtrl->setParameterByClass($class, "obj_id", $a_set["obj_id"]);
                $this->tpl->setVariable(
                    "HREF_LINK",
                    $this->ilCtrl->getLinkTargetByClass($class, $cmd)
                );
                $this->tpl->setVariable("TXT_LINK", $a_set["title"]);
            } else {
                $this->tpl->setCurrentBlock("item_title");
                $this->tpl->setVariable(
                    "TXT_TITLE",
                    ilObject::_lookupTitle($a_set["obj_id"])
                );
            }
            $this->tpl->parseCurrentBlock();
        }

        if ($this->isCommentVisible() && $a_set["user_comment"] != "") {
            $this->tpl->setCurrentBlock("user_comment");
            $this->tpl->setVariable("TXT_COMMENT", $this->lng->txt("comment"));
            $this->tpl->setVariable("TXT_USER_COMMENT", $a_set["user_comment"]);
            $this->tpl->parseCurrentBlock();
        }
    }

    /**
     * format info parameters into info text
     */
    protected function createInfoText(array $a_set): string
    {
        $info_params = explode(",", $a_set["info_params"]);

        switch ($this->getObjType()) {
            case "lm":
                $info_text = $this->lng->txt("hist_" . str_replace(":", "_", $a_set["obj_type"]) .
            "_" . $a_set["action"]);
                break;
            default:
                $info_text = $this->lng->txt("hist_" . str_replace(":", "_", $this->getObjType()) .
                    "_" . $a_set["action"]);
                break;
        }

        $i = 1;
        foreach ($info_params as $info_param) {
            $info_text = str_replace("%" . $i, $info_param, $info_text);
            $i++;
        }

        return $info_text;
    }

    public function setCommentVisibility(bool $a_visible): void
    {
        $this->comment_visibility = $a_visible;
    }

    public function isCommentVisible(): bool
    {
        return $this->comment_visibility;
    }

    public function setObjId(int $a_obj_id): void
    {
        $this->obj_id = $a_obj_id;
    }

    public function getObjId(): int
    {
        return $this->obj_id;
    }

    public function setObjType(string $a_obj_type): void
    {
        $this->obj_type = $a_obj_type;
    }

    public function getObjType(): string
    {
        if (!$this->obj_type) {
            $this->setObjType(ilObject::_lookupType($this->getObjId()));
        }
        return $this->obj_type;
    }
}
