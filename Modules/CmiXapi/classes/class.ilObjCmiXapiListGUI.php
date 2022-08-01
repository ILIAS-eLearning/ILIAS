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
 * Class ilObjCmiXapiListGUI
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/CmiXapi
 */
class ilObjCmiXapiListGUI extends ilObjectListGUI
{
    public function init() : void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $this->static_link_enabled = true; // Create static links for default command (linked title) or not
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->subscribe_enabled = false;
        $this->link_enabled = true;
        $this->copy_enabled = true;
        $this->progress_enabled = true;
        $this->notice_properties_enabled = true;
        $this->info_screen_enabled = true;
        $this->type = "cmix";
        $this->gui_class_name = "ilObjCmiXapiGUI";

        $this->commands = ilObjCmiXapiAccess::_getCommands();
        
        $DIC->language()->loadLanguageModule('cmix');
    }
    
    /**
     * @return array<int, array<string, mixed>>
     */
    public function getProperties() : array
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $props = array();
        
        if (ilObjCmiXapiAccess::_isOffline($this->obj_id)) {
            $props[] = array("alert" => true, "property" => $DIC->language()->txt("status"),
                "value" => $DIC->language()->txt("offline"));
        }
        
        $props[] = array(
            'alert' => false, 'property' => $DIC->language()->txt('type'),
            'value' => $DIC->language()->txt('obj_cmix')
        );

        $validator = new ilCertificateDownloadValidator();
        if ($validator->isCertificateDownloadable((int) $DIC->user()->getId(), $this->obj_id)) {
            $DIC->ctrl()->setParameterByClass(ilCmiXapiSettingsGUI::class, 'ref_id', $this->ref_id);
            
            $certLink = $DIC->ui()->factory()->link()->standard(
                $DIC->language()->txt('download_certificate'),
                $DIC->ctrl()->getLinkTargetByClass(
                    [ilObjCmiXapiGUI::class, ilCmiXapiSettingsGUI::class],
                    ilCmiXapiSettingsGUI::CMD_DELIVER_CERTIFICATE
                )
            );
            
            $props[] = array(
                'alert' => false, 'property' => $DIC->language()->txt('certificate'),
                'value' => $DIC->ui()->renderer()->render($certLink)
            );
        }
        
        return $props;
    }

    public function getCommandLink(string $cmd) : string
    {
        global $ilCtrl;
        
        $cmd = explode('::', $cmd);
        
        if (count($cmd) == 2) {
            $cmd_link = $ilCtrl->getLinkTargetByClass(array('ilRepositoryGUI', 'ilObjCmiXapiGUI', $cmd[0]), $cmd[1]);
        } else {
            $cmd_link = $ilCtrl->getLinkTargetByClass(array('ilRepositoryGUI', 'ilObjCmiXapiGUI'), $cmd[0]);
        }
        
        return $cmd_link;
    }
}
