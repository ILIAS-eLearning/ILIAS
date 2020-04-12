<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilObjLTIConsumer
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Modules/LTIConsumer
 */
class ilObjLTIConsumerListGUI extends ilObjectListGUI
{
    public function init()
    {
        $this->static_link_enabled = true; // Create static links for default command (linked title) or not
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->subscribe_enabled = false;
        $this->link_enabled = true;
        $this->copy_enabled = true;
        $this->progress_enabled = true;
        $this->notice_properties_enabled = true;
        $this->info_screen_enabled = true;
        $this->type = "lti";
        $this->gui_class_name = "ilObjLTIConsumerGUI";

        $this->commands = ilObjLTIConsumerAccess::_getCommands();
    }
    /**
     * Insert icons and checkboxes
     */
    public function insertIconsAndCheckboxes()
    {
        $lng = $this->lng;
        $objDefinition = $this->obj_definition;
        
        $cnt = 0;
        if ($this->getCheckboxStatus()) {
            $this->tpl->setCurrentBlock("check");
            $this->tpl->setVariable("VAL_ID", $this->getCommandId());
            $this->tpl->parseCurrentBlock();
            $cnt += 1;
        } elseif ($this->getDownloadCheckboxState() != self::DOWNLOAD_CHECKBOX_NONE) {
            $this->tpl->setCurrentBlock("check_download");
            if ($this->getDownloadCheckboxState() == self::DOWNLOAD_CHECKBOX_ENABLED) {
                $this->tpl->setVariable("VAL_ID", $this->getCommandId());
            } else {
                $this->tpl->setVariable("VAL_VISIBILITY", "visibility: hidden;\" disabled=\"disabled");
            }
            $this->tpl->parseCurrentBlock();
            $cnt += 1;
        } elseif ($this->getExpandStatus()) {
            $this->tpl->setCurrentBlock('expand');
            
            if ($this->isExpanded()) {
                $this->ctrl->setParameter($this->container_obj, 'expand', -1 * $this->obj_id);
                // "view" added, see #19922
                $this->tpl->setVariable('EXP_HREF', $this->ctrl->getLinkTarget($this->container_obj, 'view', $this->getUniqueItemId(true)));
                $this->ctrl->clearParameters($this->container_obj);
                $this->tpl->setVariable('EXP_IMG', ilUtil::getImagePath('tree_exp.svg'));
                $this->tpl->setVariable('EXP_ALT', $this->lng->txt('collapse'));
            } else {
                $this->ctrl->setParameter($this->container_obj, 'expand', $this->obj_id);
                // "view" added, see #19922
                $this->tpl->setVariable('EXP_HREF', $this->ctrl->getLinkTarget($this->container_obj, 'view', $this->getUniqueItemId(true)));
                $this->ctrl->clearParameters($this->container_obj);
                $this->tpl->setVariable('EXP_IMG', ilUtil::getImagePath('tree_col.svg'));
                $this->tpl->setVariable('EXP_ALT', $this->lng->txt('expand'));
            }
            
            $this->tpl->parseCurrentBlock();
            $cnt += 1;
        }
        
        if ($this->getIconStatus()) {
            if ($cnt == 1) {
                $this->tpl->touchBlock("i_1");	// indent
            }
            
            // icon link
            if ($this->title_link_disabled || !$this->default_command || (!$this->getCommandsStatus() && !$this->restrict_to_goto)) {
            } else {
                $this->tpl->setCurrentBlock("icon_link_s");
                
                if ($this->default_command["frame"] != "") {
                    $this->tpl->setVariable("ICON_TAR", "target='" . $this->default_command["frame"] . "'");
                }
                
                $this->tpl->setVariable(
                    "ICON_HREF",
                    $this->default_command["link"]
                );
                $this->tpl->parseCurrentBlock();
                $this->tpl->touchBlock("icon_link_e");
            }
            
            $this->tpl->setCurrentBlock("icon");
            if (!$objDefinition->isPlugin($this->getIconImageType())) {
                $this->tpl->setVariable("ALT_ICON", $lng->txt("icon") . " " . $lng->txt("obj_" . $this->getIconImageType()));
            } else {
                include_once("Services/Component/classes/class.ilPlugin.php");
                $this->tpl->setVariable("ALT_ICON", $lng->txt("icon") . " " .
                    ilObjectPlugin::lookupTxtById($this->getIconImageType(), "obj_" . $this->getIconImageType()));
            }
            
            $this->tpl->setVariable("SRC_ICON", $this->getIconHref());
            $this->tpl->parseCurrentBlock();
            $cnt += 1;
        }
        
        $this->tpl->touchBlock("d_" . $cnt);	// indent main div
    }
    
    protected function getIconHref()
    {
        /* @var ilObjLTIConsumer $object */
        $object = ilObjectFactory::getInstanceByObjId($this->obj_id);
        
        if ($object->getProvider()->hasProviderIcon()) {
            return $object->getProvider()->getProviderIcon()->getAbsoluteFilePath();
        }
        
        return ilObject::_getIcon($this->obj_id, "small", $this->getIconImageType());
    }
    
    public function getProperties()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $props = array();
        
        if (ilObjLTIConsumerAccess::_isOffline($this->obj_id)) {
            $props[] = array("alert" => true, "property" => $DIC->language()->txt("status"),
                "value" => $DIC->language()->txt("offline"));
        }
        
        $props[] = array(
            'alert' => false, 'property' => $DIC->language()->txt('type'),
            'value' => $DIC->language()->txt('obj_lti')
        );

        $validator = new ilCertificateDownloadValidator();
        if ($validator->isCertificateDownloadable((int) $DIC->user()->getId(), (int) $this->obj_id)) {
            $DIC->ctrl()->setParameterByClass(ilLTIConsumerSettingsGUI::class, 'ref_id', $this->ref_id);
            
            $certLink = $DIC->ui()->factory()->link()->standard(
                $DIC->language()->txt('download_certificate'),
                $DIC->ctrl()->getLinkTargetByClass(
                    [ilObjLTIConsumerGUI::class, ilLTIConsumerSettingsGUI::class],
                    ilLTIConsumerSettingsGUI::CMD_DELIVER_CERTIFICATE
                )
            );
            
            $props[] = array(
                'alert' => false, 'property' => $DIC->language()->txt('certificate'),
                'value' => $DIC->ui()->renderer()->render($certLink)
            );
        }

        return $props;
    }
    
    public function getCommandLink($a_cmd)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $a_cmd = explode('::', $a_cmd);
        
        if (count($a_cmd) == 2) {
            $cmd_link = $DIC->ctrl()->getLinkTargetByClass(array('ilRepositoryGUI', 'ilObjLTIConsumerGUI', $a_cmd[0]), $a_cmd[1]);
        } else {
            $cmd_link = $DIC->ctrl()->getLinkTargetByClass(array('ilRepositoryGUI', 'ilObjLTIConsumerGUI'), $a_cmd[0]);
        }
        
        return $cmd_link;
    }
}
