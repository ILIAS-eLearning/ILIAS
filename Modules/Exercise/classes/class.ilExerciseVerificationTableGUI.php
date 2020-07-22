<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Table/classes/class.ilTable2GUI.php';

/**
 * List all completed exercises for current user
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ModulesExercise
 */
class ilExerciseVerificationTableGUI extends ilTable2GUI
{

    /**
     * @var ilUserCertificateRepository
     */
    private $userCertificateRepository;

    /**
     * @var ilObjUser
     */
    protected $user;

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

        $this->ctrl = $DIC->ctrl();
        $this->user = $DIC->user();
        $database = $DIC->database();
        $logger = $DIC->logger()->root();
        $ilCtrl = $DIC->ctrl();

        if (null === $userCertificateRepository) {
            $userCertificateRepository = new ilUserCertificateRepository($database, $logger);
        }
        $this->userCertificateRepository = $userCertificateRepository;

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->addColumn($this->lng->txt("title"), "title");
        $this->addColumn($this->lng->txt("passed"), "passed");
        $this->addColumn($this->lng->txt("action"), "");

        $this->setTitle($this->lng->txt("excv_create"));
        $this->setDescription($this->lng->txt("excv_create_info"));

        $this->setRowTemplate("tpl.exc_verification_row.html", "Modules/Exercise");
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));

        $this->getItems();
    }

    /**
     * Get all achieved test certificates for the current user
     */
    protected function getItems()
    {
        $ilUser = $this->user;
        $userId = $ilUser->getId();

        $certificateArray = $this->userCertificateRepository
            ->fetchActiveCertificatesByTypeForPresentation($userId, 'exc');

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
        $ilCtrl = $this->ctrl;

        $this->tpl->setVariable("TITLE", $a_set["title"]);
        $this->tpl->setVariable("PASSED", ($a_set["passed"]) ? $this->lng->txt("yes") :
            $this->lng->txt("no"));

        if ($a_set["passed"]) {
            $ilCtrl->setParameter($this->parent_obj, "exc_id", $a_set["id"]);
            $action = $ilCtrl->getLinkTarget($this->parent_obj, "save");
            $this->tpl->setVariable("URL_SELECT", $action);
            $this->tpl->setVariable("TXT_SELECT", $this->lng->txt("select"));
        }
    }
}
