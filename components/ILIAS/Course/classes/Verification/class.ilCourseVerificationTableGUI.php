<?php

declare(strict_types=0);

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
 * List all completed course for current user
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ModulesCourse
 */
class ilCourseVerificationTableGUI extends ilTable2GUI
{
    private ?ilUserCertificateRepository $userCertificateRepository;
    private Container $dic;

    public function __construct(
        ilObjCourseVerificationGUI $a_parent_obj,
        string $a_parent_cmd = '',
        ilUserCertificateRepository $userCertificateRepository = null
    ) {
        global $DIC;

        $this->dic = $DIC;
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

        $this->setTitle($this->lng->txt('crsv_create'));
        $this->setDescription($this->lng->txt('crsv_create_info'));

        $this->setRowTemplate('tpl.crs_verification_row.html', 'Modules/Course');
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));

        $this->getItems();
    }

    protected function getItems(): void
    {
        $certificateArray = $this->userCertificateRepository->fetchActiveCertificatesByTypeForPresentation(
            $this->dic->user()->getId(),
            'crs'
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
        $this->dic->ctrl();

        $this->tpl->setVariable('TITLE', $a_set['title']);
        $this->tpl->setVariable(
            'PASSED',
            ($a_set['passed']) ? $this->lng->txt('yes') : $this->lng->txt('no')
        );

        if ($a_set['passed']) {
            $this->ctrl->setParameter($this->parent_obj, 'crs_id', $a_set['id']);
            $action = $this->ctrl->getLinkTarget($this->parent_obj, 'save');
            $this->tpl->setVariable('URL_SELECT', $action);
            $this->tpl->setVariable('TXT_SELECT', $this->lng->txt('select'));
        }
    }
}
