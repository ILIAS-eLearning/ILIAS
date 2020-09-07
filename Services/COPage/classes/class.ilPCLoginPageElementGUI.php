<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/COPage/classes/class.ilPageContentGUI.php';
include_once './Services/COPage/classes/class.ilPCLoginPageElement.php';

/**
* Class ilLoginPageElementGUI
*
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @ilCtrl_Calls ilPCLoginPageElementGUI:
*
* @ingroup ServicesCOPage
*/
class ilPCLoginPageElementGUI extends ilPageContentGUI
{
    /**
     * @var ilObjectDefinition
     */
    protected $obj_definition;

    /**
    * Constructor
    * @access	public
    */
    public function __construct($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id = "")
    {
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

    /**
     * Get login page elements
     * @return ilPCLoginPageElement $lp_elements
     */
    public function getLoginPageElements()
    {
        return $this->lp_elements;
    }

    /**
    * execute command
    */
    public function executeCommand()
    {
        // get next class that processes or forwards current command
        $next_class = $this->ctrl->getNextClass($this);

        // get current command
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            default:
                $ret = $this->$cmd();
                break;
        }

        return $ret;
    }

    /**
    * Insert new resources component form.
    */
    public function insert()
    {
        $this->edit(true);
    }

    /**
    * Edit resources form.
    */
    public function edit($a_insert = false)
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;
        $objDefinition = $this->obj_definition;
        
        $this->displayValidationError();
        
        // edit form
        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
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
        return $ret;
    }


    /**
    * Create new Login Page Element
    */
    public function create()
    {
        $this->content_obj = new ilPCLoginPageElement($this->getPage());
        $this->content_obj->create($this->pg_obj, $this->hier_id, $this->pc_id);
        $this->content_obj->setLoginPageElementType($_POST["type"]);
        $this->content_obj->setAlignment($_POST['horizontal_align']);

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
    public function update()
    {
        $this->content_obj->setLoginPageElementType($_POST["type"]);
        $this->content_obj->setAlignment($_POST['horizontal_align']);
        $this->updated = $this->pg_obj->update();
        if ($this->updated === true) {
            $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
        } else {
            $this->pg_obj->addHierIDs();
            $this->edit();
        }
    }
}
