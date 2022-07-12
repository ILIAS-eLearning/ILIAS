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
 * GUI class for scorm verification
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ilCtrl_Calls ilObjSCORMVerificationGUI: ilWorkspaceAccessGUI
 */
class ilObjSCORMVerificationGUI extends ilObject2GUI
{
    public function getType() : string
    {
        return "scov";
    }

    /**
     * @throws ilCtrlException
     */
    public function create() : void
    {
        global $DIC;
        $ilTabs = $DIC->tabs();

        $this->lng->loadLanguageModule("scov");

        $ilTabs->setBackTarget(
            $this->lng->txt("back"),
            $this->ctrl->getLinkTarget($this, "cancel")
        );

        $table = new ilSCORMVerificationTableGUI($this, "create");
        $this->tpl->setContent($table->getHTML());
    }

    /**
     * @throws JsonException
     * @throws ilCtrlException
     * @throws ilException
     */
    public function save() : void
    {
        global $DIC;

        $ilUser = $DIC->user();

        $objectId = $this->getRequestValue("lm_id");
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
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('error_creating_certificate_pdf'));
                $this->create();
            }

            $newObj = null;
            if ($newObj) {
                $parent_id = $this->node_id;
                $this->node_id = null;
                $this->putObjectInTree($newObj, $parent_id);

                $this->afterSave($newObj);
            } else {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt("msg_failed"));
            }
        } else {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("select_one"));
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

    public function render(bool $a_return = false, string $a_url = '') : string
    {
        global $DIC;
        $ilUser = $DIC->user();
        $lng = $DIC->language();

        if (!$a_return) {
            $this->deliver();
        } else {
            $tree = new ilWorkspaceTree($ilUser->getId());
            $wsp_id = $tree->lookupNodeId($this->object->getId());

            $caption = $lng->txt("wsp_type_scov") . ' "' . $this->object->getTitle() . '"';

            $valid = true;
            $message = '';
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
            }

            return '<div>' . $caption . ' (' . $message . ')</div>';
        }

        return "";
    }

    public function downloadFromPortfolioPage(ilPortfolioPage $a_page) : void
    {
        global $DIC;
        $ilErr = $DIC['ilErr'];

        if (ilPCVerification::isInPortfolioPage($a_page, $this->object->getType(), $this->object->getId())) {
            $this->deliver();
        }

        $ilErr->raiseError($this->lng->txt('permission_denied'), $ilErr->MESSAGE);
    }

    public static function _goto(string $a_target) : void
    {
        global $DIC;
        $id = explode("_", $a_target);

        $DIC->ctrl->setParameterByClass(
            "ilsharedresourceGUI",
            "wsp_id",
            $id[0]
        );
        $DIC->ctrl->redirectByClass(ilSharedResourceGUI::class);
    }

    /**
     * @param mixed $default
     * @return mixed
     */
    protected function getRequestValue(string $key, $default = null)
    {
        if (isset($this->request->getQueryParams()[$key])) {
            return $this->request->getQueryParams()[$key];
        }

        return $this->request->getParsedBody()[$key] ?? $default ?? null;
    }
}
