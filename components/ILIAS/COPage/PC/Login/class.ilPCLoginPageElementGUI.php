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
 * Class ilLoginPageElementGUI
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @ilCtrl_Calls ilPCLoginPageElementGUI:
 */
class ilPCLoginPageElementGUI extends ilPageContentGUI
{
    protected ilObjectDefinition $obj_definition;

    public function __construct(
        ilPageObject $a_pg_obj,
        ?ilPageContent $a_content_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC["tpl"];
        $this->lng = $DIC->language();
        $this->obj_definition = $DIC["objDefinition"];
        parent::__construct($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id);

        if (!is_object($this->content_obj)) {
            $this->content_obj = new ilPCLoginPageElement($this->getPage());
        }
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

    public function insert(): void
    {
        $this->edit(true);
    }

    public function edit(bool $a_insert = false): void
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;

        $this->displayValidationError();

        // edit form
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        if ($a_insert) {
            $form->setTitle($this->lng->txt("cont_insert_login_page"));
        } else {
            $form->setTitle($this->lng->txt("cont_update_login_page"));
        }

        // type selection
        $type_prop = new ilRadioGroupInputGUI($this->lng->txt("cont_type"), "type");

        foreach (ilPCLoginPageElement::getAllTypes() as $index => $lang_key) {
            $types[$index] = $this->lng->txt('cont_lpe_' . $lang_key);

            $option = new ilRadioOption($this->lng->txt('cont_lpe_' . $lang_key), $index);
            $type_prop->addOption($option);
        }

        $selected = $a_insert
            ? ""
            : $this->content_obj->getLoginPageElementType();
        $type_prop->setValue($selected);
        $form->addItem($type_prop);

        // horizonal align
        $align_prop = new ilSelectInputGUI($this->lng->txt("cont_align"), "horizontal_align");
        $options = array(
            "Left" => $lng->txt("cont_left"),
            "Center" => $lng->txt("cont_center"),
            "Right" => $lng->txt("cont_right"));
        #			"LeftFloat" => $lng->txt("cont_left_float"),
        #			"RightFloat" => $lng->txt("cont_right_float"));
        $align_prop->setOptions($options);
        $align_prop->setValue($this->content_obj->getAlignment());
        $form->addItem($align_prop);


        // save/cancel buttons
        if ($a_insert) {
            $form->addCommandButton("create_login_page_element", $lng->txt("save"));
            $form->addCommandButton("cancelCreate", $lng->txt("cancel"));
        } else {
            $form->addCommandButton("update_login_page_element", $lng->txt("save"));
            $form->addCommandButton("cancelUpdate", $lng->txt("cancel"));
        }
        $html = $form->getHTML();
        $tpl->setContent($html);
    }


    /**
     * Create new Login Page Element
     */
    public function create(): void
    {
        $this->content_obj = new ilPCLoginPageElement($this->getPage());
        $this->content_obj->create($this->pg_obj, $this->hier_id, $this->pc_id);
        $this->content_obj->setLoginPageElementType(
            $this->request->getString("type")
        );
        $this->content_obj->setAlignment(
            $this->request->getString("horizontal_align")
        );

        $this->updated = $this->pg_obj->update();
        if ($this->updated === true) {
            $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
        } else {
            $this->insert();
        }
    }

    /**
     * Update Login page element
     */
    public function update(): void
    {
        $this->content_obj->setLoginPageElementType(
            $this->request->getString("type")
        );
        $this->content_obj->setAlignment(
            $this->request->getString("horizontal_align")
        );
        $this->updated = $this->pg_obj->update();
        if ($this->updated === true) {
            $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
        } else {
            $this->pg_obj->addHierIDs();
            $this->edit();
        }
    }
}
