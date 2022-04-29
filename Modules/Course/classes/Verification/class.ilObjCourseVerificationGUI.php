<?php declare(strict_types=0);

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
 
use ILIAS\DI\Container;

/**
 * GUI class for course verification
 * @author       Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ilCtrl_Calls ilObjCourseVerificationGUI: ilWorkspaceAccessGUI
 */
class ilObjCourseVerificationGUI extends ilObject2GUI
{
    private Container $dic;
    protected ilErrorHandling $error;

    protected ilTabsGUI $tabs;

    public function __construct(int $a_id = 0, int $a_id_type = self::REPOSITORY_NODE_ID, int $a_parent_node_id = 0)
    {
        global $DIC;
        $this->dic = $DIC;
        $this->error = $DIC->error();
        $this->tabs = $DIC->tabs();

        parent::__construct($a_id, $a_id_type, $a_parent_node_id);
    }

    public function getType() : string
    {
        return "crsv";
    }

    public function create() : void
    {
        $this->lng->loadLanguageModule("crsv");

        $this->tabs->setBackTarget(
            $this->lng->txt("back"),
            $this->ctrl->getLinkTarget($this, "cancel")
        );

        $table = new ilCourseVerificationTableGUI($this, "create");
        $this->tpl->setContent($table->getHTML());
    }

    public function save() : void
    {
        $ilUser = $this->dic->user();

        $objectId = $this->getRequestValue("crs_id");
        if ($objectId) {
            $certificateVerificationFileService = new ilCertificateVerificationFileService(
                $this->dic->language(),
                $this->dic->database(),
                $this->dic->logger()->root(),
                new ilCertificateVerificationClassMap()
            );

            $userCertificateRepository = new ilUserCertificateRepository();

            $userCertificatePresentation = $userCertificateRepository->fetchActiveCertificateForPresentation(
                $ilUser->getId(),
                (int) $objectId
            );

            $newObj = null;
            try {
                $newObj = $certificateVerificationFileService->createFile($userCertificatePresentation);
            } catch (Exception $exception) {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('error_creating_certificate_pdf'));
                $this->create();
                return;
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

    public function deliver() : void
    {
        $file = $this->object->getFilePath();
        if ($file) {
            ilFileDelivery::deliverFileLegacy($file, $this->object->getTitle() . ".pdf");
        }
    }

    public function render(bool $a_return = false, string $a_url = '') : string
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
        if (ilPCVerification::isInPortfolioPage($a_page, $this->object->getType(), $this->object->getId())) {
            $this->deliver();
        }
        $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
    }

    public static function _goto(string $a_target) : void
    {
        global $DIC;

        $ctrl = $DIC->ctrl();

        $id = explode("_", $a_target);

        $ctrl->setParameterByClass(
            ilSharedResourceGUI::class,
            'wsp_id',
            $id[0]
        );
        $ctrl->redirectByClass(ilSharedResourceGUI::class);
    }

    /**
     * @param mixed  $default
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
