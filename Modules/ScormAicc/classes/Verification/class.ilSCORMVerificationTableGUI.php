<?php declare(strict_types=1);
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

/**
 * List all completed learning modules for current user
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ModulesScormAicc
 */
class ilSCORMVerificationTableGUI extends ilTable2GUI
{
    private ilUserCertificateRepository $userCertificateRepository;

    /**
     * @throws ilCtrlException
     */
    public function __construct(
        ilObjSCORMVerificationGUI $a_parent_obj,
        string $a_parent_cmd = '',
        ?ilUserCertificateRepository $userCertificateRepository = null
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

        $this->addColumn($this->lng->txt('title'), 'title');
        $this->addColumn($this->lng->txt('passed'), 'passed');
        $this->addColumn($this->lng->txt('action'), '');

        $this->setTitle($this->lng->txt('scov_create'));
        $this->setDescription($this->lng->txt('scov_create_info'));

        $this->setRowTemplate('tpl.sahs_verification_row.html', 'Modules/ScormAicc');
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));

        $this->getItems();
    }

    protected function getItems() : void
    {
        global $DIC;

        $ilUser = $DIC->user();

        $userId = $ilUser->getId();

        $certificateArray = $this->userCertificateRepository->fetchActiveCertificatesByTypeForPresentation(
            $userId,
            'sahs'
        );

        $data = [];
        foreach ($certificateArray as $certificate) {
            $user_cert = $certificate->getUserCertificate();
            if ($user_cert !== null) {
                $data[] = [
                    'id' => $user_cert->getObjId(),
                    'title' => $certificate->getObjectTitle(),
                    'passed' => true
                ];
            }
        }

        $this->setData($data);
    }

    /**
     * @throws ilCtrlException
     */
    protected function fillRow(array $a_set) : void
    {
        global $DIC;
        $ilCtrl = $DIC->ctrl();

        $this->tpl->setVariable('TITLE', $a_set['title']);
        $this->tpl->setVariable(
            'PASSED',
            ($a_set['passed']) ? $this->lng->txt('yes') : $this->lng->txt('no')
        );

        if ($a_set['passed']) {
            $ilCtrl->setParameter($this->parent_obj, 'lm_id', $a_set['id']);
            $action = $ilCtrl->getLinkTarget($this->parent_obj, 'save');
            $this->tpl->setVariable('URL_SELECT', $action);
            $this->tpl->setVariable('TXT_SELECT', $this->lng->txt('select'));
        }
    }
}
