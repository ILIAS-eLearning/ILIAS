<?php

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

declare(strict_types=1);

/**
 * List all completed tests for current user
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup components\ILIASTest
 */
class ilTestVerificationTableGUI extends ilTable2GUI
{
    private ilUserCertificateRepository $userCertificateRepository;

    public function __construct(
        ilObjTestVerificationGUI $parent_obj,
        string $parent_cmd,
        private ilDBInterface $db,
        private ilObjUser $user,
        private ilLogger $logger
    ) {
        $user_certificate_repository = new ilUserCertificateRepository($this->db, $this->logger);
        $this->userCertificateRepository = $user_certificate_repository;

        parent::__construct($parent_obj, $parent_cmd);

        $this->addColumn($this->lng->txt('title'), 'title');
        $this->addColumn($this->lng->txt('passed'), 'passed');
        $this->addColumn($this->lng->txt('action'), '');

        $this->setTitle($this->lng->txt('tstv_create'));
        $this->setDescription($this->lng->txt('tstv_create_info'));

        $this->setRowTemplate('tpl.il_test_verification_row.html', 'components/ILIAS/Test');
        $this->setFormAction($this->ctrl->getFormAction($parent_obj, $parent_cmd));

        $this->getItems();
    }

    protected function getItems(): void
    {
        $userId = $this->user->getId();

        $certificateArray = $this->userCertificateRepository->fetchActiveCertificatesByTypeForPresentation(
            $userId,
            'tst'
        );

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
        $this->tpl->setVariable('TITLE', $a_set['title']);
        $this->tpl->setVariable(
            'PASSED',
            ($a_set['passed']) ? $this->lng->txt('yes') : $this->lng->txt('no')
        );

        if ($a_set['passed']) {
            $this->ctrl->setParameter($this->parent_obj, 'tst_id', $a_set['id']);
            $action = $this->ctrl->getLinkTarget($this->parent_obj, 'save');
            $this->tpl->setVariable('URL_SELECT', $action);
            $this->tpl->setVariable('TXT_SELECT', $this->lng->txt('select'));
        }
    }
}
