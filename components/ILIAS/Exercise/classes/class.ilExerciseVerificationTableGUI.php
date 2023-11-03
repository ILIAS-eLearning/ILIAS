<?php

declare(strict_types=1);

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
 * List all completed exercises for current user
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @author Alexander Killing <killing@leifos.de>
 */
class ilExerciseVerificationTableGUI extends ilTable2GUI
{
    private ?ilUserCertificateRepository $userCertificateRepository;
    protected ilObjUser $user;

    public function __construct(
        ilObjExerciseVerificationGUI $a_parent_obj,
        string $a_parent_cmd,
        ?ilUserCertificateRepository $userCertificateRepository = null
    ) {
        global $DIC;

        $this->user = $DIC->user();
        $database = $DIC->database();
        $logger = $DIC->logger()->root();
        $ilCtrl = $DIC->ctrl();

        if (null === $userCertificateRepository) {
            $userCertificateRepository = new ilUserCertificateRepository($database, $logger);
        }
        $this->userCertificateRepository = $userCertificateRepository;

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->addColumn($this->lng->txt('title'), 'title');
        $this->addColumn($this->lng->txt('passed'), 'passed');
        $this->addColumn($this->lng->txt('action'), '');

        $this->setTitle($this->lng->txt('excv_create'));
        $this->setDescription($this->lng->txt('excv_create_info'));

        $this->setRowTemplate('tpl.exc_verification_row.html', 'Modules/Exercise');
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));

        $this->getItems();
    }

    // Get all achieved test certificates for the current user
    protected function getItems(): void
    {
        $ilUser = $this->user;
        $userId = $ilUser->getId();

        $certificateArray = $this->userCertificateRepository
            ->fetchActiveCertificatesByTypeForPresentation($userId, 'exc');

        $data = [];

        foreach ($certificateArray as $certificate) {
            $data[] = [
                'id' => $certificate->getUserCertificate()->getObjId(),
                'title' => $certificate->getObjectTitle(),
                'passed' => true
            ];
        }

        $this->setData($data);
    }

    protected function fillRow(array $a_set): void
    {
        $ilCtrl = $this->ctrl;

        $this->tpl->setVariable('TITLE', $a_set['title']);
        $this->tpl->setVariable(
            'PASSED',
            ($a_set['passed']) ? $this->lng->txt('yes') : $this->lng->txt('no')
        );

        if ($a_set['passed']) {
            $ilCtrl->setParameter($this->parent_obj, 'exc_id', $a_set['id']);
            $action = $ilCtrl->getLinkTarget($this->parent_obj, 'save');
            $this->tpl->setVariable('URL_SELECT', $action);
            $this->tpl->setVariable('TXT_SELECT', $this->lng->txt('select'));
        }
    }
}
