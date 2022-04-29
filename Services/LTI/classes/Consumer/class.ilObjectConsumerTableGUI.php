<?php declare(strict_types=1);
/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
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
    protected bool $editable = true;
    protected \ILIAS\DI\Container $dic;
    
    public function __construct(?object $a_parent_obj, string $a_parent_cmd)
    {
        global $DIC;
        $this->dic = $DIC;

        $this->setId("ltioconsumer");

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setLimit(9999);

        $this->setTitle($DIC->language()->txt("lti_object_consumer"));

        $this->addColumn($DIC->language()->txt("active"), "active");
        $this->addColumn($DIC->language()->txt("title"), "title");
        $this->addColumn($DIC->language()->txt("description"), "description");
        $this->addColumn($DIC->language()->txt("prefix"), "prefix");
        $this->addColumn($DIC->language()->txt("in_use"), "language");
        $this->addColumn($DIC->language()->txt("objects"), "objects");
        $this->addColumn($DIC->language()->txt("role"), "role");
        $this->addColumn($DIC->language()->txt("actions"), "");

        $this->setFormAction($DIC->ctrl()->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.lti_consumer_list_row.html", "Services/LTI");
        $this->setDefaultOrderField("title");

        $this->getItems();
    }
    
    /**
     * Set editable. Depends on write access
     * => show/hide actions for consumers.
     */
    public function setEditable(bool $a_status) : void
    {
        $this->editable = $a_status;
    }
    
    /**
     * Check if write permission given
     */
    public function isEditable() : bool
    {
        return $this->editable;
    }

    /**
     * Get consumer data
     */
    public function getItems() : void
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
    protected function fillRow(array $a_set) : void
    {
        $this->dic->ctrl()->setParameter($this->getParentObject(), "cid", $a_set["id"]);

        $this->tpl->setVariable("TXT_TITLE", $a_set["title"]);
        $this->tpl->setVariable("TXT_DESCRIPTION", $a_set["description"]);
        $this->tpl->setVariable("TXT_PREFIX", $a_set["prefix"]);
        $this->tpl->setVariable("TXT_KEY", $a_set["key"]);
        $this->tpl->setVariable("TXT_SECRET", $a_set["secret"]);
        $this->tpl->setVariable("TXT_LANGUAGE", $a_set["language"]);
        $obj_types = ilObjLTIAdministration::getActiveObjectTypes($a_set["id"]);
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
            $this->tpl->setVariable("TXT_ACTIVE", $this->dic->language()->txt('active'));
            $label_status = $this->dic->language()->txt("deactivate");
        } else {
            $this->tpl->setVariable("TXT_ACTIVE", $this->dic->language()->txt('inactive'));
            $label_status = $this->dic->language()->txt("activate");
        }
        
        if ($this->isEditable()) {
            $list = new ilAdvancedSelectionListGUI();
            $list->setId((string) $a_set["id"]);
            $list->setListTitle($this->dic->language()->txt("actions"));

            $edit_url = $this->dic->ctrl()->getLinkTarget($this->getParentObject(), "editConsumer");
            $delete_url = $this->dic->ctrl()->getLinkTarget($this->getParentObject(), "deleteLTIConsumer");
            $status_url = $this->dic->ctrl()->getLinkTarget($this->getParentObject(), "changeStatusLTIConsumer");
            $list->addItem($this->dic->language()->txt("edit"), "", $edit_url);
            $list->addItem($this->dic->language()->txt("delete"), "", $delete_url);
            $list->addItem($label_status, "", $status_url);

            $this->tpl->setVariable("ACTION", $list->getHTML());
        }
    }
}
