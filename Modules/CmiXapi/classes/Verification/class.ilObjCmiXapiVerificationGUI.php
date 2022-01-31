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
 * Class ilObjCmiXapiVerficationGUI
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/CmiXapi
 */
class ilObjCmiXapiVerificationGUI extends ilObject2GUI
{
    public function getType() : string
    {
        return "cmxv";
    }
    
    /**
     * List all tests in which current user participated
     */
    public function create() : void
    {
        global $ilTabs;

        $this->lng->loadLanguageModule("cmxv");
        
        $ilTabs->setBackTarget(
            $this->lng->txt("back"),
            $this->ctrl->getLinkTarget($this, "cancel")
        );
        $table = new ilCmiXapiVerificationTableGUI($this, "create");
        $this->tpl->setContent($table->getHTML());
    }
    
    /**
     * create new instance and save it
     */
    public function save() : void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $objId = $_REQUEST["cmix_id"];
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
//                return $this->create();
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
    
    public function deliver() : void
    {
        $file = $this->object->getFilePath();
        
        if ($file) {
            ilFileDelivery::deliverFileLegacy($file, $this->object->getTitle() . ".pdf");
        }
    }
    
    /**
     * Render content
     */
    public function render(bool $a_return = false, bool $a_url = false) : string
    {
        global $ilUser, $lng;
        
        if (!$a_return) {
            $this->deliver();
            return "";
        } else {
            $tree = new ilWorkspaceTree($ilUser->getId());
            $wsp_id = $tree->lookupNodeId($this->object->getId());
            
            $caption = $lng->txt("wsp_type_cmxv") . ' "' . $this->object->getTitle() . '"';
            
            $valid = true;
            if (!file_exists($this->object->getFilePath())) {
                $valid = false;
                $message = $lng->txt("url_not_found");
            } elseif (!$a_url) {
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
    
    public function downloadFromPortfolioPage(ilPortfolioPage $a_page) : void
    {
        global $ilErr;
        if (ilPCVerification::isInPortfolioPage($a_page, $this->object->getType(), $this->object->getId())) {
            $this->deliver();
        }
        
        $ilErr->raiseError($this->lng->txt('permission_denied'), $ilErr->MESSAGE);
    }
    
    public static function _goto($a_target) : void
    {
        $id = explode("_", $a_target);
        
        $_GET["baseClass"] = "ilsharedresourceGUI";
        $_GET["wsp_id"] = $id[0];
        exit;
    }
}
