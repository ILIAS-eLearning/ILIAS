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
 * TableGUI class for account codes
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilAccountCodesTableGUI extends ilTable2GUI
{
    /**
     * @var array<string,string>
     */
    public array $filter;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd
    ) {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];

        $this->setId("user_account_code");

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->addColumn("", "", "1", true);
        $this->addColumn($lng->txt("user_account_code"), "code");
        $this->addColumn($lng->txt("user_account_code_valid_until"), "valid_until");
        $this->addColumn($lng->txt("user_account_code_generated"), "generated");
        $this->addColumn($lng->txt("user_account_code_used"), "used");

        $this->setExternalSorting(true);
        $this->setExternalSegmentation(true);
        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($this->parent_obj, "listCodes"));
        $this->setRowTemplate("tpl.code_list_row.html", "Services/User");
        $this->setEnableTitle(true);
        $this->initFilter();
        $this->setFilterCommand("applyCodesFilter");
        $this->setResetCommand("resetCodesFilter");
        $this->setDefaultOrderField("generated");
        $this->setDefaultOrderDirection("desc");

        $this->setSelectAllCheckbox("id[]");
        $this->setTopCommands(true);
        $this->addMultiCommand("deleteConfirmation", $lng->txt("delete"));

        $button = ilSubmitButton::getInstance();
        $button->setCaption("user_account_codes_export");
        $button->setCommand("exportCodes");
        $button->setOmitPreventDoubleSubmission(true);
        $this->addCommandButtonInstance($button);

        $this->getItems();
    }

    public function getItems(): void
    {
        global $DIC;

        $lng = $DIC['lng'];

        $this->determineOffsetAndOrder();

        $codes_data = ilAccountCode::getCodesData(
            ilUtil::stripSlashes($this->getOrderField()),
            ilUtil::stripSlashes($this->getOrderDirection()),
            ilUtil::stripSlashes($this->getOffset()),
            ilUtil::stripSlashes($this->getLimit()),
            $this->filter["code"],
            $this->filter["valid_until"],
            $this->filter["generated"]
        );

        if (count($codes_data["set"]) == 0 && $this->getOffset() > 0) {
            $this->resetOffset();
            $codes_data = ilAccountCode::getCodesData(
                ilUtil::stripSlashes($this->getOrderField()),
                ilUtil::stripSlashes($this->getOrderDirection()),
                ilUtil::stripSlashes($this->getOffset()),
                ilUtil::stripSlashes($this->getLimit()),
                $this->filter["code"],
                $this->filter["valid_until"],
                $this->filter["generated"]
            );
        }

        $result = array();
        foreach ($codes_data["set"] as $k => $code) {
            $result[$k]["generated"] = ilDatePresentation::formatDate(new ilDateTime($code["generated"], IL_CAL_UNIX));

            if ($code["used"]) {
                $result[$k]["used"] = ilDatePresentation::formatDate(new ilDateTime($code["used"], IL_CAL_UNIX));
            } else {
                $result[$k]["used"] = "";
            }

            if ($code["valid_until"] === "0") {
                $valid = $lng->txt("user_account_code_valid_until_unlimited");
            } elseif (is_numeric($code["valid_until"])) {
                $valid = $code["valid_until"] . " " . ($code["valid_until"] == 1 ? $lng->txt("day") : $lng->txt("days"));
            } else {
                $valid = ilDatePresentation::formatDate(new ilDate($code["valid_until"], IL_CAL_DATE));
            }
            $result[$k]["valid_until"] = $valid;

            $result[$k]["code"] = $code["code"];
            $result[$k]["code_id"] = $code["code_id"];
        }

        $this->setMaxCount($codes_data["cnt"]);
        $this->setData($result);
    }

    public function initFilter(): void
    {
        global $DIC;

        $lng = $DIC['lng'];

        // code
        $ti = new ilTextInputGUI($lng->txt("user_account_code"), "query");
        $ti->setMaxLength(ilAccountCode::CODE_LENGTH);
        $ti->setSize(20);
        $ti->setSubmitFormOnEnter(true);
        $this->addFilterItem($ti);
        $ti->readFromSession();
        $this->filter["code"] = $ti->getValue();

        // generated
        $options = array("" => $lng->txt("user_account_code_generated_all"));
        foreach (ilAccountCode::getGenerationDates() as $date) {
            $options[$date] = ilDatePresentation::formatDate(new ilDateTime($date, IL_CAL_UNIX));
        }
        $si = new ilSelectInputGUI($lng->txt("user_account_code_generated"), "generated");
        $si->setOptions($options);
        $this->addFilterItem($si);
        $si->readFromSession();
        $this->filter["generated"] = $si->getValue();
    }

    /**
     * @param array<string,string> $a_set
     */
    protected function fillRow(array $a_set): void
    {
        $this->tpl->setVariable("ID", $a_set["code_id"]);
        $this->tpl->setVariable("VAL_CODE", $a_set["code"]);
        $this->tpl->setVariable("VAL_VALID_UNTIL", $a_set["valid_until"]);
        $this->tpl->setVariable("VAL_GENERATED", $a_set["generated"]);
        $this->tpl->setVariable("VAL_USED", $a_set["used"]);
    }
}
