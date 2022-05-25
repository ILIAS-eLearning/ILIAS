<?php declare(strict_types=1);
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * TableGUI class for registration codes
 * @author       Alex Killing <alex.killing@gmx.de>
 * @version      $Id$
 * @ilCtrl_Calls ilRegistrationCodesTableGUI:
 * @ingroup      ServicesRegistration
 */
class ilRegistrationCodesTableGUI extends ilTable2GUI
{
    protected ilAccessHandler $access;
    protected ilRbacReview $rbacreview;

    public array $filter = [];
    /** @var array<int, string> */
    protected array $role_map = [];

    /**
     * Constructor
     */
    public function __construct(object $a_parent_obj, string $a_parent_cmd)
    {
        global $DIC;

        $this->access = $DIC->access();
        $this->rbacreview = $DIC->rbac()->review();
        $this->setId("registration_code");
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->addColumn("", "", "1", true);
        foreach ($this->getSelectedColumns() as $c => $caption) {
            if ($c === "role_local" || $c === "alimit") {
                $c = "";
            }
            $this->addColumn($this->lng->txt($caption), $c);
        }

        $this->setExternalSorting(true);
        $this->setExternalSegmentation(true);
        $this->setEnableHeader(true);
        $this->setFormAction($this->ctrl->getFormAction($this->parent_obj, "listCodes"));
        $this->setRowTemplate("tpl.code_list_row.html", "Services/Registration");
        $this->setEnableTitle(true);
        $this->initFilter();
        $this->setFilterCommand("applyCodesFilter");
        $this->setResetCommand("resetCodesFilter");
        $this->setDefaultOrderField("generated"); // #11341
        $this->setDefaultOrderDirection("desc");

        $this->setSelectAllCheckbox("id[]");
        $this->setTopCommands(true);

        if ($this->access->checkAccess("write", '', $a_parent_obj->ref_id)) {
            $this->addMultiCommand("deleteConfirmation", $this->lng->txt("delete"));
        }
        $this->addCommandButton("exportCodes", $this->lng->txt("registration_codes_export"));
        $this->getItems();
    }

    /**
     * Get user items
     */
    public function getItems() : void
    {
        $this->determineOffsetAndOrder();

        // #12737
        if (!array_key_exists($this->getOrderField(), $this->getSelectedColumns())) {
            $this->setOrderField($this->getDefaultOrderField());
        }

        $codes_data = ilRegistrationCode::getCodesData(
            $this->getOrderField(),
            $this->getOrderDirection(),
            $this->getOffset(),
            $this->getLimit(),
            (string) $this->filter["code"],
            (int) $this->filter["role"],
            (string) $this->filter["generated"],
            (string) $this->filter["alimit"]
        );

        if (count($codes_data["set"]) === 0 && $this->getOffset() > 0) {
            $this->resetOffset();
            $codes_data = ilRegistrationCode::getCodesData(
                $this->getOrderField(),
                $this->getOrderDirection(),
                $this->getOffset(),
                $this->getLimit(),
                (string) $this->filter["code"],
                (int) $this->filter["role"],
                (string) $this->filter["generated"],
                (string) $this->filter["alimit"]
            );
        }

        $options = [];
        $this->role_map = [];
        foreach ($this->rbacreview->getGlobalRoles() as $role_id) {
            if (!in_array($role_id, [SYSTEM_ROLE_ID, ANONYMOUS_ROLE_ID], true)) {
                $this->role_map[$role_id] = ilObject::_lookupTitle($role_id);
            }
        }

        $result = [];
        foreach ($codes_data["set"] as $k => $code) {
            $result[$k]["code"] = $code["code"];
            $result[$k]["code_id"] = $code["code_id"];

            $result[$k]["generated"] = ilDatePresentation::formatDate(new ilDateTime($code["generated"], IL_CAL_UNIX));

            if ($code["used"]) {
                $result[$k]["used"] = ilDatePresentation::formatDate(new ilDateTime($code["used"], IL_CAL_UNIX));
            }

            if ($code["role"]) {
                $result[$k]["role"] = $this->role_map[$code["role"]];
            }

            if ($code["role_local"]) {
                $local = [];
                foreach (explode(";", $code["role_local"]) as $role_id) {
                    $role = ilObject::_lookupTitle((int) $role_id);
                    if ($role) {
                        $local[] = $role;
                    }
                }
                if (count($local)) {
                    sort($local);
                    $result[$k]["role_local"] = implode("<br />", $local);
                }
            }

            if ($code["alimit"]) {
                switch ($code["alimit"]) {
                    case "unlimited":
                        $result[$k]["alimit"] = $this->lng->txt("reg_access_limitation_none");
                        break;

                    case "absolute":
                        $result[$k]["alimit"] = $this->lng->txt("reg_access_limitation_mode_absolute_target") .
                            ": " . ilDatePresentation::formatDate(new ilDate($code["alimitdt"], IL_CAL_DATE));
                        break;

                    case "relative":
                        $limit_caption = [];
                        $limit = unserialize($code["alimitdt"], ['allowed_classes' => false]);
                        if ((int) $limit["d"]) {
                            $limit_caption[] = (int) $limit["d"] . " " . $this->lng->txt("days");
                        }
                        if ((int) $limit["m"]) {
                            $limit_caption[] = (int) $limit["m"] . " " . $this->lng->txt("months");
                        }
                        if ((int) $limit["y"]) {
                            $limit_caption[] = (int) $limit["y"] . " " . $this->lng->txt("years");
                        }
                        if (count($limit_caption)) {
                            $result[$k]["alimit"] = $this->lng->txt("reg_access_limitation_mode_relative_target") .
                                ": " . implode(", ", $limit_caption);
                        }
                        break;
                }
            }
        }

        $this->setMaxCount((int) $codes_data["cnt"]);
        $this->setData($result);
    }

    /**
     * Init filter
     */
    public function initFilter() : void
    {
        // code
        $ti = new ilTextInputGUI($this->lng->txt("registration_code"), "query");
        $ti->setMaxLength(ilRegistrationCode::CODE_LENGTH);
        $ti->setSize(20);
        $ti->setSubmitFormOnEnter(true);
        $this->addFilterItem($ti);
        $ti->readFromSession();
        $this->filter["code"] = $ti->getValue();

        // role

        $this->role_map = [];
        foreach ($this->rbacreview->getGlobalRoles() as $role_id) {
            if (!in_array($role_id, [SYSTEM_ROLE_ID, ANONYMOUS_ROLE_ID], true)) {
                $this->role_map[$role_id] = ilObject::_lookupTitle($role_id);
            }
        }

        $options = ["" => $this->lng->txt("registration_roles_all")] +
            $this->role_map;
        $si = new ilSelectInputGUI($this->lng->txt("role"), "role");
        $si->setOptions($options);
        $this->addFilterItem($si);
        $si->readFromSession();
        $this->filter["role"] = $si->getValue();

        // access limitation
        $options = [
            "" => $this->lng->txt("registration_codes_access_limitation_all"),
            "unlimited" => $this->lng->txt("reg_access_limitation_none"),
            "absolute" => $this->lng->txt("reg_access_limitation_mode_absolute"),
            "relative" => $this->lng->txt("reg_access_limitation_mode_relative")
        ];
        $si = new ilSelectInputGUI($this->lng->txt("reg_access_limitations"), "alimit");
        $si->setOptions($options);
        $this->addFilterItem($si);
        $si->readFromSession();
        $this->filter["alimit"] = $si->getValue();

        // generated
        $options = ["" => $this->lng->txt("registration_generated_all")];
        foreach (ilRegistrationCode::getGenerationDates() as $date) {
            $options[$date] = ilDatePresentation::formatDate(new ilDateTime($date, IL_CAL_UNIX));
        }
        $si = new ilSelectInputGUI($this->lng->txt("registration_generated"), "generated");
        $si->setOptions($options);
        $this->addFilterItem($si);
        $si->readFromSession();
        $this->filter["generated"] = $si->getValue();
    }

    public function getSelectedColumns() : array
    {
        return [
            "code" => "registration_code",
            "role" => "registration_codes_roles",
            "role_local" => "registration_codes_roles_local",
            "alimit" => "reg_access_limitations",
            "generated" => "registration_generated",
            "used" => "registration_used"
        ];
    }

    protected function fillRow(array $a_set) : void
    {
        $this->tpl->setVariable("ID", $a_set["code_id"]);
        foreach (array_keys($this->getSelectedColumns()) as $c) {
            $this->tpl->setVariable("VAL_" . strtoupper($c), $a_set[$c]);
        }
    }
}
