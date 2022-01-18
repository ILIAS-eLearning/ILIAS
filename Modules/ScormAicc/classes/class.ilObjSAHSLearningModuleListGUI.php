<?php

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
 * Class ilObjSAHSLearningModuleListGUI
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * $Id$
 *
 * @ingroup ModulesScormAicc
 */
class ilObjSAHSLearningModuleListGUI extends ilObjectListGUI
{
    /**
    * constructor
    *
    */
    public function __construct()
    {
        parent::__construct();
    }

    /**
    * initialisation
    *
    * this method should be overwritten by derived classes
    */
    public function init() : void
    {
        $this->copy_enabled = true;
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->subscribe_enabled = true;
        $this->link_enabled = true;
        $this->info_screen_enabled = true;
        $this->type = "sahs";
        $this->gui_class_name = "ilobjsahslearningmodulegui";
    }

    /**
     * @inheritdoc
     */
    public function initItem($a_ref_id, $a_obj_id, $type, $a_title = "", $a_description = "") : void
    {
        // general commands array
        $this->commands = ilObjSAHSLearningModuleAccess::_getCommands($a_obj_id);
        parent::initItem($a_ref_id, $a_obj_id, $type, $a_title, $a_description);
    }

    /**
    * Overwrite this method, if link target is not build by ctrl class
    * (e.g. "lm_presentation.php", "forum.php"). This is the case
    * for all links now, but bringing everything to ilCtrl should
    * be realised in the future.
    *
    * @param	string		$a_cmd			command
    *
    */
    public function getCommandLink($a_cmd) : string
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $cmd_link = null;
        switch ($a_cmd) {
            case "view":
                $cmd_link = "ilias.php?baseClass=ilSAHSPresentationGUI&amp;ref_id=" . $this->ref_id;

                break;

//            case "editContent":
//                $cmd_link = "ilias.php?baseClass=ilSAHSEditGUI&amp;ref_id=" . $this->ref_id . "&amp;cmd=editContent";
//                break;

            case "edit":
                $cmd_link = "ilias.php?baseClass=ilSAHSEditGUI&amp;ref_id=" . $this->ref_id;
                break;

            case "infoScreen":
                $cmd_link = "ilias.php?baseClass=ilSAHSPresentationGUI&amp;ref_id=" . $this->ref_id . "&amp;cmd=infoScreen";
                break;

            default:
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $this->ref_id);
                $cmd_link = $ilCtrl->getLinkTargetByClass("ilrepositorygui", $a_cmd);
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $_GET["ref_id"]);
                break;
        }

        return $cmd_link;
    }


    /**
    * Get command target frame
    *
    * @param	string		$a_cmd			command
    *
    * @return	string		command target frame
    */
    public function getCommandFrame($a_cmd) : string
    {
        global $DIC;
        $ilias = $DIC['ilias'];

        switch ($a_cmd) {
            case "view":
                $sahs_obj = new ilObjSAHSLearningModule($this->ref_id);
                if ($this->offline_mode) {
                    $frame = ilFrameTargetInfo::_getFrame("MainContent");
                } else {
                    $frame = "ilContObj" . $this->obj_id;
                }
                if ($sahs_obj->getEditable() == 1) {
                    $frame = ilFrameTargetInfo::_getFrame("MainContent");
                }
                break;

            case "edit":
//            case "editContent":
            case "infoScreen":
                $frame = ilFrameTargetInfo::_getFrame("MainContent");
                break;

            default:
                $frame = "";
                break;
        }

        return $frame;
    }


    /**
    * Get item properties
    *
    * @return	array		array of property arrays:
    *						"alert" (boolean) => display as an alert property (usually in red)
    *						"property" (string) => property name
    *						"value" (string) => property value
    */
    public function getProperties() : array
    {
        global $DIC;
        $lng = $DIC['lng'];
        $rbacsystem = $DIC['rbacsystem'];
        $props = parent::getProperties();

        if ($rbacsystem->checkAccess("write", $this->ref_id)) {
            $props[] = array("alert" => false, "property" => $lng->txt("type"),
                "value" => $lng->txt("sahs"));
        }

        $certValidator = new ilCertificateDownloadValidator();
        $allowed = $certValidator->isCertificateDownloadable($this->user->getId(), $this->obj_id);
        if ($allowed) {
            $type = ilObjSAHSLearningModule::_lookupSubType($this->obj_id);
            $lng->loadLanguageModule('certificate');
            $cmd_link = "ilias.php?baseClass=ilSAHSPresentationGUI&ref_id=" . $this->ref_id . "&cmd=downloadCertificate";
            $props[] = [
                'alert' => false,
                'property' => $lng->txt('certificate'),
                'value' => $DIC->ui()->renderer()->render(
                    $DIC->ui()->factory()->link()->standard($lng->txt('download_certificate'), $cmd_link)
                )
            ];
        }

        return $props;
    }
} // END class.ilObjCategoryGUI
