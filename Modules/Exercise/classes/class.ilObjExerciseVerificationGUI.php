<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('./Services/Object/classes/class.ilObject2GUI.php');

/**
* GUI class for exercise verification
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id: class.ilPersonalDesktopGUI.php 26976 2010-12-16 13:24:38Z akill $
*
* @ilCtrl_Calls ilObjExerciseVerificationGUI: ilWorkspaceAccessGUI
*
* @ingroup ModulesExercise
*/
class ilObjExerciseVerificationGUI extends ilObject2GUI
{
    public function getType()
    {
        return "excv";
    }

    /**
     * List all exercises in which current user participated
     */
    public function create()
    {
        $ilTabs = $this->tabs_gui;
        
        if ($this->id_type == self::WORKSPACE_NODE_ID) {
            include_once "Services/DiskQuota/classes/class.ilDiskQuotaHandler.php";
            if (!ilDiskQuotaHandler::isUploadPossible()) {
                $this->lng->loadLanguageModule("file");
                ilUtil::sendFailure($this->lng->txt("personal_workspace_quota_exceeded_warning"), true);
                $this->ctrl->redirect($this, "cancel");
            }
        }

        $this->lng->loadLanguageModule("excv");

        $ilTabs->setBackTarget(
            $this->lng->txt("back"),
            $this->ctrl->getLinkTarget($this, "cancel")
        );

        include_once "Modules/Exercise/classes/class.ilExerciseVerificationTableGUI.php";
        $table = new ilExerciseVerificationTableGUI($this, "create");
        $this->tpl->setContent($table->getHTML());
    }

    /**
     * create new instance and save it
     */
    public function save()
    {
        global $DIC;

        $ilUser = $this->user;

        $objectId = $_REQUEST["exc_id"];
        if ($objectId) {
            $certificateVerificationFileService = new ilCertificateVerificationFileService(
                $DIC->language(),
                $DIC->database(),
                $DIC->logger()->root(),
                new ilCertificateVerificationClassMap()
            );

            $userCertificateRepository = new ilUserCertificateRepository();

            $userCertificatePresentation = $userCertificateRepository->fetchActiveCertificateForPresentation(
                (int) $ilUser->getId(),
                (int) $objectId
            );

            try {
                $newObj = $certificateVerificationFileService->createFile($userCertificatePresentation);
            } catch (\Exception $exception) {
                ilUtil::sendFailure($this->lng->txt('error_creating_certificate_pdf'));
                return $this->create();
            }

            if ($newObj) {
                $parent_id = $this->node_id;
                $this->node_id = null;
                $this->putObjectInTree($newObj, $parent_id);
                
                $this->afterSave($newObj);
            } else {
                ilUtil::sendFailure($this->lng->txt("msg_failed"));
            }
        } else {
            ilUtil::sendFailure($this->lng->txt("select_one"));
        }
        $this->create();
    }
    
    public function deliver()
    {
        $file = $this->object->getFilePath();
        if ($file) {
            ilUtil::deliverFile($file, $this->object->getTitle() . ".pdf");
        }
    }
    
    /**
     * Render content
     *
     * @param bool $a_return
     * @param string $a_url
     */
    public function render($a_return = false, $a_url = false)
    {
        $ilUser = $this->user;
        $lng = $this->lng;
        
        if (!$a_return) {
            $this->deliver();
        } else {
            $tree = new ilWorkspaceTree($ilUser->getId());
            $wsp_id = $tree->lookupNodeId($this->object->getId());
            
            $caption = $lng->txt("wsp_type_excv") . ' "' . $this->object->getTitle() . '"';
            
            $valid = true;
            if (!file_exists($this->object->getFilePath())) {
                $valid = false;
                $message = $lng->txt("url_not_found");
            } elseif (!$a_url) {
                include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceAccessHandler.php";
                $access_handler = new ilWorkspaceAccessHandler($tree);
                if (!$access_handler->checkAccess("read", "", $wsp_id)) {
                    $valid = false;
                    $message = $lng->txt("permission_denied");
                }
            }
            
            if ($valid) {
                if (!$a_url) {
                    $a_url = $this->getAccessHandler()->getGotoLink($wsp_id, $this->object->getId());
                }
                return '<div><a href="' . $a_url . '">' . $caption . '</a></div>';
            } else {
                return '<div>' . $caption . ' (' . $message . ')</div>';
            }
        }
    }
    
    public function downloadFromPortfolioPage(ilPortfolioPage $a_page)
    {
        include_once "Services/COPage/classes/class.ilPCVerification.php";
        if (ilPCVerification::isInPortfolioPage($a_page, $this->object->getType(), $this->object->getId())) {
            $this->deliver();
        }
        
        include_once "Modules/Exercise/exceptions/class.ilExerciseException.php";
        throw new ilExerciseException($this->lng->txt('permission_denied'));
    }

    public static function _goto($a_target)
    {
        $id = explode("_", $a_target);
        
        $_GET["baseClass"] = "ilsharedresourceGUI";
        $_GET["wsp_id"] = $id[0];
        include("ilias.php");
        exit;
    }
}
