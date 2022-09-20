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
 * Class ilPCVerificationGUI
 * Handles user commands on verifications
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilPCVerificationGUI extends ilPageContentGUI
{
    private const SUPPORTED_TYPES = ['excv', 'tstv', 'crsv', 'cmxv', 'ltiv', 'scov'];
    protected ilObjUser $user;

    public function __construct(
        ilPageObject $a_pg_obj,
        ?ilPCVerification $a_content_obj,
        string $a_hier_id = '0',
        string $a_pc_id = ""
    ) {
        global $DIC;

        $this->user = $DIC->user();
        parent::__construct($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id);
    }

    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd();
        $this->$cmd();
    }

    public function insert(?ilPropertyFormGUI $a_form = null): void
    {
        $this->displayValidationError();

        if (!$a_form) {
            $a_form = $this->initForm(true);
        }
        $this->tpl->setContent($a_form->getHTML());
    }

    public function edit(?ilPropertyFormGUI $a_form = null): void
    {
        $this->displayValidationError();

        if (!$a_form) {
            $a_form = $this->initForm();
        }
        $this->tpl->setContent($a_form->getHTML());
    }

    /**
     * @return array<int, array>
     */
    private function getValidWorkspaceCertificateNodeByIdMap(): array
    {
        $nodes = [];

        $tree = new ilWorkspaceTree($this->user->getId());
        $root = $tree->getRootId();
        if ($root) {
            $root = $tree->getNodeData($root);
            foreach ($tree->getSubTree($root) as $node) {
                if (in_array($node['type'], self::SUPPORTED_TYPES, true)) {
                    $nodes[$node['obj_id']] = $node;
                }
            }
        }

        return $nodes;
    }

    /**
     * @return array<int, ilUserCertificatePresentation>
     * @throws JsonException
     */
    private function getValidCertificateByIdMap(): array
    {
        $certificates = [];

        $repository = new ilUserCertificateRepository();
        $activeCertificates = $repository->fetchActiveCertificates($this->user->getId());
        foreach ($activeCertificates as $certificate) {
            $certificates[$certificate->getObjId()] = $certificate;
        }

        return $certificates;
    }

    protected function initForm(bool $a_insert = false): ilPropertyFormGUI
    {
        $this->lng->loadLanguageModule('wsp');

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));

        $data = [];
        if ($a_insert) {
            $form->setTitle($this->lng->txt('cont_insert_verification'));
            $form->addCommandButton('create_verification', $this->lng->txt('save'));
            $form->addCommandButton('cancelCreate', $this->lng->txt('cancel'));
        } else {
            $form->setTitle($this->lng->txt('cont_update_verification'));
            $form->addCommandButton('update', $this->lng->txt('save'));
            $form->addCommandButton('cancelUpdate', $this->lng->txt('cancel'));
            $data = $this->content_obj->getData();
        }

        $certificateOptions = [];
        foreach ($this->getValidCertificateByIdMap() as $certificate) {
            $userCertificate = $certificate->getUserCertificate();
            $dateTime = ilDatePresentation::formatDate(new ilDateTime(
                $userCertificate->getAcquiredTimestamp(),
                IL_CAL_UNIX
            ));

            $type = $this->lng->txt('wsp_type_' . $userCertificate->getObjType() . 'v');
            if ('sahs' === $userCertificate->getObjType()) {
                $type = $this->lng->txt('wsp_type_scov');
            }
            $additionalInformation = ' (' . $type . ' / ' . $dateTime . ')';
            $certificateOptions[$userCertificate->getObjId()] = $certificate->getObjectTitle() . $additionalInformation;
        }

        if ($a_insert || (isset($data['type']) && 'crta' === $data['type'])) {
            $certificate = new ilSelectInputGUI($this->lng->txt('certificate'), 'persistent_object');
            $certificate->setRequired(true);
            $certificate->setOptions($certificateOptions);
            $form->addItem($certificate);
            if (isset($data['id'])) {
                $certificate->setValue($data['id']);
            }

            return $form;
        }

        $certificateSource = new ilRadioGroupInputGUI(
            $this->lng->txt('certificate_selection'),
            'certificate_selection'
        );

        $workspaceRadioButton = new ilRadioOption(
            $this->lng->txt('certificate_workspace_option'),
            'certificate_workspace_option'
        );
        $persistentRadioButton = new ilRadioOption(
            $this->lng->txt('certificate_persistent_option'),
            'certificate_persistent_option'
        );

        $workspaceCertificates = new ilSelectInputGUI($this->lng->txt('cont_verification_object'), 'object');
        $workspaceCertificates->setRequired(true);
        $workspaceOptions = [];
        foreach ($this->getValidWorkspaceCertificateNodeByIdMap() as $node) {
            $workspaceOptions[$node['obj_id']] = $node['title'] . ' (' . $this->lng->txt('wsp_type_' . $node['type']) . ')';
        }
        asort($workspaceOptions);
        $workspaceCertificates->setOptions($workspaceOptions);

        $certificate = new ilSelectInputGUI($this->lng->txt('cont_verification_object'), 'persistent_object');
        $certificate->setRequired(true);
        $certificate->setOptions($certificateOptions);
        $persistentRadioButton->addSubItem($certificate);
        $workspaceRadioButton->addSubItem($workspaceCertificates);

        $certificateSource->addOption($persistentRadioButton);
        $certificateSource->addOption($workspaceRadioButton);

        $form->addItem($certificateSource);

        $certificateSource->setValue('certificate_workspace_option');
        $workspaceCertificates->setValue($data['id']);

        return $form;
    }

    public function create(): void
    {
        $form = $this->initForm(true);
        if ($form->checkInput()) {
            $objectId = (int) $form->getInput('persistent_object');
            $userId = $this->user->getId();

            $certificateFileService = new ilPortfolioCertificateFileService();
            try {
                $certificateFileService->createCertificateFile($userId, $objectId);
            } catch (\ILIAS\Filesystem\Exception\FileAlreadyExistsException $e) {
                $this->tpl->setOnScreenMessage('info', $this->lng->txt('certificate_file_not_found_error'), true);
                $this->log->warning($e->getMessage());
            } catch (\ILIAS\Filesystem\Exception\IOException $e) {
                $this->tpl->setOnScreenMessage('info', $this->lng->txt('certificate_file_input_output_error'), true);
                $this->log->error($e->getMessage());
                $this->ctrl->redirect($this, 'initForm');
            } catch (ilException $e) {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('error_creating_certificate_pdf'), true);
                $this->log->error($e->getMessage());
                $this->ctrl->redirect($this, 'initForm');
            }

            $this->content_obj = new ilPCVerification($this->getPage());
            $this->content_obj->create($this->pg_obj, $this->hier_id, $this->pc_id);
            $this->content_obj->setData('crta', $objectId);

            $this->updated = $this->pg_obj->update();
            if ($this->updated === true) {
                $this->ctrl->returnToParent($this, 'jump' . $this->hier_id);
            }

            $this->log->info('File could not be created');
        }

        $this->insert($form);
    }

    /**
     * @throws JsonException
     * @throws ilDateTimeException
     */
    public function update(): void
    {
        $form = $this->initForm();
        if ($form->checkInput()) {
            $option = 'certificate_persistent_option';
            if ($form->getItemByPostVar('certificate_selection')) {
                $option = $form->getInput('certificate_selection');
            }

            $oldContentData = $this->content_obj->getData();

            if ('certificate_workspace_option' === $option) {
                $objectId = (int) $form->getInput('object');
                $validWorkSpaceCertificates = $this->getValidWorkspaceCertificateNodeByIdMap();

                if (isset($validWorkSpaceCertificates[$objectId])) {
                    $this->content_obj->setData($validWorkSpaceCertificates[$objectId]['type'], $objectId);
                }
            } elseif ('certificate_persistent_option' === $option) {
                try {
                    $objectId = (int) $form->getInput('persistent_object');
                    $validCertificates = $this->getValidCertificateByIdMap();

                    if (isset($validCertificates[$objectId])) {
                        $certificateFileService = new ilPortfolioCertificateFileService();
                        $certificateFileService->createCertificateFile(
                            $this->user->getId(),
                            $objectId
                        );
                        $this->content_obj->setData('crta', $objectId);
                    }
                } catch (\ILIAS\Filesystem\Exception\FileNotFoundException | \ILIAS\Filesystem\Exception\FileAlreadyExistsException $e) {
                    $this->tpl->setOnScreenMessage('info', $this->lng->txt('certificate_file_not_found_error'), true);
                    $this->log->warning($e->getMessage());
                } catch (\ILIAS\Filesystem\Exception\IOException $e) {
                    $this->tpl->setOnScreenMessage('info', $this->lng->txt('certificate_file_input_output_error'), true);
                    $this->log->warning($e->getMessage());
                } catch (ilException $e) {
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt('error_creating_certificate_pdf'), true);
                    $this->log->error($e->getMessage());
                    $this->ctrl->redirect($this, 'initForm');
                }
            }

            if ('crta' === $oldContentData['type']) {
                try {
                    $certificateFileService = new ilPortfolioCertificateFileService();
                    $certificateFileService->deleteCertificateFile(
                        $this->user->getId(),
                        (int) $oldContentData['id']
                    );
                } catch (\ILIAS\Filesystem\Exception\FileNotFoundException $e) {
                    $this->tpl->setOnScreenMessage('info', $this->lng->txt('certificate_file_not_found_error'));
                    $this->log->warning($e->getMessage());
                } catch (\ILIAS\Filesystem\Exception\IOException $e) {
                    $this->tpl->setOnScreenMessage('info', $this->lng->txt('certificate_file_input_output_error'));
                    $this->log->warning($e->getMessage());
                }
            }

            $this->updated = $this->pg_obj->update();
            if ($this->updated === true) {
                $this->ctrl->returnToParent($this, 'jump' . $this->hier_id);
            }
        }

        $this->pg_obj->addHierIDs();
        $this->edit($form);
    }
}
