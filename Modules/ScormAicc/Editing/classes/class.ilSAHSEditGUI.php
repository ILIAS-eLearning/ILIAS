<?php

declare(strict_types=1);
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
* Class ilSAHSPresentationGUI
*
* GUI class for scorm learning module presentation
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id: class.ilSAHSPresentationGUI.php 11714 2006-07-30 17:15:55Z akill $
*
* @ilCtrl_Calls ilSAHSEditGUI: ilFileSystemGUI, ilObjectMetaDataGUI, ilObjSCORMLearningModuleGUI, ilInfoScreenGUI, ilObjSCORM2004LearningModuleGUI, ilExportGUI, ilObjSAHSLearningModuleGUI, ilLTIProviderObjectSettingGUI
*
* @ingroup ModulesScormAicc
*/
class ilSAHSEditGUI implements ilCtrlBaseClassInterface
{
    private \ILIAS\HTTP\Wrapper\WrapperFactory $wrapper;
    private \ILIAS\Refinery\Factory $refinery;
    protected ilGlobalPageTemplate $tpl;
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;
    protected int $refId;

    /**
     * @var ilObjSCORMLearningModuleGUI|ilObjSCORM2004LearningModuleGUI
     */
    protected $slm_gui;

    /**
     * @throws ilCtrlException
     */
    public function __construct()
    {
        global $DIC;
        $this->tpl = $DIC['tpl'];
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->wrapper = $DIC->http()->wrapper();
        $this->refinery = $DIC->refinery();
        $this->refId = $DIC->http()->wrapper()->query()->retrieve('ref_id', $DIC->refinery()->kindlyTo()->int());

        $this->ctrl->saveParameter($this, "ref_id");
    }

    /**
     * @throws ilCtrlException
     */
    public function executeCommand(): void
    {
        global $DIC;

        $DIC->globalScreen()->tool()->context()->claim()->repository();

        $lng = $DIC->language();
        $ilAccess = $DIC->access();
        $ilNavigationHistory = $DIC['ilNavigationHistory'];
        $ilCtrl = $DIC->ctrl();
        $ilErr = $DIC["ilErr"];
        $ilLog = ilLoggerFactory::getLogger('sahs');
        $ilLog->debug("bc:" . $DIC->http()->wrapper()->query()->retrieve('baseClass', $DIC->refinery()->kindlyTo()->string()) . "; nc:" . $this->ctrl->getNextClass($this) . "; cmd:" . $this->ctrl->getCmd());

        $lng->loadLanguageModule("content");

        // permission
        if (!$ilAccess->checkAccess("write", "", $this->refId)) {
            $ilErr->raiseError($lng->txt("permission_denied"), $ilErr->MESSAGE);
        }

        // add entry to navigation history
        $ilNavigationHistory->addItem(
            $this->refId,
            "ilias.php?baseClass=ilSAHSEditGUI&ref_id=" . $this->refId,
            "lm"
        );

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $obj_id = ilObject::_lookupObjectId($this->refId);
        $type = ilObjSAHSLearningModule::_lookupSubType($obj_id);

        switch ($type) {

            case "scorm":
                $this->slm_gui = new ilObjSCORMLearningModuleGUI([], $this->refId, true, false);
                break;

            case "scorm2004":
                $this->slm_gui = new ilObjSCORM2004LearningModuleGUI([], $this->refId, true, false);
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
            $obj_id = ilObject::_lookupObjectId($this->refId);
            if ($cmd === "create_xml") {
                $exporter = new ilScormAiccExporter();
                $xml = $exporter->getXmlRepresentation("sahs", "5.1.0", (string) $obj_id);
            } elseif ($cmd === "download") {
                $file = $this->wrapper->query()->retrieve('file', $this->refinery->kindlyTo()->string());
                $ftmp = explode(":", $file);
                $fileName = (string) $ftmp[1];
                $exportDir = ilExport::_getExportDirectory($obj_id);
                ilFileDelivery::deliverFileLegacy($exportDir . "/" . $fileName, $fileName, "zip");
            } elseif ($cmd === "confirmDeletion") {
                $exportDir = ilExport::_getExportDirectory($obj_id);
//                $files = $_POST['file'];
                $files = $this->wrapper->post()->retrieve('file', $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->string()));
                foreach ($files as $file) {
                    $file = explode(":", $file);
                    $file[1] = basename($file[1]);
                    $exp_file = $exportDir . "/" . str_replace("..", "", $file[1]);
                    if (@is_file($exp_file)) {
                        unlink($exp_file);
                    }
                }
            }
            $this->ctrl->setCmd("export");
            ilUtil::redirect("ilias.php?baseClass=ilSAHSEditGUI&cmd=export&ref_id=" . $this->refId);
            break;


        default:
            die("ilSAHSEdit: Class $next_class not found.");
        }

        $this->tpl->printToStdout();
    }
}
