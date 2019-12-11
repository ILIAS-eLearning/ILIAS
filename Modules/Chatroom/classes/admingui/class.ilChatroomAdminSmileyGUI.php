<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilChatroomSmileyGUI
 * Provides methods to show, add, edit and delete smilies
 * consisting of icon and keywords
 * @author  Andreas Kordosz <akordosz@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilChatroomAdminSmileyGUI extends ilChatroomGUIHandler
{
    /**
     * @var null|\ilPropertyFormGUI
     */
    protected $form_gui;

    /**
     * {@inheritdoc}
     */
    public function executeDefault($requestedMethod)
    {
        $this->view();
    }

    /**
     * Switches GUI to visible mode and calls editSmiliesObject method
     * which prepares and displays the table of existing smilies.
     */
    public function view()
    {
        include_once 'Modules/Chatroom/classes/class.ilChatroom.php';

        ilChatroom::checkUserPermissions('read', $this->gui->ref_id);

        $this->gui->switchToVisibleMode();

        self::_checkSetup();

        $this->editSmiliesObject();
    }

    public static function _checkSetup()
    {
        global $lng;

        $path = self::_getSmileyDir();

        if (!is_dir($path)) {
            ilUtil::sendInfo($lng->txt('chat_smilies_dir_not_exists'));
            ilUtil::makeDirParents($path);

            if (!is_dir($path)) {
                ilUtil::sendFailure($lng->txt('chat_smilies_dir_not_available'));
                return false;
            } else {
                $smilies = array(
                    "icon_smile.gif",
                    "icon_wink.gif",
                    "icon_laugh.gif",
                    "icon_sad.gif",
                    "icon_shocked.gif",
                    "icon_tongue.gif",
                    "icon_cool.gif",
                    "icon_eek.gif",
                    "icon_angry.gif",
                    "icon_flush.gif",
                    "icon_idea.gif",
                    "icon_thumbup.gif",
                    "icon_thumbdown.gif",
                );

                foreach ($smilies as $smiley) {
                    copy("templates/default/images/emoticons/$smiley", $path . "/$smiley");
                }

                self::_insertDefaultValues();

                ilUtil::sendSuccess($lng->txt('chat_smilies_initialized'));
            }
        }

        if (!is_writable($path)) {
            ilUtil::sendInfo($lng->txt('chat_smilies_dir_not_writable'));
        }

        return true;
    }

    /**
     * @param bool $withBaseDir
     * @return string
     */
    public static function _getSmileyDir($withBaseDir = true)
    {
        $path = 'chatroom/smilies';

        if ($withBaseDir) {
            $path = ilUtil::getWebspaceDir() . '/' . $path;
        }

        return $path;
    }

    private static function _insertDefaultValues()
    {
        $values = array(
            array("icon_smile.gif", ":)\n:-)\n:smile:"),
            array("icon_wink.gif", ";)\n;-)\n:wink:"),
            array("icon_laugh.gif", ":D\n:-D\n:laugh:\n:grin:\n:biggrin:"),
            array("icon_sad.gif", ":(\n:-(\n:sad:"),
            array("icon_shocked.gif", ":o\n:-o\n:shocked:"),
            array("icon_tongue.gif", ":p\n:-p\n:tongue:"),
            array("icon_cool.gif", ":cool:"),
            array("icon_eek.gif", ":eek:"),
            array("icon_angry.gif", ":||\n:-||\n:angry:"),
            array("icon_flush.gif", ":flush:"),
            array("icon_idea.gif", ":idea:"),
            array("icon_thumbup.gif", ":thumbup:"),
            array("icon_thumbdown.gif", ":thumbdown:"),
        );

        require_once 'Modules/Chatroom/classes/class.ilChatroomSmilies.php';
        foreach ($values as $val) {
            ilChatroomSmilies::_storeSmiley($val[1], $val[0]);
        }
    }

    /**
     * Shows existing smilies table
     * Prepares existing smilies table and displays it.
     * @global ilRbacSystem $rbacsystem
     * @global ilLanguage   $lng
     * @global ilTemplate   $tpl
     */
    public function editSmiliesObject()
    {
        global $rbacsystem, $lng, $tpl;

        if (!$rbacsystem->checkAccess('read', $this->gui->ref_id)) {
            $this->ilias->raiseError(
                $lng->txt('msg_no_perm_read'),
                $this->gui->ilias->error_obj->MESSAGE
            );
        }

        include_once 'Modules/Chatroom/classes/class.ilChatroomSmilies.php';

        ilChatroomSmilies::_checkSetup();

        if (!$this->form_gui) {
            $form = $this->initSmiliesForm();
        } else {
            $form = $this->form_gui;
        }

        include_once "Modules/Chatroom/classes/class.ilChatroomSmiliesGUI.php";

        $table = ilChatroomSmiliesGUI::_getExistingSmiliesTable($this->gui);

        $tpl_smilies = new ilTemplate(
            "tpl.chatroom_edit_smilies.html",
            true,
            true,
            "Modules/Chatroom"
        );
        $tpl_smilies->setVariable("SMILEY_TABLE", $table);
        $tpl_smilies->setVariable("SMILEY_FORM", $form->getHtml());

        $tpl->setContent($tpl_smilies->get());
    }

    /**
     * Initializes smilies form and returns it.
     * @global ilCtrl2    $ilCtrl
     * @global ilLanguage $lng
     * @return ilPropertyFormGUI
     */
    public function initSmiliesForm()
    {
        global $ilCtrl, $lng;

        include_once('./Services/Form/classes/class.ilPropertyFormGUI.php');

        $this->form_gui = new ilPropertyFormGUI();

        $table_nav = $_REQUEST["_table_nav"] ? "&_table_nav=" . $_REQUEST["_table_nav"] : "";
        $this->form_gui->setFormAction(
            $ilCtrl->getFormAction($this->gui, 'smiley-uploadSmileyObject') . $table_nav
        );

        // chat server settings
        $sec_l = new ilFormSectionHeaderGUI();

        $sec_l->setTitle($lng->txt('chatroom_add_smiley'));
        $this->form_gui->addItem($sec_l);

        $inp = new ilImageFileInputGUI(
            $lng->txt('chatroom_image_path'),
            'chatroom_image_path'
        );
        $inp->setSuffixes(array("jpg", "jpeg", "png", "gif", "svg"));

        $inp->setRequired(true);
        $this->form_gui->addItem($inp);

        $inp = new ilTextAreaInputGUI(
            $lng->txt('chatroom_smiley_keywords'),
            'chatroom_smiley_keywords'
        );

        $inp->setRequired(true);
        $inp->setUseRte(false);
        $inp->setInfo($lng->txt('chatroom_smiley_keywords_one_per_line_note'));
        $this->form_gui->addItem($inp);
        $this->form_gui->addCommandButton(
            'smiley-uploadSmileyObject',
            $lng->txt('chatroom_upload_smiley')
        );

        return $this->form_gui;
    }

    /**
     * Shows EditSmileyEntryForm
     * Prepares EditSmileyEntryForm and displays it.
     * @global ilRbacSystem $rbacsystem
     * @global ilTemplate   $tpl
     */
    public function showEditSmileyEntryFormObject()
    {
        global $rbacsystem, $tpl, $lng;

        $this->gui->switchToVisibleMode();

        if (!$rbacsystem->checkAccess('read', $this->gui->ref_id)) {
            $this->ilias->raiseError(
                $lng->txt('msg_no_perm_read'),
                $this->ilias->error_obj->MESSAGE
            );
        }

        if (!$this->form_gui) {
            $form = $this->initSmiliesEditForm($this->getSmileyFormDataById((int) $_REQUEST['smiley_id']));
        } else {
            $form = $this->form_gui;
        }

        $tpl_form = new ilTemplate(
            "tpl.chatroom_edit_smilies.html",
            true,
            true,
            "Modules/Chatroom"
        );

        $tpl_form->setVariable("SMILEY_FORM", $form->getHTML());

        $tpl->setContent($tpl_form->get());
    }

    /**
     * @param $smileyId
     * @return array
     * @throws \Exception
     */
    protected function getSmileyFormDataById($smileyId)
    {
        require_once 'Modules/Chatroom/classes/class.ilChatroomSmilies.php';
        $smiley = ilChatroomSmilies::_getSmiley($smileyId);

        $form_data = array(
            'chatroom_smiley_id'                 => $smiley['smiley_id'],
            'chatroom_smiley_keywords'           => $smiley['smiley_keywords'],
            'chatroom_current_smiley_image_path' => $smiley['smiley_fullpath'],
        );

        return $form_data;
    }

    /**
     * Initializes SmiliesEditForm and returns it.
     * @global ilCtrl2    $ilCtrl
     * @global ilLanguage $lng
     * @return ilPropertyFormGUI
     */
    public function initSmiliesEditForm($form_data)
    {
        global $ilCtrl, $lng;

        include_once('./Services/Form/classes/class.ilPropertyFormGUI.php');

        $this->form_gui = new ilPropertyFormGUI();

        $this->form_gui->setValuesByArray($form_data);

        $table_nav = $_REQUEST["_table_nav"] ? "&_table_nav=" . $_REQUEST["_table_nav"] : "";

        $ilCtrl->saveParameter($this->gui, 'smiley_id');

        $this->form_gui->setFormAction(
            $ilCtrl->getFormAction($this->gui, 'smiley-updateSmiliesObject') . $table_nav
        );

        $sec_l = new ilFormSectionHeaderGUI();

        $sec_l->setTitle($lng->txt('chatroom_edit_smiley'));
        $this->form_gui->addItem($sec_l);

        include_once "Modules/Chatroom/classes/class.ilChatroomSmiliesCurrentSmileyFormElement.php";

        $inp = new ilChatroomSmiliesCurrentSmileyFormElement(
            $lng->txt('chatroom_current_smiley_image_path'),
            'chatroom_current_smiley_image_path'
        );

        $inp->setValue($form_data['chatroom_current_smiley_image_path']);
        $this->form_gui->addItem($inp);

        $inp = new ilImageFileInputGUI(
            $lng->txt('chatroom_image_path'),
            'chatroom_image_path'
        );
        $inp->setSuffixes(array("jpg", "jpeg", "png", "gif", "svg"));

        $inp->setRequired(false);
        $inp->setInfo($lng->txt('chatroom_smiley_image_only_if_changed'));
        $this->form_gui->addItem($inp);

        $inp = new ilTextAreaInputGUI(
            $lng->txt('chatroom_smiley_keywords'),
            'chatroom_smiley_keywords'
        );

        $inp->setValue($form_data['chatroom_smiley_keywords']);
        $inp->setUseRte(false);
        $inp->setRequired(true);
        $inp->setInfo($lng->txt('chatroom_smiley_keywords_one_per_line_note'));
        $this->form_gui->addItem($inp);

        $inp = new ilHiddenInputGUI('chatroom_smiley_id');

        $this->form_gui->addItem($inp);
        $this->form_gui->addCommandButton(
            'smiley-updateSmiliesObject',
            $lng->txt('submit')
        );
        $this->form_gui->addCommandButton('smiley', $lng->txt('cancel'));
        return $this->form_gui;
    }

    /**
     * Shows DeleteSmileyForm
     * Prepares DeleteSmileyForm and displays it.
     */
    public function showDeleteSmileyFormObject()
    {
        /**
         * @var $rbacsystem ilRbacSystem
         * @var $lng        ilLanguage
         * @var $ilCtrl     ilCtrl
         * @var $tpl        ilTemplate
         */
        global $rbacsystem, $lng, $ilCtrl, $tpl;

        $this->gui->switchToVisibleMode();

        if (!$rbacsystem->checkAccess('write', $this->gui->ref_id)) {
            $this->ilias->raiseError(
                $lng->txt('msg_no_perm_write'),
                $this->ilias->error_obj->MESSAGE
            );
        }

        include_once 'Modules/Chatroom/classes/class.ilChatroomSmilies.php';
        $smiley = ilChatroomSmilies::_getSmiley((int) $_REQUEST['smiley_id']);

        require_once 'Services/Utilities/classes/class.ilConfirmationGUI.php';
        $confirmation = new ilConfirmationGUI();
        $confirmation->setFormAction($ilCtrl->getFormAction($this->gui, 'smiley'));
        $confirmation->setHeaderText($lng->txt('chatroom_confirm_delete_smiley'));
        $confirmation->addButton($lng->txt('confirm'), 'smiley-deleteSmileyObject');
        $confirmation->addButton($lng->txt('cancel'), 'smiley');
        $confirmation->addItem('chatroom_smiley_id', $smiley['smiley_id'], ilUtil::img($smiley['smiley_fullpath'], $smiley['smiley_keywords']) . ' ' . $smiley['smiley_keywords']);

        $tpl->setContent($confirmation->getHTML());
    }

    /**
     * Deletes a smiley by $_REQUEST["chatroom_smiley_id"]
     * @global ilRbacSystem $rbacsystem
     * @global ilCtrl       $ilCtrl
     */
    public function deleteSmileyObject()
    {
        global $rbacsystem, $ilCtrl, $lng;

        if (!$rbacsystem->checkAccess('write', $this->gui->ref_id)) {
            $this->ilias->raiseError(
                $lng->txt('msg_no_perm_write'),
                $this->ilias->error_obj->MESSAGE
            );
        }

        include_once 'Modules/Chatroom/classes/class.ilChatroomSmilies.php';

        ilChatroomSmilies::_deleteSmiley($_REQUEST["chatroom_smiley_id"]);

        $ilCtrl->redirect($this->gui, "smiley");
    }

    /**
     * Updates a smiley and/or its keywords
     * Updates a smiley icon and/or its keywords by $_REQUEST["chatroom_smiley_id"]
     * and gets keywords from $_REQUEST["chatroom_smiley_keywords"].
     */
    public function updateSmiliesObject()
    {
        global $rbacsystem, $ilCtrl, $tpl, $lng;

        if (!$rbacsystem->checkAccess('write', $this->gui->ref_id)) {
            $this->ilias->raiseError(
                $lng->txt('msg_no_perm_write'),
                $this->ilias->error_obj->MESSAGE
            );
        }

        $this->initSmiliesEditForm($this->getSmileyFormDataById((int) $_REQUEST['smiley_id']));

        require_once 'Modules/Chatroom/classes/class.ilChatroomSmilies.php';
        $keywords = ilChatroomSmilies::_prepareKeywords(
            ilUtil::stripSlashes($_REQUEST["chatroom_smiley_keywords"])
        );

        $keywordscheck = count($keywords) > 0;

        if (!($isFormValid = $this->form_gui->checkInput()) || !$keywordscheck) {
            if ($isFormValid) {
                \ilUtil::sendFailure($lng->txt('form_input_not_valid'));
            }

            $this->form_gui->setValuesByPost();

            return $this->showEditSmileyEntryFormObject();
        }

        $data                    = array();
        $data["smiley_keywords"] = join("\n", $keywords);
        $data["smiley_id"]       = (int) $_REQUEST['smiley_id'];

        if ($this->upload->hasUploads() && !$this->upload->hasBeenProcessed()) {
            $this->upload->process();

            /** @var \ILIAS\FileUpload\DTO\UploadResult $result */
            $result = array_values($this->upload->getResults())[0];
            if ($result && $result->getStatus() == \ILIAS\FileUpload\DTO\ProcessingStatus::OK) {
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

        \ilChatroomSmilies::_updateSmiley($data);

        \ilUtil::sendSuccess($lng->txt('saved_successfully'), true);
        $ilCtrl->redirect($this->gui, "smiley");
    }

    /**
     * Shows confirmation view for deleting multiple smilies
     * Prepares confirmation view for deleting multiple smilies and displays it.
     */
    public function deleteMultipleObject()
    {
        /**
         * @var $rbacsystem ilRbacSystem
         * @var $lng        ilLanguage
         * @var $ilCtrl     ilCtrl
         * @var $tpl        ilTemplate
         */
        global $rbacsystem, $lng, $ilCtrl, $tpl;

        $this->gui->switchToVisibleMode();

        if (!$rbacsystem->checkAccess('write', $this->gui->ref_id)) {
            $this->ilias->raiseError(
                $this->lng->txt('msg_no_perm_write'),
                $this->ilias->error_obj->MESSAGE
            );
        }

        $items = (array) $_REQUEST['smiley_id'];
        if (count($items) == 0) {
            ilUtil::sendInfo($lng->txt('select_one'), true);
            $ilCtrl->redirect($this->gui, 'smiley');
        }

        include_once 'Modules/Chatroom/classes/class.ilChatroomSmilies.php';
        $smilies = ilChatroomSmilies::_getSmiliesById($items);
        if (count($smilies) == 0) {
            ilUtil::sendInfo($lng->txt('select_one'), true);
            $ilCtrl->redirect($this->gui, 'smiley');
        }

        require_once 'Services/Utilities/classes/class.ilConfirmationGUI.php';
        $confirmation = new ilConfirmationGUI();
        $confirmation->setFormAction($ilCtrl->getFormAction($this->gui, 'smiley'));
        $confirmation->setHeaderText($lng->txt('chatroom_confirm_delete_smiley'));
        $confirmation->addButton($lng->txt('confirm'), 'smiley-confirmedDeleteMultipleObject');
        $confirmation->addButton($lng->txt('cancel'), 'smiley');

        foreach ($smilies as $s) {
            $confirmation->addItem('sel_ids[]', $s['smiley_id'], ilUtil::img($s['smiley_fullpath'], $s['smiley_keywords']) . ' ' . $s['smiley_keywords']);
        }

        $tpl->setContent($confirmation->getHTML());
    }

    /**
     * Deletes multiple smilies by $_REQUEST["sel_ids"]
     */
    public function confirmedDeleteMultipleObject()
    {
        /**
         * @var $rbacsystem ilRbacSystem
         * @var $ilCtrl     ilCtrl
         */
        global $rbacsystem, $ilCtrl;

        if (!$rbacsystem->checkAccess('write', $this->gui->ref_id)) {
            $this->ilias->raiseError(
                $this->lng->txt('msg_no_perm_write'),
                $this->ilias->error_obj->MESSAGE
            );
        }

        $parts = $_POST["sel_ids"];
        if (count($parts) == 0) {
            $ilCtrl->redirect($this->gui, "smiley");
        }

        include_once "Modules/Chatroom/classes/class.ilChatroomSmilies.php";
        ilChatroomSmilies::_deleteMultipleSmilies($parts);

        $ilCtrl->redirect($this->gui, "smiley");
    }

    /**
     * Uploads and stores a new smiley with keywords from
     * $_REQUEST["chatroom_smiley_keywords"]
     */
    public function uploadSmileyObject()
    {
        global $rbacsystem, $ilCtrl, $tpl, $lng;

        if (!$rbacsystem->checkAccess('write', $this->gui->ref_id)) {
            $this->ilias->raiseError(
                $lng->txt('msg_no_perm_write'),
                $this->ilias->error_obj->MESSAGE
            );
        }

        $this->initSmiliesForm();

        require_once 'Modules/Chatroom/classes/class.ilChatroomSmilies.php';
        $keywords = ilChatroomSmilies::_prepareKeywords(
            ilUtil::stripSlashes($_REQUEST["chatroom_smiley_keywords"])
        );

        $keywordscheck = count($keywords) > 0;

        if (!($isFormValid = $this->form_gui->checkInput()) || !$keywordscheck) {
            if ($isFormValid) {
                \ilUtil::sendFailure($lng->txt('form_input_not_valid'));
            }

            $this->form_gui->setValuesByPost();

            return $this->view();
        }

        $pathinfo    = pathinfo($_FILES["chatroom_image_path"]["name"]);
        $target_file = md5(time() + $pathinfo['basename']) . "." . $pathinfo['extension'];

        if ($this->upload->hasUploads() && !$this->upload->hasBeenProcessed()) {
            $this->upload->process();

            /** @var \ILIAS\FileUpload\DTO\UploadResult $result */
            $result = array_values($this->upload->getResults())[0];
            if ($result && $result->getStatus() == \ILIAS\FileUpload\DTO\ProcessingStatus::OK) {
                $this->upload->moveOneFileTo(
                    $result,
                    ilChatroomSmilies::getSmiliesBasePath(),
                    \ILIAS\FileUpload\Location::WEB,
                    $target_file,
                    true
                );

                ilChatroomSmilies::_storeSmiley(join("\n", $keywords), $target_file);
            }
        }

        \ilUtil::sendSuccess($lng->txt('saved_successfully'), true);
        $ilCtrl->redirect($this->gui, "smiley");
    }
}
