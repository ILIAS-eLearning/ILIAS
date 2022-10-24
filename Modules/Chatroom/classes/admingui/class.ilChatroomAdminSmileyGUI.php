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
 * Class ilChatroomAdminSmileyGUI
 * Provides methods to show, add, edit and delete smilies
 * consisting of icon and keywords
 * @author  Andreas Kordosz <akordosz@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilChatroomAdminSmileyGUI extends ilChatroomGUIHandler
{
    protected ?ilPropertyFormGUI $form_gui = null;

    public function executeDefault(string $requestedMethod): void
    {
        $this->view();
    }

    /**
     * Switches GUI to visible mode and calls editSmiliesObject method
     * which prepares and displays the table of existing smilies.
     */
    public function view(): void
    {
        ilChatroom::checkUserPermissions('read', $this->gui->getRefId());

        $this->gui->switchToVisibleMode();

        self::_checkSetup();

        $this->editSmiliesObject();
    }

    public static function _checkSetup(): bool
    {
        global $DIC;
        $main_tpl = $DIC->ui()->mainTemplate();

        $path = self::_getSmileyDir();

        if (!is_dir($path)) {
            $main_tpl->setOnScreenMessage('info', $DIC->language()->txt('chat_smilies_dir_not_exists'));
            ilFileUtils::makeDirParents($path);

            if (!is_dir($path)) {
                $main_tpl->setOnScreenMessage('failure', $DIC->language()->txt('chat_smilies_dir_not_available'));
                return false;
            }

            $smilies = [
                'icon_smile.gif',
                'icon_wink.gif',
                'icon_laugh.gif',
                'icon_sad.gif',
                'icon_shocked.gif',
                'icon_tongue.gif',
                'icon_cool.gif',
                'icon_eek.gif',
                'icon_angry.gif',
                'icon_flush.gif',
                'icon_idea.gif',
                'icon_thumbup.gif',
                'icon_thumbdown.gif',
            ];

            foreach ($smilies as $smiley) {
                copy("templates/default/images/emoticons/$smiley", $path . "/$smiley");
            }

            self::_insertDefaultValues();

            $main_tpl->setOnScreenMessage('success', $DIC->language()->txt('chat_smilies_initialized'));
        }

        if (!is_writable($path)) {
            $main_tpl->setOnScreenMessage('info', $DIC->language()->txt('chat_smilies_dir_not_writable'));
        }

        return true;
    }

    public static function _getSmileyDir(bool $withBaseDir = true): string
    {
        $path = 'chatroom/smilies';

        if ($withBaseDir) {
            $path = ilFileUtils::getWebspaceDir() . '/' . $path;
        }

        return $path;
    }

    private static function _insertDefaultValues(): void
    {
        $values = [
            ["icon_smile.gif", ":)\n:-)\n:smile:"],
            ["icon_wink.gif", ";)\n;-)\n:wink:"],
            ["icon_laugh.gif", ":D\n:-D\n:laugh:\n:grin:\n:biggrin:"],
            ["icon_sad.gif", ":(\n:-(\n:sad:"],
            ["icon_shocked.gif", ":o\n:-o\n:shocked:"],
            ["icon_tongue.gif", ":p\n:-p\n:tongue:"],
            ["icon_cool.gif", ":cool:"],
            ["icon_eek.gif", ":eek:"],
            ["icon_angry.gif", ":||\n:-||\n:angry:"],
            ["icon_flush.gif", ":flush:"],
            ["icon_idea.gif", ":idea:"],
            ["icon_thumbup.gif", ":thumbup:"],
            ["icon_thumbdown.gif", ":thumbdown:"],
        ];

        foreach ($values as $val) {
            ilChatroomSmilies::_storeSmiley($val[1], $val[0]);
        }
    }

    /**
     * Shows existing smilies table
     * Prepares existing smilies table and displays it.
     */
    public function editSmiliesObject(): void
    {
        if (!$this->rbacsystem->checkAccess('read', $this->gui->getRefId())) {
            $this->ilias->raiseError(
                $this->ilLng->txt('msg_no_perm_read'),
                $this->ilias->error_obj->MESSAGE
            );
        }

        ilChatroomSmilies::_checkSetup();

        if (null === $this->form_gui) {
            $this->form_gui = $this->initSmiliesForm();
        }

        $table = ilChatroomSmiliesGUI::_getExistingSmiliesTable($this->gui);

        $tpl_smilies = new ilTemplate(
            'tpl.chatroom_edit_smilies.html',
            true,
            true,
            'Modules/Chatroom'
        );
        $tpl_smilies->setVariable('SMILEY_TABLE', $table);
        $tpl_smilies->setVariable('SMILEY_FORM', $this->form_gui->getHTML());

        $this->mainTpl->setContent($tpl_smilies->get());
    }

    public function initSmiliesForm(): ilPropertyFormGUI
    {
        global $DIC;

        $this->form_gui = new ilPropertyFormGUI();

        if ($this->http->wrapper()->query()->has('_table_nav')) {
            $this->ilCtrl->setParameter(
                $this->gui,
                '_table_nav',
                $this->http->wrapper()->query()->retrieve('_table_nav', $this->refinery->kindlyTo()->string())
            );
        }
        $this->form_gui->setFormAction($this->ilCtrl->getFormAction($this->gui, 'smiley-uploadSmileyObject'));

        $sec_l = new ilFormSectionHeaderGUI();

        $sec_l->setTitle($this->ilLng->txt('chatroom_add_smiley'));
        $this->form_gui->addItem($sec_l);

        $inp = new ilImageFileInputGUI(
            $this->ilLng->txt('chatroom_image_path'),
            'chatroom_image_path'
        );
        $inp->setSuffixes(['jpg', 'jpeg', 'png', 'gif', 'svg']);

        $inp->setRequired(true);
        $this->form_gui->addItem($inp);

        $inp = new ilTextAreaInputGUI(
            $this->ilLng->txt('chatroom_smiley_keywords'),
            'chatroom_smiley_keywords'
        );

        $inp->setRequired(true);
        $inp->setUseRte(false);
        $inp->setInfo($this->ilLng->txt('chatroom_smiley_keywords_one_per_line_note'));
        $this->form_gui->addItem($inp);


        if ($this->rbacsystem->checkAccess('write', $this->gui->getRefId())) {
            $this->form_gui->addCommandButton(
                'smiley-uploadSmileyObject',
                $DIC->language()->txt('chatroom_upload_smiley')
            );
        }

        return $this->form_gui;
    }

    /**
     * Shows EditSmileyEntryForm
     * Prepares EditSmileyEntryForm and displays it.
     */
    public function showEditSmileyEntryFormObject(): void
    {
        $this->gui->switchToVisibleMode();

        if (!$this->rbacsystem->checkAccess('read', $this->gui->getRefId())) {
            $this->ilias->raiseError(
                $this->ilLng->txt('msg_no_perm_read'),
                $this->ilias->error_obj->MESSAGE
            );
        }

        $smileyId = $this->getRequestValue('smiley_id', $this->refinery->kindlyTo()->int());

        if (null === $this->form_gui) {
            $this->form_gui = $this->initSmiliesEditForm($this->getSmileyFormDataById($smileyId));
        }

        $tpl_form = new ilTemplate(
            'tpl.chatroom_edit_smilies.html',
            true,
            true,
            'Modules/Chatroom'
        );

        $tpl_form->setVariable('SMILEY_FORM', $this->form_gui->getHTML());

        $this->mainTpl->setContent($tpl_form->get());
    }

    /**
     * @param int $smileyId
     * @return array{chatroom_smiley_id: int, chatroom_smiley_keywords: string, chatroom_current_smiley_image_path: string}
     */
    protected function getSmileyFormDataById(int $smileyId): array
    {
        $smiley = ilChatroomSmilies::_getSmiley($smileyId);

        return [
            'chatroom_smiley_id' => $smiley['smiley_id'],
            'chatroom_smiley_keywords' => $smiley['smiley_keywords'],
            'chatroom_current_smiley_image_path' => $smiley['smiley_fullpath'],
        ];
    }

    /**
     * @param array<string, mixed> $form_data
     * @return ilPropertyFormGUI
     */
    public function initSmiliesEditForm(array $form_data): ilPropertyFormGUI
    {
        $this->form_gui = new ilPropertyFormGUI();
        $this->form_gui->setValuesByArray($form_data);

        if ($this->http->wrapper()->query()->has('_table_nav')) {
            $this->ilCtrl->setParameter(
                $this->gui,
                '_table_nav',
                $this->http->wrapper()->query()->retrieve('_table_nav', $this->refinery->kindlyTo()->string())
            );
        }

        $this->ilCtrl->saveParameter($this->gui, 'smiley_id');
        $this->form_gui->setFormAction($this->ilCtrl->getFormAction($this->gui, 'smiley-updateSmiliesObject'));

        $sec_l = new ilFormSectionHeaderGUI();

        $sec_l->setTitle($this->ilLng->txt('chatroom_edit_smiley'));
        $this->form_gui->addItem($sec_l);

        $inp = new ilChatroomSmiliesCurrentSmileyFormElement(
            $this->ilLng->txt('chatroom_current_smiley_image_path'),
            'chatroom_current_smiley_image_path'
        );

        $inp->setValue($form_data['chatroom_current_smiley_image_path']);
        $this->form_gui->addItem($inp);

        $inp = new ilImageFileInputGUI(
            $this->ilLng->txt('chatroom_image_path'),
            'chatroom_image_path'
        );
        $inp->setSuffixes(['jpg', 'jpeg', 'png', 'gif', 'svg']);

        $inp->setRequired(false);
        $inp->setInfo($this->ilLng->txt('chatroom_smiley_image_only_if_changed'));
        $this->form_gui->addItem($inp);

        $inp = new ilTextAreaInputGUI(
            $this->ilLng->txt('chatroom_smiley_keywords'),
            'chatroom_smiley_keywords'
        );

        $inp->setValue($form_data['chatroom_smiley_keywords']);
        $inp->setUseRte(false);
        $inp->setRequired(true);
        $inp->setInfo($this->ilLng->txt('chatroom_smiley_keywords_one_per_line_note'));
        $this->form_gui->addItem($inp);

        $inp = new ilHiddenInputGUI('chatroom_smiley_id');

        $this->form_gui->addItem($inp);
        $this->form_gui->addCommandButton(
            'smiley-updateSmiliesObject',
            $this->ilLng->txt('submit')
        );
        $this->form_gui->addCommandButton('smiley', $this->ilLng->txt('cancel'));

        return $this->form_gui;
    }

    /**
     * Shows DeleteSmileyForm
     * Prepares DeleteSmileyForm and displays it.
     */
    public function showDeleteSmileyFormObject(): void
    {
        $this->gui->switchToVisibleMode();

        if (!$this->rbacsystem->checkAccess('write', $this->gui->getRefId())) {
            $this->ilias->raiseError(
                $this->ilLng->txt('msg_no_perm_write'),
                $this->ilias->error_obj->MESSAGE
            );
        }

        $smileyId = $this->getRequestValue('smiley_id', $this->refinery->kindlyTo()->int());

        $smiley = ilChatroomSmilies::_getSmiley($smileyId);

        $confirmation = new ilConfirmationGUI();
        $confirmation->setFormAction($this->ilCtrl->getFormAction($this->gui, 'smiley'));
        $confirmation->setHeaderText($this->ilLng->txt('chatroom_confirm_delete_smiley'));
        $confirmation->setConfirm($this->ilLng->txt('confirm'), 'smiley-deleteSmileyObject');
        $confirmation->setCancel($this->ilLng->txt('cancel'), 'smiley');
        $confirmation->addItem(
            'chatroom_smiley_id',
            (string) $smiley['smiley_id'],
            ilUtil::img($smiley['smiley_fullpath'], $smiley['smiley_keywords']) . ' ' . $smiley['smiley_keywords']
        );

        $this->mainTpl->setContent($confirmation->getHTML());
    }

    public function deleteSmileyObject(): void
    {
        if (!$this->rbacsystem->checkAccess('write', $this->gui->getRefId())) {
            $this->ilias->raiseError(
                $this->ilLng->txt('msg_no_perm_write'),
                $this->ilias->error_obj->MESSAGE
            );
        }

        $smileyId = $this->getRequestValue(
            'chatroom_smiley_id',
            $this->refinery->kindlyTo()->int()
        );

        ilChatroomSmilies::_deleteSmiley($smileyId);

        $this->ilCtrl->redirect($this->gui, 'smiley');
    }

    public function updateSmiliesObject(): void
    {
        if (!$this->rbacsystem->checkAccess('write', $this->gui->getRefId())) {
            $this->ilias->raiseError(
                $this->ilLng->txt('msg_no_perm_write'),
                $this->ilias->error_obj->MESSAGE
            );
        }

        $smileyId = $this->getRequestValue('smiley_id', $this->refinery->kindlyTo()->int());

        $this->initSmiliesEditForm($this->getSmileyFormDataById($smileyId));

        $keywords = ilChatroomSmilies::_prepareKeywords(ilUtil::stripSlashes(
            $this->getRequestValue('chatroom_smiley_keywords', $this->refinery->kindlyTo()->string(), '')
        ));

        $atLeastOneKeywordGiven = count($keywords) > 0;

        $isFormValid = $this->form_gui->checkInput();
        if (!$atLeastOneKeywordGiven || !$isFormValid) {
            $errorShown = !$isFormValid;
            if (!$atLeastOneKeywordGiven && !$errorShown) {
                $this->mainTpl->setOnScreenMessage('failure', $this->ilLng->txt('form_input_not_valid'));
            }

            $this->form_gui->setValuesByPost();

            $this->showEditSmileyEntryFormObject();
            return;
        }

        $data = [];
        $data['smiley_keywords'] = implode("\n", $keywords);
        $data['smiley_id'] = $smileyId;

        if ($this->upload->hasUploads() && !$this->upload->hasBeenProcessed()) {
            $this->upload->process();

            /** @var \ILIAS\FileUpload\DTO\UploadResult|null $result */
            $result = array_values($this->upload->getResults())[0];
            if ($result && $result->isOK()) {
                $this->upload->moveOneFileTo(
                    $result,
                    ilChatroomSmilies::getSmiliesBasePath(),
                    \ILIAS\FileUpload\Location::WEB,
                    $result->getName(),
                    true
                );

                $data['smiley_path'] = $result->getName();
            }
        }

        ilChatroomSmilies::_updateSmiley($data);

        $this->mainTpl->setOnScreenMessage('success', $this->ilLng->txt('saved_successfully'), true);
        $this->ilCtrl->redirect($this->gui, 'smiley');
    }

    /**
     * Shows confirmation view for deleting multiple smilies
     * Prepares confirmation view for deleting multiple smilies and displays it.
     */
    public function deleteMultipleObject(): void
    {
        $this->gui->switchToVisibleMode();

        if (!$this->rbacsystem->checkAccess('write', $this->gui->getRefId())) {
            $this->ilias->raiseError(
                $this->ilLng->txt('msg_no_perm_write'),
                $this->ilias->error_obj->MESSAGE
            );
        }

        $ids = $this->getRequestValue(
            'smiley_id',
            $this->refinery->kindlyTo()->listOf(
                $this->refinery->kindlyTo()->int()
            ),
            []
        );
        if ($ids === []) {
            $this->mainTpl->setOnScreenMessage('info', $this->ilLng->txt('select_one'), true);
            $this->ilCtrl->redirect($this->gui, 'smiley');
        }

        $smilies = ilChatroomSmilies::_getSmiliesById($ids);
        if ($smilies === []) {
            $this->mainTpl->setOnScreenMessage('info', $this->ilLng->txt('select_one'), true);
            $this->ilCtrl->redirect($this->gui, 'smiley');
        }

        $confirmation = new ilConfirmationGUI();
        $confirmation->setFormAction($this->ilCtrl->getFormAction($this->gui, 'smiley'));
        $confirmation->setHeaderText($this->ilLng->txt('chatroom_confirm_delete_smiley'));
        $confirmation->setConfirm($this->ilLng->txt('confirm'), 'smiley-confirmedDeleteMultipleObject');
        $confirmation->setCancel($this->ilLng->txt('cancel'), 'smiley');

        foreach ($smilies as $s) {
            $confirmation->addItem(
                'sel_ids[]',
                (string) $s['smiley_id'],
                ilUtil::img($s['smiley_fullpath'], $s['smiley_keywords']) . ' ' . $s['smiley_keywords']
            );
        }

        $this->mainTpl->setContent($confirmation->getHTML());
    }

    public function confirmedDeleteMultipleObject(): void
    {
        if (!$this->rbacsystem->checkAccess('write', $this->gui->getRefId())) {
            $this->ilias->raiseError(
                $this->ilLng->txt('msg_no_perm_write'),
                $this->ilias->error_obj->MESSAGE
            );
        }

        $ids = $this->getRequestValue(
            'sel_ids',
            $this->refinery->kindlyTo()->listOf(
                $this->refinery->kindlyTo()->int()
            ),
            []
        );

        if ($ids === []) {
            $this->ilCtrl->redirect($this->gui, 'smiley');
        }

        ilChatroomSmilies::_deleteMultipleSmilies($ids);

        $this->ilCtrl->redirect($this->gui, 'smiley');
    }

    public function uploadSmileyObject(): void
    {
        if (!$this->rbacsystem->checkAccess('write', $this->gui->getRefId())) {
            $this->ilias->raiseError(
                $this->ilLng->txt('msg_no_perm_write'),
                $this->ilias->error_obj->MESSAGE
            );
        }

        $this->initSmiliesForm();

        $keywords = ilChatroomSmilies::_prepareKeywords(ilUtil::stripSlashes(
            $this->getRequestValue(
                'chatroom_smiley_keywords',
                $this->refinery->kindlyTo()->string(),
                ''
            )
        ));

        $atLeastOneKeywordGiven = count($keywords) > 0;

        $isFormValid = $this->form_gui->checkInput();
        if (!$atLeastOneKeywordGiven || !$isFormValid) {
            $errorShown = !$isFormValid;
            if (!$atLeastOneKeywordGiven && !$errorShown) {
                $this->mainTpl->setOnScreenMessage('failure', $this->ilLng->txt('form_input_not_valid'));
            }

            $this->form_gui->setValuesByPost();

            $this->view();
            return;
        }

        $pathinfo = pathinfo($_FILES['chatroom_image_path']['name']);
        $target_file = md5(time() . $pathinfo['basename']) . '.' . $pathinfo['extension'];

        if ($this->upload->hasUploads() && !$this->upload->hasBeenProcessed()) {
            $this->upload->process();

            /** @var \ILIAS\FileUpload\DTO\UploadResult|null $result */
            $result = array_values($this->upload->getResults())[0];
            if ($result && $result->isOK()) {
                $this->upload->moveOneFileTo(
                    $result,
                    ilChatroomSmilies::getSmiliesBasePath(),
                    \ILIAS\FileUpload\Location::WEB,
                    $target_file,
                    true
                );

                ilChatroomSmilies::_storeSmiley(implode("\n", $keywords), $target_file);
            }
        }

        $this->mainTpl->setOnScreenMessage('success', $this->ilLng->txt('saved_successfully'), true);
        $this->ilCtrl->redirect($this->gui, 'smiley');
    }
}
