<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\DI\Container;

include_once './Services/Table/classes/class.ilTable2GUI.php';

/**
 * List all completed course for current user
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ModulesCourse
 */
class ilCourseVerificationTableGUI extends ilTable2GUI
{
    private $userCertificateRepository;
    private Container $dic;

    /**
     * @param ilObject $a_parent_obj
     * @param string $a_parent_cmd
     * @param ilUserCertificateRepository|null $userCertificateRepository
     */
    public function __construct(
        $a_parent_obj,
        $a_parent_cmd = "",
        ilUserCertificateRepository $userCertificateRepository = null
    ) {
        global $DIC;

        $this->dic = $DIC;

        $ilCtrl = $DIC->ctrl();
        $database = $DIC->database();
        $logger = $DIC->logger()->root();

        if (null === $userCertificateRepository) {
            $userCertificateRepository = new ilUserCertificateRepository($database, $logger);
        }
        $this->userCertificateRepository = $userCertificateRepository;

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->addColumn($this->lng->txt("title"), "title");
        $this->addColumn($this->lng->txt("passed"), "passed");
        $this->addColumn($this->lng->txt("action"), "");

        $this->setTitle($this->lng->txt("crsv_create"));
        $this->setDescription($this->lng->txt("crsv_create_info"));

        $this->setRowTemplate("tpl.crs_verification_row.html", "Modules/Course");
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));

        $this->getItems();
    }

    /**
     * Get all completed tests
     */
    protected function getItems()
    {
        $ilUser = $this->dic->user();

        $data = array();

        $userId = $ilUser->getId();

        $certificateArray = $this->userCertificateRepository->fetchActiveCertificatesByTypeForPresentation($userId, 'crs');

        $data = array();
        foreach ($certificateArray as $certificate) {
            $data[] = array(
                'id' => $certificate->getUserCertificate()->getObjId(),
                'title' => $certificate->getObjectTitle(),
                'passed' => true
            );
        }

        $this->setData($data);
    }

    /**
     * Fill template row
     *
     * @param array $a_set
     */
    protected function fillRow($a_set)
    {
        $ilCtrl = $this->dic->ctrl();

        $this->tpl->setVariable("TITLE", $a_set["title"]);
        $this->tpl->setVariable("PASSED", ($a_set["passed"]) ? $this->lng->txt("yes") :
            $this->lng->txt("no"));

        if ($a_set["passed"]) {
            $ilCtrl->setParameter($this->parent_obj, "crs_id", $a_set["id"]);
            $action = $ilCtrl->getLinkTarget($this->parent_obj, "save");
            $this->tpl->setVariable("URL_SELECT", $action);
            $this->tpl->setVariable("TXT_SELECT", $this->lng->txt("select"));
        }
    }
}
