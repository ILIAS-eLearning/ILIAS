<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Table/classes/class.ilTable2GUI.php';

/**
 * List all completed tests for current user
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ModulesTest
 */
class ilTestVerificationTableGUI extends ilTable2GUI
{
    /**
     * @var ilUserCertificateRepository|null
     */
    private $userCertificateRepository;

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

        $this->setTitle($this->lng->txt("tstv_create"));
        $this->setDescription($this->lng->txt("tstv_create_info"));

        $this->setRowTemplate("tpl.il_test_verification_row.html", "Modules/Test");
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));

        $this->getItems();
    }

    /**
     * Get all completed tests
     */
    protected function getItems()
    {
        global $DIC;

        $ilUser = $DIC->user();

        $userId = $ilUser->getId();

        $certificateArray = $this->userCertificateRepository->fetchActiveCertificatesByTypeForPresentation($userId, 'tst');

        $data = array();
        foreach ($certificateArray as $certificate) {
            $data[] = array(
                'id'     => $certificate->getUserCertificate()->getObjId(),
                'title'  => $certificate->getObjectTitle(),
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
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];

        $this->tpl->setVariable("TITLE", $a_set["title"]);
        $this->tpl->setVariable("PASSED", ($a_set["passed"]) ? $this->lng->txt("yes") :
            $this->lng->txt("no"));

        if ($a_set["passed"]) {
            $ilCtrl->setParameter($this->parent_obj, "tst_id", $a_set["id"]);
            $action = $ilCtrl->getLinkTarget($this->parent_obj, "save");
            $this->tpl->setVariable("URL_SELECT", $action);
            $this->tpl->setVariable("TXT_SELECT", $this->lng->txt("select"));
        }
    }
}
