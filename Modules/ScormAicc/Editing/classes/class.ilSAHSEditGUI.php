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
* Class ilSAHSPresentationGUI
*
* GUI class for scorm learning module presentation
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id: class.ilSAHSPresentationGUI.php 11714 2006-07-30 17:15:55Z akill $
*
* @ilCtrl_Calls ilSAHSEditGUI: ilFileSystemGUI, ilObjectMetaDataGUI, ilObjSCORMLearningModuleGUI, ilInfoScreenGUI
* @ilCtrl_Calls ilSAHSEditGUI: ilObjSCORM2004LearningModuleGUI, ilExportGUI, ilObjSAHSLearningModuleGUI, ilLTIProviderObjectSettingGUI
*
* @ingroup ModulesScormAicc
*/
class ilSAHSEditGUI implements ilCtrlBaseClassInterface
{
    public $ilias;
    public $tpl;
    public $lng;

    public function __construct()
    {
        global $DIC;
        $ilias = $DIC['ilias'];
        $tpl = $DIC['tpl'];
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];

        $this->ilias = $ilias;
        $this->tpl = $tpl;
        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        
        $this->ctrl->saveParameter($this, "ref_id");
    }
    
    /**
    * execute command
    */
    public function executeCommand(): void
    {
        global $DIC;

        $DIC->globalScreen()->tool()->context()->claim()->repository();

        $lng = $DIC['lng'];
        $ilAccess = $DIC['ilAccess'];
        $ilNavigationHistory = $DIC['ilNavigationHistory'];
        $ilias = $DIC['ilias'];
        $ilCtrl = $DIC['ilCtrl'];
        $GLOBALS['DIC']["ilLog"]->write("bc:" . $_GET["baseClass"] . "; nc:" . $this->ctrl->getNextClass($this) . "; cmd:" . $this->ctrl->getCmd());

        $lng->loadLanguageModule("content");

        // permission
        if (!$ilAccess->checkAccess("write", "", $_GET["ref_id"])) {
            $this->ilias->raiseError($lng->txt("permission_denied"), $ilias->error_obj->MESSAGE);
        }
        
        // add entry to navigation history
        $ilNavigationHistory->addItem(
            $_GET["ref_id"],
            "ilias.php?baseClass=ilSAHSEditGUI&ref_id=" . $_GET["ref_id"],
            "lm"
        );

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $obj_id = ilObject::_lookupObjectId($_GET['ref_id']);
        $type = ilObjSAHSLearningModule::_lookupSubType($obj_id);

        switch ($type) {
            
            case "scorm2004":
                $this->slm_gui = new ilObjSCORM2004LearningModuleGUI("", $_GET["ref_id"], true, false);
                break;
                
            case "scorm":
                $this->slm_gui = new ilObjSCORMLearningModuleGUI("", $_GET["ref_id"], true, false);
                break;
        }

        if ($next_class == "") {
            switch ($type) {
                
                case "scorm2004":
                    $this->ctrl->setCmdClass("ilobjscorm2004learningmodulegui");
                    break;
                    
                case "scorm":
                    $this->ctrl->setCmdClass("ilobjscormlearningmodulegui");
                    break;
            }
            $next_class = $this->ctrl->getNextClass($this);
        }

        switch ($next_class) {
        case "ilobjscormlearningmodulegui":
        case "ilobjscorm2004learningmodulegui":
            $ret = $this->ctrl->forwardCommand($this->slm_gui);
            break;

        case "ilexportgui":
            $obj_id = ilObject::_lookupObjectId($_GET["ref_id"]);
            if ($cmd == "create_xml") {
                $exporter = new ilScormAiccExporter();
//                $xml = $exporter->getXmlRepresentation("sahs", "5.1.0", $obj_id);
            } elseif ($cmd == "download") {
                $file = $_GET["file"];
                $ftmp = explode(":", $file);
                $fileName = $ftmp[1];
                $exportDir = ilExport::_getExportDirectory($obj_id);
                ilUtil::deliverFile($exportDir . "/" . $fileName, $fileName);
            } elseif ($cmd == "confirmDeletion") {
                $exportDir = ilExport::_getExportDirectory($obj_id);
                foreach ($_POST["file"] as $file) {
                    $file = explode(":", $file);
                    $file[1] = basename($file[1]);
                    $exp_file = $exportDir . "/" . str_replace("..", "", $file[1]);
                    if (@is_file($exp_file)) {
                        unlink($exp_file);
                    }
                }
            }
            $this->ctrl->setCmd("export");
            ilUtil::redirect("ilias.php?baseClass=ilSAHSEditGUI&cmd=export&ref_id=" . $_GET["ref_id"]);
            break;


        default:
            die("ilSAHSEdit: Class $next_class not found.");
        }
        
        $this->tpl->printToStdout();
    }
}
