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
 * @author       Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilSurveyRaterGUI: ilRepositorySearchGUI
 */
class ilSurveyRaterGUI
{
    protected \ILIAS\Survey\Editing\EditingGUIRequest $edit_request;
    protected \ilCtrl $ctrl;
    protected \ilLanguage $lng;
    protected \ILIAS\DI\UIServices $ui;
    protected \ILIAS\Refinery\Factory $refinery;
    protected \ilGlobalTemplateInterface $main_tpl;
    protected ilSurveyParticipantsGUI $parent;
    protected ilObjSurvey $survey;
    protected \ilObjUser $user;
    protected \ilAccessHandler $access;
    protected ilTabsGUI $tabs;

    public function __construct(
        ilSurveyParticipantsGUI $parent,
        ilObjSurvey $survey
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->ui = $DIC->ui();
        $this->refinery = $DIC->refinery();
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->parent = $parent;
        $this->survey = $survey;
        $this->user = $DIC->user();
        $this->access = $DIC->access();
        $this->tabs = $DIC->tabs();

        $this->ctrl->saveParameter($this, "appr_id");
        $this->edit_request = $DIC->survey()
            ->internal()
            ->gui()
            ->editing()
            ->request();
    }

    public function executeCommand(): void
    {
        $ctrl = $this->ctrl;

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd("add");

        switch ($next_class) {
            case 'ilrepositorysearchgui':
                $rep_search = new ilRepositorySearchGUI();

                $this->ctrl->setParameter($this, "rate360", 1);
                $this->ctrl->saveParameter($this, "appr_id");

                $rep_search->setCallback(
                    $this,
                    'addFromSearch',
                    array()
                );

                // Set tabs
                $this->ctrl->setReturn($this, 'add');
                $this->ctrl->forwardCommand($rep_search);
                break;

            default:
                if (in_array($cmd, ["add",
                                    "doAutoComplete",
                                    "continue",
                                    "mailRaters",
                                    "mailRatersAction",
                                    "cancel"
                ])) {
                    $this->$cmd();
                }
                break;
        }
    }

    protected function cancel(): void
    {
        $this->ctrl->redirect($this->parent, "editRaters");
    }

    protected function add(
        ilPropertyFormGUI $form = null
    ): void {
        $form_html = (!is_null($form))
            ? $form->getHTML()
            : $this->initOptionSelectForm()->getHTML();
        $main_tpl = $this->main_tpl;
        $main_tpl->setContent($form_html);
    }

    public function initOptionSelectForm(): ilPropertyFormGUI
    {
        $ctrl = $this->ctrl;
        $lng = $this->lng;

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();

        //
        $radg = new ilRadioGroupInputGUI($lng->txt("svy_type_of_rater"), "type");
        //$radg->setInfo($lng->txt(""));
        //$radg->setValue();
        $op1 = new ilRadioOption($lng->txt("svy_add_internal_user"), "direct", $lng->txt("svy_add_internal_user_info"));
        $radg->addOption($op1);
        $radg->setValue("direct");

        $user = new \ilTextInputGUI($lng->txt("obj_user"), "user");
        $user->setDataSource(
            $ctrl->getLinkTargetByClass(
                "ilsurveyratergui",
                "doAutoComplete",
                "",
                true
            )
        );
        $user->setRequired(true);
        $user->setMulti(false);
        $op1->addSubItem($user);


        $op2 = new ilRadioOption($lng->txt("svy_search_user"), "search", $lng->txt("svy_search_user_info"));
        $radg->addOption($op2);

        $op3 = new ilRadioOption($lng->txt("svy_external_rater"), "external", $lng->txt("svy_external_rater_info"));
        $radg->addOption($op3);

        $email = new ilEMailInputGUI($this->lng->txt("email"), "email");
        $email->setRequired(true);
        $op3->addSubItem($email);

        $lname = new ilTextInputGUI($this->lng->txt("lastname"), "lname");
        $lname->setSize(30);
        $op3->addSubItem($lname);

        $fname = new ilTextInputGUI($this->lng->txt("firstname"), "fname");
        $fname->setSize(30);
        $op3->addSubItem($fname);


        $form->addItem($radg);

        // save and cancel commands
        $form->addCommandButton("continue", $lng->txt("svy_save_and_continue"));
        $form->addCommandButton("cancel", $lng->txt("cancel"));

        $form->setTitle($lng->txt("svy_add_rater"));
        $form->setFormAction($ctrl->getFormAction($this));

        return $form;
    }

    public function doAutoComplete(): void
    {
        $fields = array('login','firstname','lastname','email');

        $auto = new ilUserAutoComplete();
        $auto->setSearchFields($fields);
        $auto->setResultField('login');
        $auto->enableFieldSearchableCheck(true);
        $auto->setMoreLinkAvailable(true);
        $auto->setPrivacyMode(ilUserAutoComplete::PRIVACY_MODE_RESPECT_USER_SETTING);

        if ($this->edit_request->getFetchAll()) {
            $auto->setLimit(ilUserAutoComplete::MAX_ENTRIES);
        }

        echo $auto->getList($this->edit_request->getTerm());
        exit();
    }

    protected function continue(): void
    {
        $form = $this->initOptionSelectForm();
        if ($form->checkInput()) {
            switch ($form->getInput("type")) {
                case "direct":
                    $this->addRater($form);
                    break;

                case "external":
                    $this->addExternalRater($form);
                    break;

                case "search":
                    $this->ctrl->redirectByClass("ilrepositorysearchgui", "");
                    break;
            }
        } else {
            $form->setValuesByPost();
            $this->add($form);
        }
    }

    public function addRater(ilPropertyFormGUI $form): void
    {
        // check access
        $ilAccess = $this->access;
        $ilUser = $this->user;

        $appr_id = $this->parent->handleRatersAccess();
        $user = $form->getInput("user");
        $user_id = ilObjUser::_lookupId($user);
        if ($user_id > 0) {
            if ($ilAccess->checkAccess("write", "", $this->survey->getRefId()) ||
                $this->survey->get360SelfEvaluation() ||
                $user_id !== $ilUser->getId()) {
                if ($appr_id !== $user_id) {
                    $this->survey->addRater($appr_id, $user_id);
                    $this->main_tpl->setOnScreenMessage('success', $this->lng->txt("settings_saved"), true);
                } else {
                    $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt("svy_appraisses_cannot_be_raters"), true);
                    $user_id = 0;
                }
            }
        } else {
            $this->main_tpl->setOnScreenMessage(
                'failure',
                $this->lng->txt("svy_user_not_found") . " (" . $user . ")",
                true
            );
        }

        $this->ctrl->setParameter($this->parent, "appr_id", $appr_id);
        if ($user_id > 0) {
            $this->ctrl->setParameter($this, "rater_id", "u" . $user_id);
            $this->ctrl->redirect($this, "mailRaters");
        }
        $this->ctrl->redirect($this->parent, "editRaters");
    }


    public function mailRaters(ilPropertyFormGUI $a_form = null): void
    {
        $appr_id = $this->parent->handleRatersAccess();
        $this->ctrl->setParameterByClass("ilSurveyParticipantsGUI", "appr_id", $appr_id);
        $this->ctrl->setParameterByClass("ilSurveyParticipantsGUI", "rater_id", $this->edit_request->getRaterId());
        $this->ctrl->redirectByClass("ilSurveyParticipantsGUI", "mailRaters");
    }

    public function initMailRatersForm(
        int $appr_id,
        array $rec_ids
    ): ilPropertyFormGUI {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, "mailRatersAction"));
        $form->setTitle($this->lng->txt('compose'));

        $all_data = $this->survey->getRatersData($appr_id);

        $rec_data = array();
        foreach ($rec_ids as $rec_id) {
            if (isset($all_data[$rec_id])) {
                $rec_data[] = $all_data[$rec_id]["lastname"] . ", " .
                    $all_data[$rec_id]["firstname"] .
                    " (" . $all_data[$rec_id]["email"] . ")";
            }
        }
        sort($rec_data);
        $rec = new ilCustomInputGUI($this->lng->txt('recipients'));
        $rec->setHtml(implode("<br />", $rec_data));
        $form->addItem($rec);

        $subject = new ilTextInputGUI($this->lng->txt('subject'), 'subject');
        $subject->setSize(50);
        $subject->setRequired(true);
        $form->addItem($subject);

        $existingdata = $this->survey->getExternalCodeRecipients();
        $existingcolumns = array();
        if (count($existingdata)) {
            $first = array_shift($existingdata);
            foreach ($first as $key => $value) {
                if (strcmp($key, 'code') !== 0 && strcmp($key, 'email') !== 0 && strcmp($key, 'sent') !== 0) {
                    $existingcolumns[] = '[' . $key . ']';
                }
            }
        }

        $mailmessage_u = new ilTextAreaInputGUI($this->lng->txt('survey_360_rater_message_content_registered'), 'message_u');
        $mailmessage_u->setRequired(true);
        $mailmessage_u->setCols(80);
        $mailmessage_u->setRows(10);
        $form->addItem($mailmessage_u);

        $mailmessage_a = new ilTextAreaInputGUI($this->lng->txt('survey_360_rater_message_content_anonymous'), 'message_a');
        $mailmessage_a->setRequired(true);
        $mailmessage_a->setCols(80);
        $mailmessage_a->setRows(10);
        $mailmessage_a->setInfo(sprintf($this->lng->txt('message_content_info'), implode(', ', $existingcolumns)));
        $form->addItem($mailmessage_a);

        $recf = new ilHiddenInputGUI("rtr_id");
        $recf->setValue(implode(";", $rec_ids));
        $form->addItem($recf);

        $form->addCommandButton("mailRatersAction", $this->lng->txt("send"));
        $form->addCommandButton("cancel", $this->lng->txt("svy_dont_send"));

        $subject->setValue(sprintf($this->lng->txt('survey_360_rater_subject_default'), $this->survey->getTitle()));
        $mailmessage_u->setValue($this->lng->txt('survey_360_rater_message_content_registered_default'));
        $mailmessage_a->setValue($this->lng->txt('survey_360_rater_message_content_anonymous_default'));

        return $form;
    }


    public function mailRatersAction(): void
    {
        $ilUser = $this->user;

        $appr_id = $this->parent->handleRatersAccess();
        $this->ctrl->setParameter($this, "appr_id", $appr_id);

        $rec_ids = $this->edit_request->getRaterIds();
        if (count($rec_ids) === 0) {
            $this->ctrl->redirect($this, "editRaters");
        }

        $form = $this->initMailRatersForm($appr_id, $rec_ids);
        if ($form->checkInput()) {
            $txt_u = $form->getInput("message_u");
            $txt_a = $form->getInput("message_a");
            $subj = $form->getInput("subject");

            // #12743
            $sender_id = (trim($ilUser->getEmail()))
                ? $ilUser->getId()
                : ANONYMOUS_USER_ID;

            $all_data = $this->survey->getRatersData($appr_id);
            foreach ($rec_ids as $rec_id) {
                if (isset($all_data[$rec_id])) {
                    $user = $all_data[$rec_id];

                    // anonymous
                    if (strpos($rec_id, "a") === 0) {
                        $mytxt = $txt_a;
                        $url = $user["href"];
                        $rcp = $user["email"];
                    }
                    // reg
                    else {
                        $mytxt = $txt_u;
                        $user["code"] = $this->lng->txt("survey_code_mail_on_demand");
                        $url = ilLink::_getStaticLink($this->survey->getRefId());
                        $rcp = $user["login"]; // #15141
                    }

                    $mytxt = str_replace(
                        ["[lastname]", "[firstname]", "[url]", "[code]"],
                        [$user["lastname"], $user["firstname"], $url, $user["code"]],
                        $mytxt
                    );

                    $mail = new ilMail($sender_id);
                    $mail->enqueue(
                        $rcp, // to
                        "", // cc
                        "", // bcc
                        $subj, // subject
                        $mytxt, // message
                        array() // attachments
                    );

                    $this->survey->set360RaterSent(
                        $appr_id,
                        (strpos($rec_id, "a") === 0) ? 0 : (int) substr($rec_id, 1),
                        (strpos($rec_id, "u") === 0) ? 0 : (int) substr($rec_id, 1)
                    );
                }
            }

            $this->main_tpl->setOnScreenMessage('success', $this->lng->txt("mail_sent"), true);
            $this->ctrl->redirect($this->parent, "editRaters");
        }

        $form->setValuesByPost();
//        $this->mailRatersObject($form);
    }

    public function addExternalRater(ilPropertyFormGUI $form): void
    {
        $appr_id = $this->edit_request->getAppraiseeId();

        if (!$appr_id) {
            $this->ctrl->redirect($this, "listAppraisees");
        }

        $this->ctrl->setParameter($this, "appr_id", $appr_id);

        $code_id = $this->parent->addCodeForExternal(
            $form->getInput("email"),
            $form->getInput("lname"),
            $form->getInput("fname")
        );

        $this->survey->addRater($appr_id, 0, $code_id);

        $this->ctrl->setParameter($this->parent, "appr_id", $appr_id);
        if ($code_id > 0) {
            $this->ctrl->setParameter($this, "rater_id", "a" . $code_id);
            $this->ctrl->redirect($this, "mailRaters");
        }
        $this->ctrl->redirect($this->parent, "editRaters");
    }

    public function addFromSearch(
        array $user_ids
    ): void {
        // check access
        $ilAccess = $this->access;
        $ilUser = $this->user;

        $user_id = 0;

        $appr_id = $this->parent->handleRatersAccess();

        foreach ($user_ids as $user_id) {
            if ($user_id > 0) {
                if ($ilAccess->checkAccess("write", "", $this->survey->getRefId()) ||
                    $this->survey->get360SelfEvaluation() ||
                    $user_id != $ilUser->getId()) {
                    if ($appr_id != $user_id) {
                        $this->survey->addRater($appr_id, $user_id);
                        $this->main_tpl->setOnScreenMessage('success', $this->lng->txt("settings_saved"), true);
                    } else {
                        $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt("svy_appraisses_cannot_be_raters"), true);
                    }
                }
            }
        }

        $user_str = implode(";", array_map(static function ($u): string {
            return "u" . $u;
        }, $user_ids));

        $this->ctrl->setParameter($this->parent, "appr_id", $appr_id);
        if ($user_id > 0) {
            $this->ctrl->setParameter($this, "rater_id", $user_str);
            $this->ctrl->redirect($this, "mailRaters");
        }
        $this->ctrl->redirect($this->parent, "editRaters");
    }
}
