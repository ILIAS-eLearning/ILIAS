<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Table/classes/class.ilTable2GUI.php");

/**
 * TableGUI class for badge template listing
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ServicesBadge
 */
class ilBadgeImageTemplateTableGUI extends ilTable2GUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    protected $has_write; // [bool]
    
    public function __construct($a_parent_obj, $a_parent_cmd = "", $a_has_write = false)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        
        $this->setId("bdgtmpl");
        $this->has_write = (bool) $a_has_write;
                
        parent::__construct($a_parent_obj, $a_parent_cmd);
            
        $this->setLimit(9999);
        
        $this->setTitle($lng->txt("badge_image_templates"));
                        
        if ($this->has_write) {
            $this->addColumn("", "", 1);
        }
        
        $this->addColumn($lng->txt("title"), "title");
        $this->addColumn($lng->txt("image"), "image");
                
        if ($this->has_write) {
            $this->addColumn($lng->txt("action"), "");
            $this->addMultiCommand("confirmDeleteImageTemplates", $lng->txt("delete"));
        }
        
        $this->setSelectAllCheckbox("id");
            
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.template_row.html", "Services/Badge");
        $this->setDefaultOrderField("title");
                                
        $this->getItems();
    }
    
    public function getItems()
    {
        $data = array();
        
        include_once "Services/Badge/classes/class.ilBadgeImageTemplate.php";
        foreach (ilBadgeImageTemplate::getInstances() as $template) {
            $data[] = array(
                "id" => $template->getId(),
                "title" => $template->getTitle(),
                "path" => $template->getImagePath(),
                "file" => $template->getImage()
            );
        }
        
        $this->setData($data);
    }
    
    protected function fillRow($a_set)
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
        if ($this->has_write) {
            $this->tpl->setVariable("VAL_ID", $a_set["id"]);
        }
        
        $this->tpl->setVariable("TXT_TITLE", $a_set["title"]);
        $this->tpl->setVariable("VAL_IMG", $a_set["path"]);
        $this->tpl->setVariable("TXT_IMG", $a_set["file"]);
        
        if ($this->has_write) {
            $ilCtrl->setParameter($this->getParentObject(), "tid", $a_set["id"]);
            $url = $ilCtrl->getLinkTarget($this->getParentObject(), "editImageTemplate");
            $ilCtrl->setParameter($this->getParentObject(), "tid", "");
            
            $this->tpl->setVariable("TXT_EDIT", $lng->txt("edit"));
            $this->tpl->setVariable("URL_EDIT", $url);
        }
    }
}
