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
* Class ilSCORMPresentationGUI
*
* GUI class for scorm learning module presentation
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesScormAicc
*/
class ilSCORMPresentationGUI
{
    public ilObjSCORMLearningModule $slm;
    public ilGlobalTemplate $tpl;
    public ilLanguage $lng;
    protected int $refId;
    protected ilCtrlInterface $ctrl;

    public function __construct()
    {
        global $DIC;
        $ilCtrl = $DIC->ctrl();
//        $this->tpl = $DIC['tpl'];
        $this->lng = $DIC->language();
        $this->ctrl = $ilCtrl;

        $this->lng->loadLanguageModule('cert');

        // Todo: check lm id
        $this->refId = $DIC->http()->wrapper()->query()->retrieve('ref_id', $DIC->refinery()->kindlyTo()->int());
        $this->slm = new ilObjSCORMLearningModule($this->refId, true);
    }

    /**
     * execute command
     * @throws ilCtrlException
     */
    public function executeCommand() : void
    {
        global $DIC;
        $ilAccess = $DIC->access();
        $ilLog = ilLoggerFactory::getLogger('sahs');
        $lng = $DIC->language();
        $ilErr = $DIC["ilErr"];

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd("frameset");

        if (!$ilAccess->checkAccess("write", "", $this->refId) &&
            (!$ilAccess->checkAccess("read", "", $this->refId) ||
            $this->slm->getOfflineStatus())) {
            $ilErr->raiseError($lng->txt("permission_denied"), $ilErr->WARNING);
        }

//        switch ($next_class) {
//            default:
        $this->$cmd();
//        }
    }

    public function attrib2arr(?array $a_attributes) : array
    {
        $attr = array();

        if (!is_array($a_attributes)) {
            return $attr;
        }
        foreach ($a_attributes as $attribute) {
            $attr[$attribute->name()] = $attribute->value();
        }
    
        return $attr;
    }

    /**
     * Output main frameset. If only one SCO/Asset is given, it is displayed
     * without the table of contents explorer frame on the left.
     * @throws ilCtrlException
     */
    public function frameset() : void
    {
        global $DIC;
        $lng = $DIC->language();
        $javascriptAPI = true;
        $items = ilSCORMObject::_lookupPresentableItems($this->slm->getId());
        
        //check for max_attempts and raise error if max_attempts is exceeded
//        if ($this->get_max_attempts() != 0) {
//            if ($this->get_actual_attempts() >= $this->get_max_attempts()) {
//                header('Content-Type: text/html; charset=utf-8');
//                echo($lng->txt("cont_sc_max_attempt_exceed"));
//                exit;
//            }
//        }
    
        $this->increase_attemptAndsave_module_version();
        ilWACSignedPath::signFolderOfStartFile($this->slm->getDataDirectory() . '/imsmanifest.xml');

        $debug = $this->slm->getDebug();
        if (count($items) > 1) {
            $this->ctrl->setParameter($this, "expand", "1");
            $this->ctrl->setParameter($this, "jsApi", "1");
            $exp_link = $this->ctrl->getLinkTarget($this, "explorer");
            
            // should be able to grep templates
            if ($debug) {
                $this->tpl = new ilGlobalTemplate("tpl.sahs_pres_frameset_js_debug.html", false, false, "Modules/ScormAicc");
            } else {
                $this->tpl = new ilGlobalTemplate("tpl.sahs_pres_frameset_js.html", false, false, "Modules/ScormAicc");
            }
                            
            $this->tpl->setVariable("EXPLORER_LINK", $exp_link);
            $pres_link = $this->ctrl->getLinkTarget($this, "contentSelect");
            $this->tpl->setVariable("PRESENTATION_LINK", $pres_link);
        } else {
            if ($debug) {
                $this->tpl = new ilGlobalTemplate("tpl.sahs_pres_frameset_js_debug_one_page.html", false, false, "Modules/ScormAicc");
            } else {
                $this->tpl = new ilGlobalTemplate("tpl.sahs_pres_frameset_js_one_page.html", false, false, "Modules/ScormAicc");
            }

            $this->ctrl->setParameter($this, "autolaunch", $items[0]);
        }
        $api_link = $this->ctrl->getLinkTarget($this, "apiInitData");
        $this->tpl->setVariable("API_LINK", $api_link);
        $this->tpl->printToStdout("DEFAULT", false, true);

        
        exit;
    }

//    /**
//    * Get max. number of attempts allowed for this package
//    */
//    public function get_max_attempts() : int
//    {
//        return ilObjSCORMInitData::get_max_attempts($this->slm->getId());
//    }

    /**
     * Get number of actual attempts for the user
     */
    public function get_actual_attempts() : int
    {
        global $DIC;
        $ilDB = $DIC->database();
        $ilUser = $DIC->user();
        $val_set = $ilDB->queryF(
            'SELECT package_attempts FROM sahs_user WHERE obj_id = %s AND user_id = %s',
            array('integer','integer'),
            array($this->slm->getId(),$ilUser->getId())
        );
        $val_rec = $ilDB->fetchAssoc($val_set);
        $attempts = $val_rec["package_attempts"];
        if ($attempts == null) {
            $attempts = 0;
        }
        return $attempts;
    }
    // function get_actual_attempts() {
    // global $DIC;
    // $ilDB = $DIC->database();
    // $ilUser = $DIC->user();
    // $val_set = $ilDB->queryF('
    // SELECT * FROM scorm_tracking
    // WHERE user_id =  %s
    // AND sco_id = %s
    // AND lvalue= %s
    // AND obj_id = %s',
    // array('integer','integer','text','integer'),
    // array($ilUser->getId(),0,'package_attempts',$this->slm->getId())
    // );
    // $val_rec = $ilDB->fetchAssoc($val_set);
        
    // $val_rec["rvalue"] = str_replace("\r\n", "\n", $val_rec["rvalue"]);
    // if ($val_rec["rvalue"] == null) {
    // $val_rec["rvalue"]=0;
    // }

    // return $val_rec["rvalue"];
    // }
    
    /**
    * Increases attempts by one for this package
    */
    // function increase_attempt() {
    // global $DIC;
    // $ilDB = $DIC->database();
    // $ilUser = $DIC->user();
        
    // //get existing account - sco id is always 0
    // $val_set = $ilDB->queryF('
    // SELECT * FROM scorm_tracking
    // WHERE user_id =  %s
    // AND sco_id = %s
    // AND lvalue= %s
    // AND obj_id = %s',
    // array('integer','integer','text','integer'),
    // array($ilUser->getId(),0,'package_attempts',$this->slm->getId())
    // );

    // $val_rec = $ilDB->fetchAssoc($val_set);
        
    // $val_rec["rvalue"] = str_replace("\r\n", "\n", $val_rec["rvalue"]);
    // if ($val_rec["rvalue"] == null) {
    // $val_rec["rvalue"]=0;
    // }
    // $new_rec =  $val_rec["rvalue"]+1;
    // //increase attempt by 1
    // //TODO: do not set c_timestamp because of last_access
    // if($ilDB->numRows($val_set) > 0)
    // {
    // $ilDB->update('scorm_tracking',
    // array(
    // 'rvalue'		=> array('clob', $new_rec),
    // 'c_timestamp'	=> array('timestamp', ilUtil::now())
    // ),
    // array(
    // 'user_id'		=> array('integer', $ilUser->getId()),
    // 'sco_id'		=> array('integer', 0),
    // 'lvalue'		=> array('text', 'package_attempts'),
    // 'obj_id'		=> array('integer', $this->slm->getId())
    // )
    // );
    // }
    // else
    // {
    // $ilDB->insert('scorm_tracking', array(
    // 'obj_id'		=> array('integer', $this->slm->getId()),
    // 'user_id'		=> array('integer', $ilUser->getId()),
    // 'sco_id'		=> array('integer', 0),
    // 'lvalue'		=> array('text', 'package_attempts'),
    // 'rvalue'		=> array('clob', $new_rec),
    // 'c_timestamp'	=> array('timestamp', ilUtil::now())
    // ));
    // }
        
    // include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
    // ilLPStatusWrapper::_updateStatus($this->slm->getId(), $ilUser->getId());

    // }

    /**
     * Increases attempts by one and saves module_version for this package
     */
    public function increase_attemptAndsave_module_version() : void
    {
        global $DIC;
        $ilDB = $DIC->database();
        $ilUser = $DIC->user();
        $res = $ilDB->queryF(
            'SELECT package_attempts,count(*) cnt FROM sahs_user WHERE obj_id = %s AND user_id = %s GROUP BY package_attempts',
            array('integer','integer'),
            array($this->slm->getId(),$ilUser->getId())
        );
        $val_rec = $ilDB->fetchAssoc($res);
        if ($val_rec["cnt"] == 0) { //offline_mode could be inserted
            $attempts = 1;
            $ilDB->manipulateF(
                'INSERT INTO sahs_user (obj_id,user_id,package_attempts,module_version,last_access) VALUES(%s,%s,%s,%s,%s)',
                array('integer', 'integer', 'integer', 'integer', 'timestamp'),
                array($this->slm->getId(), $ilUser->getId(), $attempts, $this->slm->getModuleVersion(), date('Y-m-d H:i:s'))
            );
        } else {
            $attempts = $val_rec["package_attempts"];
            if ($attempts == null) {
                $attempts = 0;
            }
            $attempts++;
            $ilDB->manipulateF(
                'UPDATE sahs_user SET package_attempts = %s, module_version = %s, last_access=%s WHERE obj_id = %s AND user_id = %s ',
                array('integer', 'integer', 'timestamp', 'integer', 'integer'),
                array($attempts, $this->slm->getModuleVersion(), date('Y-m-d H:i:s'), $this->slm->getId(), $ilUser->getId())
            );
        }
        ilLPStatusWrapper::_updateStatus($this->slm->getId(), $ilUser->getId());
    }

    /**
    * save the active module version to scorm_tracking
    */
    // function save_module_version() {
    // global $DIC;
    // $ilDB = $DIC->database();
    // $ilUser = $DIC->user();

    // $val_set = $ilDB->queryF('
    // SELECT * FROM scorm_tracking
    // WHERE user_id =  %s
    // AND sco_id = %s
    // AND lvalue= %s
    // AND obj_id = %s',
    // array('integer','integer','text','integer'),
    // array($ilUser->getId(),0,'module_version',$this->slm->getId())

    // );
        
    // if($ilDB->numRows($val_set) > 0)
    // {
    // $ilDB->update('scorm_tracking',
    // array(
    // 'rvalue'		=> array('clob', $this->slm->getModuleVersion()),
    // 'c_timestamp'	=> array('timestamp', ilUtil::now())
    // ),
    // array(
    // 'user_id'		=> array('integer', $ilUser->getId()),
    // 'sco_id'		=> array('integer', 0),
    // 'lvalue'		=> array('text', 'module_version'),
    // 'obj_id'		=> array('integer', $this->slm->getId())
    // )
    // );
    // }
    // else
    // {
    // $ilDB->insert('scorm_tracking', array(
    // 'obj_id'		=> array('integer', $this->slm->getId()),
    // 'user_id'		=> array('integer', $ilUser->getId()),
    // 'sco_id'		=> array('integer', 0),
    // 'lvalue'		=> array('text', 'module_version'),
    // 'rvalue'		=> array('clob', $this->slm->getModuleVersion()),
    // 'c_timestamp'	=> array('timestamp', ilUtil::now())
    // ));
    // }
        
    // include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
    // ilLPStatusWrapper::_updateStatus($this->slm->getId(), $ilUser->getId());
        
    // }

    /**
     * output table of content
     * @throws ilCtrlException
     * @throws ilTemplateException
     */
    public function explorer(string $a_target = "sahs_content") : void
    {
        global $DIC;
        $ilBench = $DIC['ilBench'];
        $ilLog = ilLoggerFactory::getLogger('sahs');

        $ilBench->start("SCORMExplorer", "initExplorer");
        
        $this->tpl = new ilGlobalTemplate("tpl.sahs_exp_main.html", true, true, "Modules/ScormAicc");
//        $this->tpl = new ilTemplate("tpl.sahs_exp_main.html", true, true, "Modules/ScormAicc");
        $exp = new ilSCORMExplorer($this->ctrl->getLinkTarget($this, "view"), $this->slm);
        $exp->setTargetGet("obj_id");
        $exp->setFrameTarget($a_target);
        
        //$exp->setFiltered(true);
        $jsApi = true;

        $expanded = "";
        if ($DIC->http()->wrapper()->query()->has('scexpand')) {
            $expanded = $DIC->http()->wrapper()->query()->retrieve('scexpand', $DIC->refinery()->kindlyTo()->string());
        }
        if ($expanded == "") {
            $mtree = new ilSCORMTree($this->slm->getId());
            $expanded = $mtree->readRootId();
        }
        $exp->setExpand($expanded);
        
        $exp->forceExpandAll(true, false);
        $ilBench->stop("SCORMExplorer", "initExplorer");

        // build html-output
        $ilBench->start("SCORMExplorer", "setOutput");
        $exp->setOutput(0);
        $ilBench->stop("SCORMExplorer", "setOutput");

        $ilBench->start("SCORMExplorer", "getOutput");
        $output = $exp->getOutput($jsApi);
        $ilBench->stop("SCORMExplorer", "getOutput");

        $this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
        $this->tpl->addBlockFile("CONTENT", "content", "tpl.sahs_explorer.html", "Modules/ScormAicc");
        //$this->tpl->setVariable("TXT_EXPLORER_HEADER", $this->lng->txt("cont_content"));
        $this->tpl->setVariable("EXP_REFRESH", $this->lng->txt("refresh"));
        $this->tpl->setVariable("EXPLORER", $output);

        $cmd = "";
        if ($DIC->http()->wrapper()->query()->has('cmd')) {
            $cmd = $DIC->http()->wrapper()->query()->retrieve('cmd', $DIC->refinery()->kindlyTo()->string());
        }
        $frame = "";
        if ($DIC->http()->wrapper()->query()->has('frame')) {
            $frame = $DIC->http()->wrapper()->query()->retrieve('frame', $DIC->refinery()->kindlyTo()->string());
        }
        $this->tpl->setVariable("ACTION", "ilias.php?baseClass=ilSAHSPresentationGUI&cmd=" . $cmd . "&frame=" . $frame .
            "&ref_id=" . $this->slm->getRefId() . "&scexpand=" . $expanded);
        $this->tpl->parseCurrentBlock();
        //BUG 16794? $this->tpl->show();
//        $this->tpl->show();
        $this->tpl->printToStdout("DEFAULT", false);
    }


    /**
    * SCORM content screen
    */
    public function view() : void
    {
        global $DIC;
        $objId = $DIC->http()->wrapper()->query()->retrieve('obj_id', $DIC->refinery()->kindlyTo()->int());
        $sc_gui_object = ilSCORMObjectGUI::getInstance($objId);

        if (is_object($sc_gui_object)) {
            $sc_gui_object->view();
        }

        $this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
        $this->tpl->printToStdout('DEFAULT', false);
    }

    public function contentSelect() : void
    {
        global $DIC;
        $lng = $DIC->language();
        $this->tpl = new ilGlobalTemplate("tpl.scorm_content_select.html", true, true, "Modules/ScormAicc");
        $this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
        $this->tpl->setVariable('TXT_SPECIALPAGE', $lng->txt("seq_toc"));
        $this->tpl->printToStdout("DEFAULT", false);
    }
    
    /**
     * SCORM Data for Javascript-API
     */
    public function apiInitData() : void
    {
        global $DIC;

        if (!($DIC->http()->wrapper()->query()->has('ref_id'))) {
            print('alert("no start without ref_id");');
            die;
        }

        header('Content-Type: text/javascript; charset=UTF-8');
        print("function iliasApi() {\r\n");
        $js_data = file_get_contents("./Modules/ScormAicc/scripts/basisAPI.js");
        echo $js_data;
        $js_data = file_get_contents("./Modules/ScormAicc/scripts/SCORM1_2standard.js");//want to give opportunities to different files (Uwe Kohnle)
        echo $js_data;
        print("}\r\n");

        print("IliasScormVars=" . ilObjSCORMInitData::getIliasScormVars($this->slm) . ";\r\n");

        //Resources
        print("IliasScormResources=" . ilObjSCORMInitData::getIliasScormResources($this->slm->getId()) . ";\r\n");

        //Tree
        print("IliasScormTree=" . ilObjSCORMInitData::getIliasScormTree($this->slm->getId()) . ";\r\n");

        //prevdata
        print("IliasScormData=" . ilObjSCORMInitData::getIliasScormData($this->slm->getId()) . ";\r\n");

        // set alternative API name - not necessary for scorm
        if ($this->slm->getAPIAdapterName() !== "API") {
            print('var ' . $this->slm->getAPIAdapterName() . '=new iliasApi();');
        } else {
            print('var API=new iliasApi();');
        }
    }

    public function pingSession() : bool
    {
        ilWACSignedPath::signFolderOfStartFile($this->slm->getDataDirectory() . '/imsmanifest.xml');
        return true;
    }

    /**
     * Download the certificate for the active user
     * @throws ilCtrlException
     */
    public function downloadCertificate() : void
    {
        global $DIC;

        $ilUser = $DIC->user();
        $tree = $DIC['tree'];
        $ilCtrl = $DIC->ctrl();
        $refId = $DIC->http()->wrapper()->query()->retrieve('ref_id', $DIC->refinery()->kindlyTo()->int());
        $obj_id = ilObject::_lookupObjId($refId);

        $certValidator = new ilCertificateDownloadValidator();
        $allowed = $certValidator->isCertificateDownloadable($ilUser->getId(), $obj_id);
        if ($allowed) {
            $certificateLogger = $DIC->logger()->root();

            $ilUserCertificateRepository = new ilUserCertificateRepository();
            $pdfGenerator = new ilPdfGenerator($ilUserCertificateRepository, $certificateLogger);

            $pdfAction = new ilCertificatePdfAction(
                $certificateLogger,
                $pdfGenerator,
                new ilCertificateUtilHelper(),
                $this->lng->txt('error_creating_certificate_pdf')
            );

            $pdfAction->downloadPdf($ilUser->getId(), $obj_id);
            exit;
        }
        // redirect to parent category if certificate is not accessible
        $parent = $tree->getParentId($refId);
        $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $parent);
        $ilCtrl->redirectByClass("ilrepositorygui", "");
    }
}
