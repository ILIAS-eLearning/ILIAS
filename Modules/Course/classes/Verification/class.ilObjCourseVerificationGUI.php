<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\DI\Container;

include_once('./Services/Object/classes/class.ilObject2GUI.php');

/**
 * GUI class for course verification
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 *
 * @ilCtrl_Calls ilObjCourseVerificationGUI: ilWorkspaceAccessGUI
 */
class ilObjCourseVerificationGUI extends ilObject2GUI
{
    private Container $dic;

    public function __construct($a_id = 0, $a_id_type = self::REPOSITORY_NODE_ID, $a_parent_node_id = 0)
    {
        global $DIC;
        $this->dic = $DIC;
        parent::__construct($a_id, $a_id_type, $a_parent_node_id);
    }

    public function getType()
    {
        return "crsv";
    }

    /**
     * List all tests in which current user participated
     */
    public function create()
    {
        $ilTabs = $this->dic->tabs();

        $this->lng->loadLanguageModule("crsv");

        $ilTabs->setBackTarget(
            $this->lng->txt("back"),
            $this->ctrl->getLinkTarget($this, "cancel")
        );

        $table = new ilCourseVerificationTableGUI($this, "create");
        $this->tpl->setContent($table->getHTML());
    }

    /**
     * create new instance and save it
     */
    public function save()
    {
        $ilUser = $this->dic->user();
        
        $objectId = $_REQUEST["crs_id"];
        if ($objectId) {
            $certificateVerificationFileService = new ilCertificateVerificationFileService(
                $this->dic->language(),
                $this->dic->database(),
                $this->dic->logger()->root(),
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
        $ilUser = $this->dic->user();
        $lng = $this->dic->language();
        
        if (!$a_return) {
            $this->deliver();
        } else {
            $tree = new ilWorkspaceTree($ilUser->getId());
            $wsp_id = $tree->lookupNodeId($this->object->getId());
            
            $caption = $lng->txt("wsp_type_crsv") . ' "' . $this->object->getTitle() . '"';
            
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
        $ilErr = $this->dic['ilErr'];
        
        include_once "Services/COPage/classes/class.ilPCVerification.php";
        if (ilPCVerification::isInPortfolioPage($a_page, $this->object->getType(), $this->object->getId())) {
            $this->deliver();
        }
        
        $ilErr->raiseError($this->lng->txt('permission_denied'), $ilErr->MESSAGE);
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
