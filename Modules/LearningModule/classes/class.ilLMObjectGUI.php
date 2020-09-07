<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* Class ilLMObject
*
* Base class for ilStructureObjects and ilPageObjects (see ILIAS DTD)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesIliasLearningModule
*/
class ilLMObjectGUI
{
    public $tpl;
    public $lng;
    public $obj;
    public $ctrl;
    public $content_object;
    public $actions;


    /**
    * constructor
    *
    * @param	object		$a_content_obj		content object
    */
    public function __construct(&$a_content_obj)
    {
        global $DIC;

        $tpl = $DIC["tpl"];
        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();

        $this->tpl = $tpl;
        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        $this->content_object = $a_content_obj;
    }


    /**
    * build action array
    *
    * @param	array		$a_actions		action array (key = action key,
    *										value = action language string)
    * @access	private
    */
    public function setActions($a_actions = "")
    {
        if (is_array($a_actions)) {
            foreach ($a_actions as $name => $lng) {
                $this->actions[$name] = array("name" => $name, "lng" => $lng);
            }
        } else {
            $this->actions = "";
        }
    }


    /**
    * get target frame for command (command is method name without "Object", e.g. "perm")
    * @param	string		$a_cmd			command
    * @param	string		$a_target_frame	default target frame (is returned, if no special
    *										target frame was set)
    * @access	public
    */
    public function getTargetFrame($a_cmd, $a_target_frame = "")
    {
        if ($this->target_frame[$a_cmd] != "") {
            return $this->target_frame[$a_cmd];
        } elseif (!empty($a_target_frame)) {
            return "target=\"" . $a_target_frame . "\"";
        } else {
            return;
        }
    }

    /**
    * structure / page object creation form
    */
    public function create()
    {
        $new_type = $_REQUEST["new_type"];

        $this->ctrl->setParameter($this, "new_type", $new_type);
        include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, "save"));
        $form->setTitle($this->lng->txt($new_type . "_new"));
        
        $title = new ilTextInputGUI($this->lng->txt("title"), "Fobject[title]");
        //$title->setRequired(true);
        $form->addItem($title);
        
        $desc = new ilTextAreaInputGUI($this->lng->txt("description"), "Fobject[desc]");
        $form->addItem($desc);
        
        $form->addCommandButton("save", $this->lng->txt($new_type . "_add"));
        $form->addCommandButton("cancel", $this->lng->txt("cancel"));
        
        $this->tpl->setContent($form->getHTML());
    }


    /**
    * put this object into content object tree
    */
    public function putInTree()
    {
        $tree = new ilTree($this->content_object->getId());
        $tree->setTableNames('lm_tree', 'lm_data');
        $tree->setTreeTablePK("lm_id");

        $parent_id = (!empty($_GET["obj_id"]))
            ? $_GET["obj_id"]
            : $tree->getRootId();

        if (!empty($_GET["target"])) {
            $target = $_GET["target"];
        } else {
            // determine last child of current type
            $childs = $tree->getChildsByType($parent_id, $this->obj->getType());
            if (count($childs) == 0) {
                $target = IL_FIRST_NODE;
            } else {
                $target = $childs[count($childs) - 1]["obj_id"];
            }
        }
        if (!$tree->isInTree($this->obj->getId())) {
            $tree->insertNode($this->obj->getId(), $parent_id, $target);
        }
    }


    /**
    * Confirm deletion screen (delete page or structure objects)
    */
    public function delete()
    {
        $this->setTabs();

        $cont_obj_gui = new ilObjContentObjectGUI(
            "",
            $this->content_object->getRefId(),
            true,
            false
        );
        $cont_obj_gui->delete($this->obj->getId());
    }


    /**
    * cancel deletion of page/structure objects
    */
    public function cancelDelete()
    {
        ilSession::clear("saved_post");
        $this->ctrl->redirect($this, $_GET["backcmd"]);
    }


    /**
    * page and structure object deletion
    */
    public function confirmedDelete()
    {
        $cont_obj_gui = new ilObjContentObjectGUI(
            "",
            $this->content_object->getRefId(),
            true,
            false
        );
        $cont_obj_gui->confirmedDelete($this->obj->getId());
        $this->ctrl->redirect($this, $_GET["backcmd"]);
    }


    /**
    * output a cell in object list
    */
    public function add_cell($val, $link = "")
    {
        if (!empty($link)) {
            $this->tpl->setCurrentBlock("begin_link");
            $this->tpl->setVariable("LINK_TARGET", $link);
            $this->tpl->parseCurrentBlock();
            $this->tpl->touchBlock("end_link");
        }

        $this->tpl->setCurrentBlock("text");
        $this->tpl->setVariable("TEXT_CONTENT", $val);
        $this->tpl->parseCurrentBlock();
        $this->tpl->setCurrentBlock("table_cell");
        $this->tpl->parseCurrentBlock();
    }


    /**
    * show possible action (form buttons)
    *
    * @access	public
    */
    public function showActions($a_actions)
    {
        foreach ($a_actions as $name => $lng) {
            $d[$name] = array("name" => $name, "lng" => $lng);
        }

        $notoperations = array();
        $operations = array();

        $operations = $d;

        if (count($operations) > 0) {
            foreach ($operations as $val) {
                $this->tpl->setCurrentBlock("operation_btn");
                $this->tpl->setVariable("BTN_NAME", $val["name"]);
                $this->tpl->setVariable("BTN_VALUE", $this->lng->txt($val["lng"]));
                $this->tpl->parseCurrentBlock();
            }

            $this->tpl->setCurrentBlock("operation");
            $this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.svg"));
            $this->tpl->parseCurrentBlock();
        }
    }

    /**
    * check the content object tree
    */
    public function checkTree()
    {
        $this->content_object->checkTree();
    }
}
