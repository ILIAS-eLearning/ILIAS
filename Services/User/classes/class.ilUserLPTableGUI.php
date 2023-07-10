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
 * Learning progress account list for user administration
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilUserLPTableGUI extends ilTable2GUI
{
    protected bool $lp_active = false;
    protected int $ref_id;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        int $a_ref_id
    ) {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];

        $this->ref_id = $a_ref_id;
        $this->setId("admusrlp");

        parent::__construct($a_parent_obj, $a_parent_cmd);
        // $this->setTitle($this->lng->txt("obj_usr"));

        $this->addColumn($this->lng->txt("login"), "login");
        $this->addColumn($this->lng->txt("firstname"), "firstname");
        $this->addColumn($this->lng->txt("lastname"), "lastname");
        $this->addColumn($this->lng->txt("online_time"), "online_time");
        $this->addColumn($this->lng->txt("last_login"), "last_login");
        $this->setExternalSorting(true);
        $this->setExternalSegmentation(true);

        $this->setFormAction($ilCtrl->getFormAction($this->parent_obj));

        $this->setRowTemplate("tpl.user_list_lp_row.html", "Services/User");

        $this->setDefaultOrderField("login");
        $this->setDefaultOrderDirection("asc");

        $this->setExportFormats(array(self::EXPORT_EXCEL));

        $this->getItems();
    }

    public function getItems(): void
    {
        $this->determineOffsetAndOrder();

        $usr_data = ilUserQuery::getUserListData(
            ilUtil::stripSlashes($this->getOrderField()),
            ilUtil::stripSlashes($this->getOrderDirection()),
            ilUtil::stripSlashes($this->getOffset()),
            ilUtil::stripSlashes($this->getLimit()),
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            array("online_time"),
            null,
            null
        );

        if (count($usr_data["set"]) == 0 && $this->getOffset() > 0) {
            $this->resetOffset();
            $usr_data = ilUserQuery::getUserListData(
                ilUtil::stripSlashes($this->getOrderField()),
                ilUtil::stripSlashes($this->getOrderDirection()),
                ilUtil::stripSlashes($this->getOffset()),
                ilUtil::stripSlashes($this->getLimit()),
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                array("online_time"),
                null,
                null
            );
        }

        $this->setMaxCount($usr_data["cnt"]);
        $this->setData($usr_data["set"]);

        $this->lp_active = ilObjUserTracking::_enabledLearningProgress();
    }

    /**
     * @param array<string,mixed> $a_set
     * @throws ilDateTimeException
     * @throws ilTemplateException
     */
    protected function fillRow(array $a_set): void
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];

        if ($this->lp_active) {
            $ilCtrl->setParameterByClass("illearningprogressgui", "ref_id", $this->ref_id);
            $ilCtrl->setParameterByClass("illearningprogressgui", "obj_id", $a_set["usr_id"]);
            $link = $ilCtrl->getLinkTargetByClass(array("ilobjusergui",'illearningprogressgui'), "");

            $this->tpl->setCurrentBlock("login_link");
            $this->tpl->setVariable("HREF_LOGIN", $link);
            $this->tpl->setVariable("VAL_LOGIN", $a_set["login"]);
        } else {
            $this->tpl->setCurrentBlock("login_plain");
            $this->tpl->setVariable("VAL_LOGIN_PLAIN", $a_set["login"]);
        }
        $this->tpl->parseCurrentBlock();

        $this->tpl->setVariable("VAL_FIRSTNAME", $a_set["firstname"]);
        $this->tpl->setVariable("VAL_LASTNAME", $a_set["lastname"]);
        $this->tpl->setVariable(
            "VAL_ONLINE_TIME",
            self::secondsToShortString($a_set["online_time"])
        );
        $this->tpl->setVariable(
            "VAL_LAST_LOGIN",
            ilDatePresentation::formatDate(new ilDateTime($a_set["last_login"], IL_CAL_DATETIME))
        );
    }

    protected function fillRowExcel(ilExcel $a_excel, int &$a_row, array $a_set): void // Missing array type.
    {
        $a_excel->setCell($a_row, 0, $a_set["login"]);
        $a_excel->setCell($a_row, 1, $a_set["firstname"]);
        $a_excel->setCell($a_row, 2, $a_set["lastname"]);
        $a_excel->setCell(
            $a_row,
            3,
            self::secondsToShortString($a_set["online_time"])
        );
        $a_excel->setCell($a_row, 4, new ilDateTime($a_set["last_login"], IL_CAL_DATETIME));
    }

    /**
     * converts seconds to string:
     * Long: 7 days 4 hour(s) ...
     */
    protected static function secondsToShortString(int $seconds): string
    {
        $seconds = $seconds ?: 0;
        $days = floor($seconds / 86400);
        $rest = $seconds % 86400;

        $hours = floor($rest / 3600);
        $rest %= 3600;

        $minutes = floor($rest / 60);
        $rest %= 60;

        return sprintf("%02d:%02d:%02d:%02d", $days, $hours, $minutes, $rest);
    }
}
