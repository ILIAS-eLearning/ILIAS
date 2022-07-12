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

/**
 * Class FormMailCodesGUI
 *
 * @author		Helmut Schottmüller <ilias@aurealis.de>
 */
class FormMailCodesGUI extends ilPropertyFormGUI
{
    protected \ILIAS\Survey\Editing\EditingGUIRequest $request;
    protected ilSurveyParticipantsGUI $guiclass;
    protected ilTextInputGUI $subject;
    protected ilRadioGroupInputGUI $sendtype;
    protected ilSelectInputGUI $savedmessages;
    protected ilTextAreaInputGUI $mailmessage;
    protected ilCheckboxInputGUI $savemessage;
    protected ilTextInputGUI $savemessagetitle;
    
    public function __construct(
        ilSurveyParticipantsGUI $guiclass
    ) {
        global $DIC;
        $main_tpl = $DIC->ui()->mainTemplate();

        parent::__construct();

        $ilAccess = $DIC->access();
        $ilSetting = $DIC->settings();
        $ilUser = $DIC->user();
        $this->request = $DIC->survey()
            ->internal()
            ->gui()
            ->editing()
            ->request();

        $lng = $this->lng;

        $this->guiclass = $guiclass;
        
        $this->setFormAction($this->ctrl->getFormAction($this->guiclass));
        $this->setTitle($this->lng->txt('compose'));

        $this->subject = new ilTextInputGUI($this->lng->txt('subject'), 'm_subject');
        $this->subject->setSize(50);
        $this->subject->setRequired(true);
        $this->addItem($this->subject);

        $this->sendtype = new ilRadioGroupInputGUI($this->lng->txt('recipients'), "m_notsent");
        $this->sendtype->addOption(new ilCheckboxOption($this->lng->txt("send_to_all"), 0, ''));
        $this->sendtype->addOption(new ilCheckboxOption($this->lng->txt("not_sent_only"), 1, ''));
        $this->sendtype->addOption(new ilCheckboxOption($this->lng->txt("send_to_unanswered"), 3, ''));
        $this->sendtype->addOption(new ilCheckboxOption($this->lng->txt("send_to_answered"), 2, ''));
        $this->addItem($this->sendtype);

        $existingdata = $this->guiclass->getObject()->getExternalCodeRecipients();

        $existingcolumns = array();
        if (count($existingdata)) {
            $first = array_shift($existingdata);
            foreach ($first as $key => $value) {
                if (strcmp($key, 'code') !== 0 && strcmp($key, 'email') !== 0 && strcmp($key, 'sent') !== 0) {
                    $existingcolumns[] = '[' . $key . ']';
                }
            }
        }

        $settings = $this->guiclass->getObject()->getUserSettings($ilUser->getId(), 'savemessage');
        if (count($settings)) {
            $options = array(0 => $this->lng->txt('please_select'));
            foreach ($settings as $setting) {
                $options[$setting['settings_id']] = $setting['title'];
            }
            $this->savedmessages = new ilSelectInputGUI($this->lng->txt("saved_messages"), "savedmessage");
            $this->savedmessages->setOptions($options);
            $this->addItem($this->savedmessages);
        }

        $this->mailmessage = new ilTextAreaInputGUI($this->lng->txt('message_content'), 'm_message');
        $this->mailmessage->setRequired(true);
        $this->mailmessage->setCols(80);
        $this->mailmessage->setRows(10);
        $this->mailmessage->setInfo(sprintf($this->lng->txt('message_content_info'), implode(', ', $existingcolumns)));
        $this->addItem($this->mailmessage);

        // save message
        $this->savemessage = new ilCheckboxInputGUI('', "savemessage");
        $this->savemessage->setOptionTitle($this->lng->txt("save_reuse_message"));
        $this->savemessage->setValue(1);

        $this->savemessagetitle = new ilTextInputGUI($this->lng->txt('save_reuse_title'), 'savemessagetitle');
        $this->savemessagetitle->setSize(60);
        $this->savemessage->addSubItem($this->savemessagetitle);

        $this->addItem($this->savemessage);

        if (count($settings)) {
            if ($ilAccess->checkAccess("write", "", $this->request->getRefId())) {
                $this->addCommandButton("deleteSavedMessage", $this->lng->txt("delete_saved_message"));
            }
            if ($ilAccess->checkAccess("write", "", $this->request->getRefId())) {
                $this->addCommandButton("insertSavedMessage", $this->lng->txt("insert_saved_message"));
            }
        }

        if ((int) $ilSetting->get('mail_allow_external')) {
            $this->addCommandButton("sendCodesMail", $this->lng->txt("send"));
        } else {
            $main_tpl->setOnScreenMessage('info', $lng->txt("cant_send_email_smtp_disabled"));
        }
    }
    
    public function getSavedMessages() : ilSelectInputGUI
    {
        return $this->savedmessages;
    }
    
    public function getMailMessage() : ilTextAreaInputGUI
    {
        return $this->mailmessage;
    }
}
