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
 * Class ilPCListGUI
 *
 * User Interface for LM List Editing
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCListGUI extends ilPageContentGUI
{
    protected ilPropertyFormGUI $form;

    public function __construct(
        ilPageObject $a_pg_obj,
        ?ilPageContent $a_content_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ) {
        global $DIC;

        $this->tpl = $DIC["tpl"];
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        parent::__construct($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id);
    }

    public function executeCommand(): void
    {
        // get next class that processes or forwards current command
        $next_class = $this->ctrl->getNextClass($this);

        // get current command
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            default:
                $this->$cmd();
                break;
        }
    }


    /**
     * insert new list form
     */
    public function insert(): void
    {
        $this->displayValidationError();
        $this->initListForm("create");
        $this->tpl->setContent($this->form->getHTML());
    }


    /**
     * Save list
     */
    public function create(): void
    {
        $tpl = $this->tpl;

        $this->initListForm("create");
        if ($this->form->checkInput()) {
            $this->content_obj = new ilPCList($this->getPage());
            $this->content_obj->create($this->pg_obj, $this->hier_id, $this->pc_id);
            $this->content_obj->addItems($this->form->getInput("nr_items"));
            $this->content_obj->setStartValue(
                (int) $this->form->getInput("start_value")
            );
            $list_type = $this->form->getInput("list_type");
            $this->content_obj->setListType(
                $list_type
            );
            if ($list_type == "Unordered") {
                $this->content_obj->setNumberingType("");
                $this->content_obj->setStyleClass(
                    $this->form->getInput("bullet_style")
                );
            } else {
                $this->content_obj->setNumberingType(
                    $this->form->getInput("numbering_type")
                );
                $this->content_obj->setStyleClass(
                    $this->form->getInput("number_style")
                );
            }
            $this->updated = $this->pg_obj->update();
            if ($this->updated === true) {
                $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
            }
        }
        $this->form->setValuesByPost();
        $tpl->setContent($this->form->getHTML());
    }

    /**
     * edit properties form
     */
    public function edit(): void
    {
        $this->displayValidationError();
        $this->initListForm("edit");
        $this->getValues();
        $this->tpl->setContent($this->form->getHTML());
    }

    /**
     * Save properties
     */
    public function saveProperties(): void
    {
        $tpl = $this->tpl;

        $this->initListForm("edit");
        if ($this->form->checkInput()) {
            $this->content_obj->setStartValue(
                $this->form->getInput("start_value")
            );
            $list_type = $this->form->getInput("list_type");
            $this->content_obj->setListType(
                $list_type
            );
            if ($list_type == "Unordered") {
                $this->content_obj->setNumberingType("");
                $this->content_obj->setStyleClass(
                    $this->form->getInput("bullet_style")
                );
            } else {
                $this->content_obj->setNumberingType(
                    $this->form->getInput("numbering_type")
                );
                $this->content_obj->setStyleClass(
                    $this->form->getInput("number_style")
                );
            }

            $this->updated = $this->pg_obj->update();
            if ($this->updated === true) {
                $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
            }
        }
        $this->form->setValuesByPost();
        $tpl->setContent($this->form->getHTML());
    }

    public function initListForm(
        string $a_mode = "edit"
    ): void {
        $lng = $this->lng;
        $this->form = new ilPropertyFormGUI();

        // type
        $radg = new ilRadioGroupInputGUI($lng->txt("type"), "list_type");
        $op1 = new ilRadioOption($lng->txt("cont_bullet_list"), "Unordered");

        // style of bullet list
        $style = new ilAdvSelectInputGUI(
            $this->lng->txt("cont_style"),
            "bullet_style"
        );
        $this->getCharacteristicsOfCurrentStyle(["list_u"]);
        $options = $this->getCharacteristics();
        if ($a_mode == "edit" && $this->content_obj->getListType() == "Unordered"
                && $this->content_obj->getStyleClass() != ""
                && !in_array($this->content_obj->getStyleClass(), $options)) {
            $options[$this->content_obj->getStyleClass()] =
                    $this->content_obj->getStyleClass();
        }
        if (count($options) > 1) {
            foreach ($options as $k => $option) {
                $html = '<ul class="ilc_list_u_' . $k . '"><li class="ilc_list_item_StandardListItem">' .
                        $option . '</li></ul>';
                if ($k == "BulletedList") {
                    $k = "";
                }
                $style->addOption($k, $option, $html);
            }
            $style->setValue("");
            $op1->addSubItem($style);
        }

        $radg->addOption($op1);


        $op2 = new ilRadioOption($lng->txt("cont_numbered_list"), "Ordered");

        // style of numbered list
        $style = new ilAdvSelectInputGUI(
            $this->lng->txt("cont_style"),
            "number_style"
        );
        $this->getCharacteristicsOfCurrentStyle(["list_o"]);
        $options = $this->getCharacteristics();
        if ($a_mode == "edit" && $this->content_obj->getListType() == "Ordered"
                && $this->content_obj->getStyleClass() != ""
                && !in_array($this->content_obj->getStyleClass(), $options)) {
            $options[$this->content_obj->getStyleClass()] =
                    $this->content_obj->getStyleClass();
        }
        if (count($options) > 1) {
            foreach ($options as $k => $option) {
                $html = '<ol class="ilc_list_o_' . $k . '"><li class="ilc_list_item_StandardListItem">' .
                        $option . '</li></ol>';
                if ($k == "NumberedList") {
                    $k = "";
                }
                $style->addOption($k, $option, $html);
            }
            $style->setValue("");
            $op2->addSubItem($style);
        }

        // numeric type
        $options = array(
                "Number" => $this->lng->txt("cont_number_std"),
                "Decimal" => $this->lng->txt("cont_decimal"),
                "Roman" => $this->lng->txt("cont_roman"),
                "roman" => $this->lng->txt("cont_roman_s"),
                "Alphabetic" => $this->lng->txt("cont_alphabetic"),
                "alphabetic" => $this->lng->txt("cont_alphabetic_s")
                );
        $si = new ilSelectInputGUI($this->lng->txt("cont_number_type"), "numbering_type");
        $si->setOptions($options);
        $op2->addSubItem($si);

        // starting value
        $ni = new ilNumberInputGUI($this->lng->txt("cont_start_value"), "start_value");
        $ni->setMaxLength(3);
        $ni->setSize(3);
        $ni->setInfo($lng->txt("cont_start_value_info"));
        $op2->addSubItem($ni);

        $radg->addOption($op2);
        $radg->setValue("Unordered");
        $this->form->addItem($radg);

        // nr of items
        $options = array();
        if ($a_mode == "create") {
            for ($i = 1; $i <= 10; $i++) {
                $options[$i] = $i;
            }
            $si = new ilSelectInputGUI($this->lng->txt("cont_nr_items"), "nr_items");
            $si->setOptions($options);
            $si->setValue(2);
            $this->form->addItem($si);
        }

        // save and cancel commands
        if ($a_mode == "create") {
            $this->form->addCommandButton("create_list", $lng->txt("save"));
            $this->form->addCommandButton("cancelCreate", $lng->txt("cancel"));
            $this->form->setTitle($lng->txt("cont_insert_list"));
        } else {
            $this->form->addCommandButton("saveProperties", $lng->txt("save"));
            $this->form->addCommandButton("cancelUpdate", $lng->txt("cancel"));
            $this->form->setTitle($lng->txt("cont_list_properties"));
        }

        $this->form->setFormAction($this->ctrl->getFormAction($this));
    }

    public function getValues(): void
    {
        $values = array();

        $values["start_value"] = $this->content_obj->getStartValue();
        $values["list_type"] = $this->content_obj->getListType();
        $values["numbering_type"] = $this->content_obj->getNumberingType();
        if ($values["list_type"] == "Ordered") {
            $values["number_style"] = $this->content_obj->getStyleClass();
            $values["bullet_style"] = "";
        } else {
            $values["bullet_style"] = $this->content_obj->getStyleClass();
            $values["number_style"] = "";
        }
        $this->form->setValuesByArray($values);
    }
}
