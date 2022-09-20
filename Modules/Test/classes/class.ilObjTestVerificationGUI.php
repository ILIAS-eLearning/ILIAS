<?php

declare(strict_types=1);
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * GUI class for test verification
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ilCtrl_Calls ilObjTestVerificationGUI: ilWorkspaceAccessGUI
 *
 * @ingroup ModulesTest
 */
class ilObjTestVerificationGUI extends ilObject2GUI
{
    public function __construct(int $id = 0, int $id_type = self::REPOSITORY_NODE_ID, int $parent_node_id = 0)
    {
        parent::__construct($id, $id_type, $parent_node_id);
    }

    public function getType(): string
    {
        return "tstv";
    }

    public function create(): void
    {
        global $DIC;
        $ilTabs = $DIC['ilTabs'];

        $this->lng->loadLanguageModule("tstv");

        $ilTabs->setBackTarget(
            $this->lng->txt("back"),
            $this->ctrl->getLinkTarget($this, "cancel")
        );

        $table = new ilTestVerificationTableGUI($this, "create");
        $this->tpl->setContent($table->getHTML());
    }

    public function save(): void
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];

        $objectId = $this->getRequestValue("tst_id");
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

            $newObj = null;
            try {
                $newObj = $certificateVerificationFileService->createFile($userCertificatePresentation);
            } catch (\Exception $exception) {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('error_creating_certificate_pdf'));
                $this->create();
            }

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

    public function deliver(): void
    {
        $file = $this->object->getFilePath();
        if ($file) {
            ilFileDelivery::deliverFileLegacy($file, $this->object->getTitle() . ".pdf");
        }
    }

    public function render(bool $a_return = false, string $a_url = ''): string
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];
        $lng = $DIC['lng'];

        if (!$a_return) {
            $this->deliver();
        } else {
            $tree = new ilWorkspaceTree($ilUser->getId());
            $wsp_id = $tree->lookupNodeId($this->object->getId());
            $caption = $lng->txt("wsp_type_tstv") . ' "' . $this->object->getTitle() . '"';

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

    public function downloadFromPortfolioPage(ilPortfolioPage $a_page): void
    {
        global $DIC;
        $ilErr = $DIC['ilErr'];

        if (ilPCVerification::isInPortfolioPage($a_page, $this->object->getType(), $this->object->getId())) {
            $this->deliver();
        }

        $ilErr->raiseError($this->lng->txt('permission_denied'), $ilErr->MESSAGE);
    }

    public static function _goto(string $a_target): void
    {
        global $DIC;
        $id = explode("_", $a_target);
        $DIC->ctrl()->setParameterByClass('ilsharedresourceGUI', 'wsp_id', $id[0]);
        $DIC->ctrl()->redirectByClass('ilsharedresourceGUI');
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed|null
     */
    protected function getRequestValue(string $key, $default = null)
    {
        if (isset($this->request->getQueryParams()[$key])) {
            return $this->request->getQueryParams()[$key];
        }

        if (isset($this->request->getParsedBody()[$key])) {
            return $this->request->getParsedBody()[$key];
        }

        return $default ?? null;
    }
}
