<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

require_once("./Services/COPage/classes/class.ilPCVerification.php");
require_once("./Services/COPage/classes/class.ilPageContentGUI.php");

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
    /**
     * @var ilObjUser
     */
    protected $user;


    /**
    * Constructor
    * @access	public
    */
    public function __construct(&$a_pg_obj, &$a_content_obj, $a_hier_id, $a_pc_id = "")
    {
        global $DIC;

        $this->tpl = $DIC["tpl"];
        $this->ctrl = $DIC->ctrl();
        $this->user = $DIC->user();
        $this->lng = $DIC->language();
        parent::__construct($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id);
    }

    /**
    * execute command
    */
    public function executeCommand()
    {
        // get next class that processes or forwards current command
        $next_class = $this->ctrl->getNextClass($this);

        // get current command
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            default:
                $ret = $this->$cmd();
                break;
        }

        return $ret;
    }

    /**
     * Insert new verification form.
     *
     * @param ilPropertyFormGUI $a_form
     */
    public function insert(ilPropertyFormGUI $a_form = null)
    {
        $tpl = $this->tpl;

        $this->displayValidationError();

        if (!$a_form) {
            $a_form = $this->initForm(true);
        }
        $tpl->setContent($a_form->getHTML());
    }

    /**
     * Edit verification form.
     *
     * @param ilPropertyFormGUI $a_form
     */
    public function edit(ilPropertyFormGUI $a_form = null)
    {
        $tpl = $this->tpl;

        $this->displayValidationError();

        if (!$a_form) {
            $a_form = $this->initForm();
        }
        $tpl->setContent($a_form->getHTML());
    }

    /**
     * Init verification form
     *
     * @param bool $a_insert
     * @return ilPropertyFormGUI
     * @throws ilDateTimeException
     */
    protected function initForm($a_insert = false)
    {
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;
        $lng = $this->lng;

        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        if ($a_insert) {
            $form->setTitle($this->lng->txt("cont_insert_verification"));
        } else {
            $form->setTitle($this->lng->txt("cont_update_verification"));
        }

        $lng->loadLanguageModule("wsp");
        $workspaceOptions = array();

        $certificateSource = new ilRadioGroupInputGUI($this->lng->txt('certificate_selection'), 'certificate_selection');

        $workspaceRadioButton = new ilRadioOption($this->lng->txt('certificate_workspace_option'), 'certificate_workspace_option');
        $persistentRadioButton = new ilRadioOption($this->lng->txt('certificate_persistent_option'), 'certificate_persistent_option');

        $tree = new ilWorkspaceTree($ilUser->getId());
        $root = $tree->getRootId();
        if ($root) {
            $root = $tree->getNodeData($root);
            foreach ($tree->getSubTree($root) as $node) {
                if (in_array($node["type"], array("excv", "tstv", "crsv", "scov"))) {
                    $workspaceOptions[$node["obj_id"]] = $node["title"] . " (" . $lng->txt("wsp_type_" . $node["type"]) . ")";
                }
            }
            asort($workspaceOptions);
        }

        $workspaceCertificates = new ilSelectInputGUI($this->lng->txt("cont_verification_object"), "object");
        $workspaceCertificates->setRequired(true);
        $workspaceCertificates->setOptions($workspaceOptions);

        $repository = new ilUserCertificateRepository();

        $certificates = $repository->fetchActiveCertificates($ilUser->getId());

        $persistentOptions = array();
        foreach ($certificates as $certificate) {
            $userCertificate = $certificate->getUserCertificate();
            $dateTime = ilDatePresentation::formatDate(new ilDateTime($userCertificate->getAcquiredTimestamp(), IL_CAL_UNIX));

            $type = $lng->txt("wsp_type_" . $userCertificate->getObjType() . 'v');
            $additionalInformation = ' (' . $type . ' / ' . $dateTime . ')';
            $persistentOptions[$userCertificate->getObjId()] = $certificate->getObjectTitle() . $additionalInformation;
        }

        $persistentObject = new ilSelectInputGUI($this->lng->txt("cont_verification_object"), "persistent_object");
        $persistentObject->setRequired(true);
        $persistentObject->setOptions($persistentOptions);

        $persistentRadioButton->addSubItem($persistentObject);
        $workspaceRadioButton->addSubItem($workspaceCertificates);

        $certificateSource->addOption($persistentRadioButton);
        $certificateSource->addOption($workspaceRadioButton);

        $certificateSource->setValue('certificate_persistent_option');

        $form->addItem($certificateSource);

        if ($a_insert) {
            $form->addCommandButton("create_verification", $this->lng->txt("save"));
            $form->addCommandButton("cancelCreate", $this->lng->txt("cancel"));
        } else {
            $data = $this->content_obj->getData();

            if ($data['type'] === 'crta') {
                $certificateSource->setValue('certificate_persistent_option');
                $persistentObject->setValue($data["id"]);
            } else {
                $certificateSource->setValue('certificate_workspace_option');
                $workspaceCertificates->setValue($data["id"]);
            }


            $form->addCommandButton("update", $this->lng->txt("save"));
            $form->addCommandButton("cancelUpdate", $this->lng->txt("cancel"));
        }

        return $form;
    }

    /**
     * Create new verification
     * @throws ilException
     */
    public function create()
    {
        $form = $this->initForm(true);
        if ($form->checkInput()) {
            $option = $form->getInput('certificate_selection');

            if ('certificate_workspace_option' === $option) {
                $type = ilObject::_lookupType($form->getInput("object"));
                if ($type) {
                    $this->content_obj = new ilPCVerification($this->getPage());
                    $this->content_obj->create($this->pg_obj, $this->hier_id, $this->pc_id);
                    $verificationObjectId = $form->getInput("object");

                    $this->content_obj->setData($type, $verificationObjectId);

                    $this->updated = $this->pg_obj->update();
                    if ($this->updated === true) {
                        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
                    }
                }
            } elseif ('certificate_persistent_option' === $option) {
                $objectId = $form->getInput("persistent_object");

                $userId = $this->user->getId();

                $certificateFileService = new ilPortfolioCertificateFileService();
                try {
                    $certificateFileService->createCertificateFile($userId, $objectId);
                } catch (\ILIAS\Filesystem\Exception\FileAlreadyExistsException $e) {
                    ilUtil::sendInfo($this->lng->txt('certificate_file_not_found_error'), true);
                    $this->log->warning($e->getMessage());
                } catch (\ILIAS\Filesystem\Exception\IOException $e) {
                    ilUtil::sendInfo($this->lng->txt('certificate_file_input_output_error'), true);
                    $this->log->error($e->getMessage());
                    return $this->ctrl->redirect($this, 'initForm');
                } catch (ilException $e) {
                    ilUtil::sendFailure($this->lng->txt('error_creating_certificate_pdf'), true);
                    $this->log->error($e->getMessage());
                    return $this->ctrl->redirect($this, 'initForm');
                }

                $this->content_obj = new ilPCVerification($this->getPage());
                $this->content_obj->create($this->pg_obj, $this->hier_id, $this->pc_id);
                $this->content_obj->setData('crta', $objectId);

                $this->updated = $this->pg_obj->update();
                if ($this->updated === true) {
                    $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
                }

                $this->log->info('File could not be created');
            }
        }

        $this->insert($form);
    }

    /**
    * Update verification
    */
    public function update()
    {
        $form = $this->initForm(true);
        if ($form->checkInput()) {
            $option = $form->getInput('certificate_selection');
            if ('certificate_workspace_option' === $option) {
                $object = $form->getInput("object");
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
                        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
                    }
                }
            } elseif ('certificate_persistent_option' === $option) {
                $oldContentData = $this->content_obj->getData();

                $objectId = $form->getInput("persistent_object");

                $certificateFileService = new ilPortfolioCertificateFileService();

                try {
                    $userId = $this->user->getId();

                    $certificateFileService->createCertificateFile($userId, $objectId);
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
                    return $this->ctrl->redirect($this, 'initForm');
                }

                $this->content_obj->setData('crta', $objectId);
                $this->updated = $this->pg_obj->update();
                if ($this->updated === true) {
                    $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
                }

                $this->log->info('File could not be created');
            }
        }

        $this->pg_obj->addHierIDs();
        $this->edit($form);
    }

    private function initStorage(int $objectId, string $subDirectory = '')
    {
        $storage = new ilVerificationStorageFile($objectId);
        $storage->create();

        $path = $storage->getAbsolutePath() . "/";

        if ($subDirectory !== '') {
            $path .= $subDirectory . "/";

            if (!is_dir($path)) {
                mkdir($path);
            }
        }

        return $path;
    }
}
