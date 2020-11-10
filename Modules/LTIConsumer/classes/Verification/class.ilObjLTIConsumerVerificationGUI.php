<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilObjLTIConsumerVerificationGUI
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/LTIConsumer
 */
class ilObjLTIConsumerVerificationGUI extends ilObject2GUI
{
    public function getType()
    {
        return "ltiv";
    }
    
    /**
     * List all tests in which current user participated
     */
    public function create()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $this->lng->loadLanguageModule("ltiv");
        
        $DIC->tabs()->setBackTarget(
            $this->lng->txt("back"),
            $this->ctrl->getLinkTarget($this, "cancel")
        );
        
        include_once "Modules/Course/classes/Verification/class.ilCourseVerificationTableGUI.php";
        $table = new ilLTIConsumerVerificationTableGUI($this, "create");
        $this->tpl->setContent($table->getHTML());
    }
    
    /**
     * create new instance and save it
     */
    public function save()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $objId = $_REQUEST["lti_id"];
        if ($objId) {
            $certificateVerificationFileService = new ilCertificateVerificationFileService(
                $DIC->language(),
                $DIC->database(),
                $DIC->logger()->root(),
                new ilCertificateVerificationClassMap()
            );

            $userCertificateRepository = new ilUserCertificateRepository();

            $userCertificatePresentation = $userCertificateRepository->fetchActiveCertificateForPresentation(
                (int) $DIC->user()->getId(),
                (int) $objId
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
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        if (!$a_return) {
            $this->deliver();
        } else {
            $tree = new ilWorkspaceTree($DIC->user()->getId());
            $wsp_id = $tree->lookupNodeId($this->object->getId());
            
            $caption = $DIC->language()->txt("wsp_type_ltiv") . ' "' . $this->object->getTitle() . '"';
            
            $valid = true;
            if (!file_exists($this->object->getFilePath())) {
                $valid = false;
                $message = $DIC->language()->txt("url_not_found");
            } elseif (!$a_url) {
                include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceAccessHandler.php";
                $access_handler = new ilWorkspaceAccessHandler($tree);
                if (!$access_handler->checkAccess("read", "", $wsp_id)) {
                    $valid = false;
                    $message = $DIC->language()->txt("permission_denied");
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
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        include_once "Services/COPage/classes/class.ilPCVerification.php";
        if (ilPCVerification::isInPortfolioPage($a_page, $this->object->getType(), $this->object->getId())) {
            $this->deliver();
        }
        
        $DIC['ilErr']->raiseError($this->lng->txt('permission_denied'), $DIC['ilErr']->MESSAGE);
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
