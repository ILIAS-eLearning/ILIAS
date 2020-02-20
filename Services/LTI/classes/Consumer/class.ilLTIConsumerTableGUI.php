<?php
/* Copyright (c) 1998-20016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * TableGUI class for LTI consumer listing
 *
 * @author Jesús López <lopez@leifos.com>
 * @version $Id$
 *
 * @ingroup ServicesLTI
 */
class ilObjectConsumerTableGUI extends ilTable2GUI
{
    protected $editable = true;
    
    public function __construct($a_parent_obj, $a_parent_cmd)
    {
        global $ilCtrl, $lng;

        $this->setId("ltioconsumer");

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setLimit(9999);

        $this->setTitle($lng->txt("lti_object_consumer"));

        $this->addColumn($lng->txt("active"), "active");
        $this->addColumn($lng->txt("title"), "title");
        $this->addColumn($lng->txt("description"), "description");
        $this->addColumn($lng->txt("prefix"), "prefix");
        $this->addColumn($lng->txt("in_use"), "language");
        $this->addColumn($lng->txt("objects"), "objects");
        $this->addColumn($lng->txt("role"), "role");
        $this->addColumn($lng->txt("actions"), "");

        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.lti_consumer_list_row.html", "Services/LTI");
        $this->setDefaultOrderField("title");

        $this->getItems();
    }
    
    /**
     * Set editable. Depends on write access
     * => show/hide actions for consumers.
     * @param bool $a_status
     */
    public function setEditable($a_status)
    {
        $this->editable = $a_status;
    }
    
    /**
     * Check if write permission given
     */
    public function isEditable()
    {
        return $this->editable;
    }

    /**
     * Get consumer data
     */
    public function getItems()
    {
        $dataConnector = new ilLTIDataConnector();
        
        $consumer_data = $dataConnector->getGlobalToolConsumerSettings();
        $result = array();
        foreach ($consumer_data as $cons) {
            $result[] = array(
                "id" => $cons->getExtConsumerId(),
                "title" => $cons->getTitle(),
                "description" => $cons->getDescription(),
                "prefix" => $cons->getPrefix(),
                "language" => $cons->getLanguage(),
                "role" => $cons->getRole(),
                "active" => $cons->getActive()
            );
        }

        $this->setData($result);
    }

    /**
     * Fill a single data row.
     */
    protected function fillRow($a_set)
    {
        global $lng, $ilCtrl;

        $ilCtrl->setParameter($this->getParentObject(), "cid", $a_set["id"]);

        $this->tpl->setVariable("TXT_TITLE", $a_set["title"]);
        $this->tpl->setVariable("TXT_DESCRIPTION", $a_set["description"]);
        $this->tpl->setVariable("TXT_PREFIX", $a_set["prefix"]);
        $this->tpl->setVariable("TXT_KEY", $a_set["key"]);
        $this->tpl->setVariable("TXT_SECRET", $a_set["secret"]);
        $this->tpl->setVariable("TXT_LANGUAGE", $a_set["language"]);
        $obj_types = $this->parent_obj->object->getActiveObjectTypes($a_set["id"]);
        if ($obj_types) {
            foreach ($obj_types as $obj_type) {
                $this->tpl->setCurrentBlock("objects");
                $this->tpl->setVariable("OBJECTS", $GLOBALS['DIC']->language()->txt('objs_' . $obj_type));
                $this->tpl->parseCurrentBlock();
            }
        } else {
            $this->tpl->setVariable("NO_OBJECTS", "-");
        }

        $role = ilObjectFactory::getInstanceByObjId($a_set['role'], false);
        if ($role instanceof ilObjRole) {
            $this->tpl->setVariable('TXT_ROLE', $role->getTitle());
        } else {
            $this->tpl->setVariable('TXT_ROLE', '');
        }

        if ($a_set["active"]) {
            $this->tpl->setVariable("TXT_ACTIVE", $lng->txt('active'));
            $label_status = $lng->txt("deactivate");
        } else {
            $this->tpl->setVariable("TXT_ACTIVE", $lng->txt('inactive'));
            $label_status = $lng->txt("activate");
        }
        
        if ($this->isEditable()) {
            $list = new ilAdvancedSelectionListGUI();
            $list->setId($a_set["id"]);
            $list->setListTitle($lng->txt("actions"));

            $edit_url = $ilCtrl->getLinkTarget($this->getParentObject(), "editConsumer");
            $delete_url = $ilCtrl->getLinkTarget($this->getParentObject(), "deleteLTIConsumer");
            $status_url = $ilCtrl->getLinkTarget($this->getParentObject(), "changeStatusLTIConsumer");
            $list->addItem($lng->txt("edit"), "", $edit_url);
            $list->addItem($lng->txt("delete"), "", $delete_url);
            $list->addItem($label_status, "", $status_url);

            $this->tpl->setVariable("ACTION", $list->getHTML());
        }
    }
}
