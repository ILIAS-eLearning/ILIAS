<?php

declare(strict_types=1);

/**
 * Class ilObjLearningSequenceSettingsGUI
 */
class ilObjLearningSequenceSettingsGUI
{
    const PROP_TITLE = 'title';
    const PROP_DESC = 'desc';
    const PROP_ABSTRACT = 'abstract';
    const PROP_ABSTRACT_IMAGE = 'abstract_img';
    const PROP_EXTRO = 'extro';
    const PROP_EXTRO_IMAGE = 'extro_img';
    const PROP_ONLINE = 'online';
    const PROP_AVAIL_PERIOD = 'online_period';
    const PROP_GALLERY = 'gallery';

    const CMD_SAVE = "update";
    const CMD_CANCEL = "cancel";

    private $rte_allowed_tags = [
        'br',
        'em',
        'h1',
        'h2',
        'h3',
        'li',
        'ol',
        'p',
        'strong',
        'u',
        'ul'
    ];

    private $img_allowed_suffixes = [
        'png',
        'jpg',
        'jpeg',
        'gif'
    ];

    public function __construct(
        ilObjLearningSequence $obj,
        ilCtrl $il_ctrl,
        ilLanguage $il_language,
        ilTemplate $il_template,
        ilObjectService $obj_service
    ) {
        $this->obj = $obj;
        $this->settings = $obj->getLSSettings();
        $this->activation = $obj->getLSActivation();
        $this->obj_title = $obj->getTitle();
        $this->obj_description = $obj->getDescription();
        $this->ctrl = $il_ctrl;
        $this->lng = $il_language;
        $this->tpl = $il_template;
        $this->object_service = $object_service;
        $this->obj_service = $obj_service;

        $this->lng->loadLanguageModule('content');
        $this->lng->loadLanguageModule('obj');
    }

    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd('settings');

        switch ($cmd) {
            case "settings":
            case self::CMD_SAVE:
            case self::CMD_CANCEL:
                $content = $this->$cmd();
                break;
            default:
                throw new ilException("ilObjLearningSequenceSettingsGUI: " .
                                      "Command not supported: $cmd");

        }
        $this->tpl->setContent($content);
    }

    protected function settings()
    {
        $form = $this->buildForm();
        $this->fillForm($form);
        $this->addCommonFieldsToForm($form);
        return $form->getHTML();
    }

    protected function cancel()
    {
        $this->ctrl->returnToParent($this);
    }

    private function initImgInput(ilImageFileInputGUI $inpt) : ilImageFileInputGUI
    {
        $inpt->setSuffixes($this->img_allowed_suffixes);
        $inpt->setALlowDeletion(true);
        return $inpt;
    }

    private function initRTEInput(ilTextAreaInputGUI $inpt) : ilTextAreaInputGUI
    {
        $inpt->setUseRte(true);
        $inpt->removePlugin(ilRTE::ILIAS_IMG_MANAGER_PLUGIN);
        $inpt->setRteTags($this->rte_allowed_tags);
        //$inpt->setRTESupport($obj_id, "lso", "learningsequence");
        return $inpt;
    }


    protected function buildForm()
    {
        $txt = function ($id) {
            return $this->lng->txt($id);
        };
        $settings = $this->settings;
        $activation = $this->activation;
        $obj_id = $settings->getObjId();

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, self::CMD_SAVE));
        $form->setTitle($this->lng->txt('lso_edit'));

        $title = new ilTextInputGUI($txt("title"), self::PROP_TITLE);
        $title->setRequired(true);
        $desc = new ilTextAreaInputGUI($txt("description"), self::PROP_DESC);

        $section_avail = new ilFormSectionHeaderGUI();
        $section_avail->setTitle($txt('lso_settings_availability'));
        $online = new ilCheckboxInputGUI($txt("online"), self::PROP_ONLINE);
        $online->setInfo($this->lng->txt('lso_activation_online_info'));
        $duration = new ilDateDurationInputGUI($txt('avail_time_period'), self::PROP_AVAIL_PERIOD);
        $duration->setShowTime(true);
        if ($activation->getActivationStart() !== null) {
            $duration->setStart(
                new ilDateTime(
                    (string) $activation->getActivationStart()->format('Y-m-d H:i:s'),
                    IL_CAL_DATETIME
                )
            );
        }
        if ($activation->getActivationEnd() !== null) {
            $duration->setEnd(
                new ilDateTime(
                    (string) $activation->getActivationEnd()->format('Y-m-d H:i:s'),
                    IL_CAL_DATETIME
                )
            );
        }

        $section_misc = new ilFormSectionHeaderGUI();
        $section_misc->setTitle($txt('lso_settings_misc'));
        $show_members_gallery = new ilCheckboxInputGUI($txt("members_gallery"), self::PROP_GALLERY);
        $show_members_gallery->setInfo($txt('lso_show_members_info'));

        $abstract = $this->initRTEInput(
            new ilTextAreaInputGUI($txt("abstract"), self::PROP_ABSTRACT)
        );
        $abstract_img = $this->initImgInput(
            new ilImageFileInputGUI($txt("abstract_img"), self::PROP_ABSTRACT_IMAGE)
        );
        $abstract_img->setImage($settings->getAbstractImage());

        $extro = $this->initRTEInput(
            new ilTextAreaInputGUI($txt("extro"), self::PROP_EXTRO)
        );
        $extro_img = $this->initImgInput(
            new ilImageFileInputGUI($txt("extro_img"), self::PROP_EXTRO_IMAGE)
        );
        $extro_img->setImage($settings->getExtroImage());

        $section_intro = new ilFormSectionHeaderGUI();
        $section_intro->setTitle($txt('lso_settings_intro'));
        $section_extro = new ilFormSectionHeaderGUI();
        $section_extro->setTitle($txt('lso_settings_extro'));

        $section_misc = new ilFormSectionHeaderGUI();
        $section_misc->setTitle($txt('obj_features'));
        $show_members_gallery = new ilCheckboxInputGUI($txt("members_gallery"), self::PROP_GALLERY);
        $show_members_gallery->setInfo($txt('lso_show_members_info'));

        $form->addItem($title);
        $form->addItem($desc);

        $form->addItem($section_avail);
        $form->addItem($online);
        $form->addItem($duration);

        $form->addItem($section_intro);
        $form->addItem($abstract);
        $form->addItem($abstract_img, true);

        $form->addItem($section_extro);
        $form->addItem($extro);
        $form->addItem($extro_img, true);

        $form->addItem($section_misc);
        $form->addItem($show_members_gallery);

        $form->addCommandButton(self::CMD_SAVE, $txt("save"));
        $form->addCommandButton(self::CMD_CANCEL, $txt("cancel"));

        return $form;
    }

    protected function fillForm(\ilPropertyFormGUI $form) : \ilPropertyFormGUI
    {
        $settings = $this->settings;
        $activation = $this->activation;
        $values = [
            self::PROP_TITLE => $this->obj_title,
            self::PROP_DESC => $this->obj_description,
            self::PROP_ABSTRACT => $settings->getAbstract(),
            self::PROP_EXTRO => $settings->getExtro(),
            self::PROP_ABSTRACT_IMAGE => $settings->getAbstractImage(),
            self::PROP_EXTRO_IMAGE => $settings->getExtroImage(),
            self::PROP_ONLINE => $activation->getIsOnline(),
            self::PROP_GALLERY => $settings->getMembersGallery()
        ];
        $form->setValuesByArray($values);
        return $form;
    }

    protected function addCommonFieldsToForm(\ilPropertyFormGUI $form)
    {
        $txt = function ($id) {
            return $this->lng->txt($id);
        };
        $section_appearance = new ilFormSectionHeaderGUI();
        $section_appearance->setTitle($txt('cont_presentation'));
        $form->addItem($section_appearance);
        $form_service = $this->obj_service->commonSettings()->legacyForm($form, $this->obj);
        $form = $form_service->addTitleIconVisibility();
        $form = $form_service->addTopActionsVisibility();
        $form = $form_service->addIcon();
        $form = $form_service->addTileImage();
    }


    protected function update()
    {
        $form = $this->buildForm();
        $this->addCommonFieldsToForm($form);
        if (!$form->checkInput()) {
            $form->setValuesByPost();
            ilUtil::sendFailure($this->lng->txt("msg_form_save_error"));
            return $form->getHTML();
        }

        $post = $_POST;
        $lso = $this->obj;

        $lso->setTitle($post[self::PROP_TITLE]);
        $lso->setDescription($post[self::PROP_DESC]);

        $settings = $this->settings
            ->withAbstract($post[self::PROP_ABSTRACT])
            ->withExtro($post[self::PROP_EXTRO])
            ->withMembersGallery((bool) $post[self::PROP_GALLERY])
        ;

        $inpt = $form->getItemByPostVar(self::PROP_AVAIL_PERIOD);
        $start = $inpt->getStart();
        $end = $inpt->getEnd();
        $activation = $this->activation
            ->withIsOnline((bool) $post[self::PROP_ONLINE]);

        if ($start) {
            $activation = $activation
                            ->withActivationStart(
                                \DateTime::createFromFormat(
                                    'Y-m-d H:i:s',
                                    (string) $start->get(IL_CAL_DATETIME)
                                )
                            );
        } else {
            $activation = $activation->withActivationStart();
        }
        if ($end) {
            $activation = $activation
                            ->withActivationEnd(
                                \DateTime::createFromFormat(
                                    'Y-m-d H:i:s',
                                    (string) $end->get(IL_CAL_DATETIME)
                                )
                            );
        } else {
            $activation = $activation->withActivationEnd();
        }

        $inpt = $form->getItemByPostVar(self::PROP_ABSTRACT_IMAGE);
        if ($inpt->getDeletionFlag()) {
            $settings = $settings->withDeletion(ilLearningSequenceFilesystem::IMG_ABSTRACT);
        } else {
            $img = $_POST[self::PROP_ABSTRACT_IMAGE];
            if ($img['size'] > 0) {
                $settings = $settings->withUpload($img, ilLearningSequenceFilesystem::IMG_ABSTRACT);
            }
        }

        $inpt = $form->getItemByPostVar(self::PROP_EXTRO_IMAGE);
        if ($inpt->getDeletionFlag()) {
            $settings = $settings->withDeletion(ilLearningSequenceFilesystem::IMG_EXTRO);
        } else {
            $img = $_POST[self::PROP_EXTRO_IMAGE];
            if ($img['size'] > 0) {
                $settings = $settings->withUpload($img, ilLearningSequenceFilesystem::IMG_EXTRO);
            }
        }

        $form_service = $this->obj_service->commonSettings()->legacyForm($form, $this->obj);
        $form_service->saveTitleIconVisibility();
        $form_service->saveTopActionsVisibility();
        $form_service->saveIcon();
        $form_service->saveTileImage();

        $lso->updateSettings($settings);
        $lso->updateActivation($activation);
        $lso->update();

        ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this);
    }
}
