<?php declare(strict_types=1);
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilPCVerificationGUI
 *
 * Handles user commands on verifications
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $I$
 *
 * @ingroup ServicesCOPage
 */
class ilPCVerificationGUI extends ilPageContentGUI
{
    /** @var ilObjUser */
    protected $user;

    /**
     * @ineritdoc
     */
    public function __construct($a_pg_obj, $a_content_obj, $a_hier_id = 0, $a_pc_id = "")
    {
        global $DIC;

        $this->user = $DIC->user();
        parent::__construct($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id);
    }

    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();
        return $this->$cmd();
    }

    /**
     * @param ilPropertyFormGUI|null $a_form
     * @throws ilDateTimeException
     */
    public function insert(ilPropertyFormGUI $a_form = null) : void
    {
        $this->displayValidationError();

        if (!$a_form) {
            $a_form = $this->initForm(true);
        }
        $this->tpl->setContent($a_form->getHTML());
    }

    /**
     * @param ilPropertyFormGUI|null $a_form
     * @throws ilDateTimeException
     */
    public function edit(ilPropertyFormGUI $a_form = null) : void
    {
        $this->displayValidationError();

        if (!$a_form) {
            $a_form = $this->initForm();
        }
        $this->tpl->setContent($a_form->getHTML());
    }

    /**
     * @param false $a_insert
     * @return ilPropertyFormGUI
     * @throws ilDateTimeException
     */
    protected function initForm(bool $a_insert = false) : ilPropertyFormGUI
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

        $repository = new ilUserCertificateRepository();
        $certificates = $repository->fetchActiveCertificates($this->user->getId());
        $certificateOptions = [];
        foreach ($certificates as $certificate) {
            $userCertificate = $certificate->getUserCertificate();
            $dateTime = ilDatePresentation::formatDate(new ilDateTime($userCertificate->getAcquiredTimestamp(), IL_CAL_UNIX));

            $type = $this->lng->txt('wsp_type_' . $userCertificate->getObjType() . 'v');
            if ('sahs' === $userCertificate->getObjType()) {
                $type = $this->lng->txt('wsp_type_scov');
            }
            $additionalInformation = ' (' . $type . ' / ' . $dateTime . ')';
            $certificateOptions[$userCertificate->getObjId()] = $certificate->getObjectTitle() . $additionalInformation;
        }

        if ($a_insert || (is_array($data) && isset($data['type']) && 'crta' == $data['type'])) {
            $certificate = new ilSelectInputGUI($this->lng->txt('certificate'), 'persistent_object');
            $certificate->setRequired(true);
            $certificate->setOptions($certificateOptions);
            $form->addItem($certificate);
            if (is_array($data) && isset($data['id'])) {
                $certificate->setValue($data['id']);
            }

            return $form;
        }

        $workspaceOptions = [];
        $certificateSource = new ilRadioGroupInputGUI($this->lng->txt('certificate_selection'), 'certificate_selection');

        $workspaceRadioButton = new ilRadioOption($this->lng->txt('certificate_workspace_option'), 'certificate_workspace_option');
        $persistentRadioButton = new ilRadioOption($this->lng->txt('certificate_persistent_option'), 'certificate_persistent_option');
        $tree = new ilWorkspaceTree($this->user->getId());
        $root = $tree->getRootId();
        if ($root) {
            $root = $tree->getNodeData($root);
            foreach ($tree->getSubTree($root) as $node) {
                if (in_array($node['type'], ['excv', 'tstv', 'crsv', 'cmxv', 'ltiv', 'scov'])) {
                    $workspaceOptions[$node['obj_id']] = $node['title'] . ' (' . $this->lng->txt('wsp_type_' . $node['type']) . ')';
                }
            }
            asort($workspaceOptions);
        }

        $workspaceCertificates = new ilSelectInputGUI($this->lng->txt('cont_verification_object'), 'object');
        $workspaceCertificates->setRequired(true);
        $workspaceCertificates->setOptions($workspaceOptions);

        $certificate = new ilSelectInputGUI($this->lng->txt("cont_verification_object"), "persistent_object");
        $certificate->setRequired(true);
        $certificate->setOptions($certificateOptions);
        $persistentRadioButton->addSubItem($certificate);
        $workspaceRadioButton->addSubItem($workspaceCertificates);

        $certificateSource->addOption($persistentRadioButton);
        $certificateSource->addOption($workspaceRadioButton);
        $certificateSource->setValue('certificate_persistent_option');

        $form->addItem($certificateSource);

        if ($data['type'] === 'crta') {
            $certificateSource->setValue('certificate_persistent_option');
            $certificate->setValue($data['id']);
        } else {
            $certificateSource->setValue('certificate_workspace_option');
            $workspaceCertificates->setValue($data['id']);
        }

        return $form;
    }

    /**
     * @throws ilDateTimeException
     */
    public function create() : void
    {
        $form = $this->initForm(true);
        if ($form->checkInput()) {
            $objectId = (int) $form->getInput('persistent_object');
            $userId = (int) $this->user->getId();

            $certificateFileService = new ilPortfolioCertificateFileService();
            try {
                $certificateFileService->createCertificateFile($userId, $objectId);
            } catch (\ILIAS\Filesystem\Exception\FileAlreadyExistsException $e) {
                ilUtil::sendInfo($this->lng->txt('certificate_file_not_found_error'), true);
                $this->log->warning($e->getMessage());
            } catch (\ILIAS\Filesystem\Exception\IOException $e) {
                ilUtil::sendInfo($this->lng->txt('certificate_file_input_output_error'), true);
                $this->log->error($e->getMessage());
                $this->ctrl->redirect($this, 'initForm');
            } catch (ilException $e) {
                ilUtil::sendFailure($this->lng->txt('error_creating_certificate_pdf'), true);
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
     * @throws ilDateTimeException
     */
    public function update() : void
    {
        $form = $this->initForm(true);
        if ($form->checkInput()) {
            $option = $form->getInput('certificate_selection');
            if ('certificate_workspace_option' === $option) {
                $object = $form->getInput('object');
                $type = ilObject::_lookupType($object);
                if ($type) {
                    $oldContentData = $this->content_obj->getData();

                    if ('crta' === $oldContentData['type']) {
                        $userId = $this->user->getId();
                        $oldObjectId = $oldContentData['id'];

                        $certificateFileService = new ilPortfolioCertificateFileService();
                        try {
                            $certificateFileService->deleteCertificateFile($userId, $oldObjectId);
                        } catch (\ILIAS\Filesystem\Exception\FileNotFoundException $e) {
                            ilUtil::sendInfo($this->lng->txt('certificate_file_not_found_error'));
                            $this->log->warning($e->getMessage());
                        } catch (\ILIAS\Filesystem\Exception\IOException $e) {
                            ilUtil::sendInfo($this->lng->txt('certificate_file_input_output_error'));
                            $this->log->warning($e->getMessage());
                        }
                    }

                    $this->content_obj->setData($type, $object);
                    $this->updated = $this->pg_obj->update();
                    if ($this->updated === true) {
                        $this->ctrl->returnToParent($this, 'jump' . $this->hier_id);
                    }
                }
            } elseif ('certificate_persistent_option' === $option) {
                $oldContentData = $this->content_obj->getData();

                $objectId = $form->getInput('persistent_object');

                $certificateFileService = new ilPortfolioCertificateFileService();

                try {
                    $userId = $this->user->getId();

                    $certificateFileService->createCertificateFile($userId, (int) $objectId);
                    if ('crta' === $oldContentData['type']) {
                        $oldObjectId = $oldContentData['id'];
                        $certificateFileService->deleteCertificateFile($userId, $oldObjectId);
                    }
                } catch (\ILIAS\Filesystem\Exception\FileNotFoundException $e) {
                    ilUtil::sendInfo($this->lng->txt('certificate_file_not_found_error'), true);
                    $this->log->warning($e->getMessage());
                } catch (\ILIAS\Filesystem\Exception\FileAlreadyExistsException $e) {
                    ilUtil::sendInfo($this->lng->txt('certificate_file_not_found_error'), true);
                    $this->log->warning($e->getMessage());
                } catch (\ILIAS\Filesystem\Exception\IOException $e) {
                    ilUtil::sendInfo($this->lng->txt('certificate_file_input_output_error'), true);
                    $this->log->warning($e->getMessage());
                } catch (ilException $e) {
                    ilUtil::sendFailure($this->lng->txt('error_creating_certificate_pdf'), true);
                    $this->log->error($e->getMessage());
                    $this->ctrl->redirect($this, 'initForm');
                }

                $this->content_obj->setData('crta', $objectId);
                $this->updated = $this->pg_obj->update();
                if ($this->updated === true) {
                    $this->ctrl->returnToParent($this, 'jump' . $this->hier_id);
                }

                $this->log->info('File could not be created');
            }
        }

        $this->pg_obj->addHierIDs();
        $this->edit($form);
    }
}
