<?php declare(strict_types=1);

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
 * Class ilObjLTIConsumer
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Modules/LTIConsumer
 */
class ilObjLTIConsumerListGUI extends ilObjectListGUI
{
    public function init() : void
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
    public function insertIconsAndCheckboxes() : void
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
                $this->tpl->setVariable("ALT_ICON", $lng->txt("icon") . " " .
                    ilObjectPlugin::lookupTxtById($this->getIconImageType(), "obj_" . $this->getIconImageType()));
            }

            $this->tpl->setVariable("SRC_ICON", $this->getIconHref());
            $this->tpl->parseCurrentBlock();
            $cnt += 1;
        }

        $this->tpl->touchBlock("d_" . $cnt);	// indent main div
    }
    
    protected function getIconHref() : string
    {
        /* @var ilObjLTIConsumer $object */
        $object = ilObjectFactory::getInstanceByObjId($this->obj_id);
        
        if ($object->getProvider()->hasProviderIcon()) {
            return $object->getProvider()->getProviderIcon()->getAbsoluteFilePath();
        }
        
        return ilObject::_getIcon($this->obj_id, "small", $this->getIconImageType());
    }

    /**
                 * @throws ilCtrlException
                 * @return array<int, array<string, mixed>>
                 */
    public function getProperties() : array
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
        if ($validator->isCertificateDownloadable($DIC->user()->getId(), $this->obj_id)) {
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

    /**
     * @throws ilCtrlException
     */
    public function getCommandLink(string $cmd) : string
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $commands = explode('::', $cmd);
        
        if (count($commands) == 2) {
            $cmd_link = $DIC->ctrl()->getLinkTargetByClass(
                [
                    'ilRepositoryGUI',
                    'ilObjLTIConsumerGUI',
                    $commands[0]
                ],
                $commands[1]
            );
        } else {
            $cmd_link = $DIC->ctrl()->getLinkTargetByClass(['ilRepositoryGUI', 'ilObjLTIConsumerGUI'], $commands[0]);
        }
        
        return $cmd_link;
    }
}
