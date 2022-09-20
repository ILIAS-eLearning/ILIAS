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

use ILIAS\LearningModule\Editing\EditingGUIRequest;

/**
 * Export IDs table
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilExportIDTableGUI extends ilTable2GUI
{
    protected bool $dup_info_given;
    protected array $cnt_exp_ids;
    protected bool $validation = false;
    protected ilAccessHandler $access;
    public bool $online_help_mode = false;
    protected EditingGUIRequest $request;
    protected \ilGlobalTemplateInterface $main_tpl;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        bool $a_validation = false,
        bool $a_oh_mode = false
    ) {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();

        $this->request = $DIC
            ->learningModule()
            ->internal()
            ->gui()
            ->editing()
            ->request();

        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();

        $this->setOnlineHelpMode($a_oh_mode);
        $this->setId("lm_expids");
        $this->validation = $a_validation;

        parent::__construct($a_parent_obj, $a_parent_cmd);

        if ($this->getOnlineHelpMode()) {
            $this->setData(ilStructureObject::getChapterList($this->parent_obj->getObject()->getId()));
            $this->cnt_exp_ids = ilLMPageObject::getDuplicateExportIDs(
                $this->parent_obj->getObject()->getId(),
                "st"
            );
        } else {
            $this->setData(ilLMPageObject::getPageList($this->parent_obj->getObject()->getId()));
            $this->cnt_exp_ids = ilLMPageObject::getDuplicateExportIDs(
                $this->parent_obj->getObject()->getId()
            );
        }

        $this->setTitle($lng->txt("cont_html_export_ids"));

        $this->addColumn($this->lng->txt("pg"), "title");
        $this->addColumn($this->lng->txt("cont_export_id"));

        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.export_id_row.html", "Modules/LearningModule");
        $this->setDefaultOrderField("title");
        $this->setDefaultOrderDirection("asc");

        $this->addCommandButton("saveExportIDs", $lng->txt("save"));
    }

    public function setOnlineHelpMode(bool $a_val): void
    {
        $this->online_help_mode = $a_val;
    }

    public function getOnlineHelpMode(): bool
    {
        return $this->online_help_mode;
    }

    protected function fillRow(array $a_set): void
    {
        $lng = $this->lng;

        $this->tpl->setVariable("VAL_TITLE", $a_set["title"]);
        $this->tpl->setVariable("PAGE_ID", $a_set["obj_id"]);

        $exp_id = ilLMPageObject::getExportId(
            $this->parent_obj->getObject()->getId(),
            $a_set["obj_id"],
            $a_set["type"]
        );

        $req_export_ids = $this->request->getExportIds();
        if ($this->validation) {
            if (!preg_match(
                "/^[a-zA-Z_]*$/",
                trim($req_export_ids[$a_set["obj_id"]])
            )) {
                // @todo: move to style
                $this->tpl->setVariable(
                    "STYLE",
                    " style='background-color: #FCEAEA;' "
                );
                $this->tpl->setVariable(
                    "ALERT_IMG",
                    ilUtil::img(
                        ilUtil::getImagePath("icon_alert.svg"),
                        $lng->txt("alert"),
                        "",
                        "",
                        0,
                        "",
                        "ilIcon"
                    )
                );
            }
            $this->tpl->setVariable(
                "EXPORT_ID",
                ilLegacyFormElementsUtil::prepareFormOutput(
                    ilUtil::stripSlashes($req_export_ids[$a_set["obj_id"]])
                )
            );
        } else {
            $this->tpl->setVariable(
                "EXPORT_ID",
                ilLegacyFormElementsUtil::prepareFormOutput($exp_id)
            );
        }

        if (($this->cnt_exp_ids[$exp_id] ?? 0) > 1) {
            $this->tpl->setVariable(
                "ITEM_ADD_TXT",
                $lng->txt("cont_exp_id_used_multiple")
            );
            $this->tpl->setVariable(
                "ALERT_IMG",
                ilUtil::img(
                    ilUtil::getImagePath("icon_alert.svg"),
                    $lng->txt("alert"),
                    "",
                    "",
                    0,
                    "",
                    "ilIcon"
                )
            );
            if (!$this->dup_info_given) {
                $this->main_tpl->setOnScreenMessage('info', $lng->txt("content_some_export_ids_multiple_times"));
                $this->dup_info_given = true;
            }
        }
    }
}
