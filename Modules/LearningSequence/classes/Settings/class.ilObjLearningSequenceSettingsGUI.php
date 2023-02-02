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

use ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper;

class ilObjLearningSequenceSettingsGUI
{
    public const PROP_TITLE = 'title';
    public const PROP_DESC = 'desc';
    public const PROP_ONLINE = 'online';
    public const PROP_AVAIL_PERIOD = 'online_period';
    public const PROP_GALLERY = 'gallery';

    public const CMD_EDIT = "settings";
    public const CMD_SAVE = "update";
    public const CMD_CANCEL = "cancel";

    public const CMD_OLD_INTRO = "viewlegacyi";
    public const CMD_OLD_EXTRO = "viewlegacye";

    public function __construct(
        ilObjLearningSequence $obj,
        ilCtrl $ctrl,
        ilLanguage $lng,
        ilGlobalTemplateInterface $tpl,
        ilObjectService $obj_service,
        ArrayBasedRequestWrapper $post_wrapper,
        ILIAS\Refinery\Factory $refinery,
        ilToolbarGUI $toolbar
    ) {
        $this->obj = $obj;
        $this->ctrl = $ctrl;
        $this->lng = $lng;
        $this->tpl = $tpl;
        $this->obj_service = $obj_service;
        $this->post_wrapper = $post_wrapper;
        $this->refinery = $refinery;

        $this->settings = $obj->getLSSettings();
        $this->activation = $obj->getLSActivation();
        $this->obj_title = $obj->getTitle();
        $this->obj_description = $obj->getDescription();

        $this->lng->loadLanguageModule('content');
        $this->lng->loadLanguageModule('obj');
        $this->toolbar = $toolbar;
    }

    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd('settings');

        switch ($cmd) {
            case self::CMD_EDIT:
            case self::CMD_SAVE:
            case self::CMD_CANCEL:
                $content = $this->$cmd();
                break;
            case self::CMD_OLD_INTRO:
            case self::CMD_OLD_EXTRO:
                $content = $this->showLegacyPage($cmd);
                break;

            default:
                throw new ilException("ilObjLearningSequenceSettingsGUI: Command not supported: $cmd");
        }
        $this->tpl->setContent($content);
    }

    protected function settings(): string
    {
        $this->addLegacypagesToToolbar();
        $this->tpl->setOnScreenMessage("info", $this->lng->txt("lso_intropages_deprecationhint"));

        $form = $this->buildForm();
        $this->fillForm($form);
        $this->addCommonFieldsToForm($form);
        return $form->getHTML();
    }

    protected function cancel(): void
    {
        $this->ctrl->redirectByClass(ilObjLearningSequenceGUI::class);
    }


    //TODO: remove in release 9
    public function addLegacypagesToToolbar(): void
    {
        $this->toolbar->addButton(
            $this->lng->txt("lso_settings_old_intro"),
            $this->ctrl->getLinkTarget($this, self::CMD_OLD_INTRO)
        );

        $this->toolbar->addButton(
            $this->lng->txt("lso_settings_old_extro"),
            $this->ctrl->getLinkTarget($this, self::CMD_OLD_EXTRO)
        );
    }

    protected function showLegacyPage(string $cmd): string
    {
        $this->toolbar->addButton(
            $this->lng->txt('back'),
            $this->ctrl->getLinkTarget($this, self::CMD_EDIT)
        );

        $out = [];
        $settings = $this->settings;
        if ($cmd === self::CMD_OLD_INTRO) {
            $out[] = $settings->getAbstract();
            $img = $settings->getAbstractImage();
            if ($img) {
                $out[] = '<img src="' . $img . '"/>';
            }
        }
        if ($cmd === self::CMD_OLD_EXTRO) {
            $out[] = $settings->getExtro();
            $img = $settings->getExtroImage();
            if ($img) {
                $out[] = '<img src="' . $img . '"/>';
            }
        }

        return implode('<hr>', $out);
    }

    protected function buildForm(): ilPropertyFormGUI
    {
        $txt = fn ($id) => $this->lng->txt($id);
        $settings = $this->settings;
        $activation = $this->activation;

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
                    $activation->getActivationStart()->format('Y-m-d H:i:s'),
                    IL_CAL_DATETIME
                )
            );
        }
        if ($activation->getActivationEnd() !== null) {
            $duration->setEnd(
                new ilDateTime(
                    $activation->getActivationEnd()->format('Y-m-d H:i:s'),
                    IL_CAL_DATETIME
                )
            );
        }

        $section_misc = new ilFormSectionHeaderGUI();
        $section_misc->setTitle($txt('obj_features'));
        $show_members_gallery = new ilCheckboxInputGUI($txt("members_gallery"), self::PROP_GALLERY);
        $show_members_gallery->setInfo($txt('lso_show_members_info'));

        $form->addItem($title);
        $form->addItem($desc);

        $form->addItem($section_avail);
        $form->addItem($online);
        $form->addItem($duration);
        $form->addItem($section_misc);
        $form->addItem($show_members_gallery);

        $form->addCommandButton(self::CMD_SAVE, $txt("save"));
        $form->addCommandButton(self::CMD_CANCEL, $txt("cancel"));

        return $form;
    }

    protected function fillForm(ilPropertyFormGUI $form): ilPropertyFormGUI
    {
        $settings = $this->settings;
        $activation = $this->activation;
        $values = [
            self::PROP_TITLE => $this->obj_title,
            self::PROP_DESC => $this->obj_description,
            self::PROP_ONLINE => $activation->getIsOnline(),
            self::PROP_GALLERY => $settings->getMembersGallery()
        ];
        $form->setValuesByArray($values);
        return $form;
    }

    protected function addCommonFieldsToForm(ilPropertyFormGUI $form): void
    {
        $txt = fn ($id) => $this->lng->txt($id);
        $section_appearance = new ilFormSectionHeaderGUI();
        $section_appearance->setTitle($txt('cont_presentation'));
        $form->addItem($section_appearance);
        $form_service = $this->obj_service->commonSettings()->legacyForm($form, $this->obj);
        $form_service->addTitleIconVisibility();
        $form_service->addTopActionsVisibility();
        $form_service->addIcon();
        $form_service->addTileImage();
    }

    protected function update(): ?string
    {
        $form = $this->buildForm();
        $this->addCommonFieldsToForm($form);
        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->tpl->setOnScreenMessage("failure", $this->lng->txt("msg_form_save_error"));
            return $form->getHTML();
        }

        $lso = $this->obj;

        $lso->setTitle($this->post_wrapper->retrieve(self::PROP_TITLE, $this->refinery->kindlyTo()->string()));
        $lso->setDescription($this->post_wrapper->retrieve(self::PROP_DESC, $this->refinery->kindlyTo()->string()));

        $settings = $this->settings
            ->withMembersGallery(
                $this->post_wrapper->retrieve(
                    self::PROP_GALLERY,
                    $this->refinery->byTrying([
                        $this->refinery->kindlyTo()->bool(),
                        $this->refinery->always(false)
                    ])
                )
            );

        $inpt = $form->getItemByPostVar(self::PROP_AVAIL_PERIOD);
        $start = $inpt->getStart();
        $end = $inpt->getEnd();
        $activation = $this->activation
            ->withIsOnline(
                $this->post_wrapper->retrieve(
                    self::PROP_ONLINE,
                    $this->refinery->byTrying([
                        $this->refinery->kindlyTo()->bool(),
                        $this->refinery->always(false)
                    ])
                )
            );

        if ($start) {
            $activation = $activation
                ->withActivationStart(DateTime::createFromFormat('Y-m-d H:i:s', (string) $start->get(IL_CAL_DATETIME)));
        } else {
            $activation = $activation->withActivationStart();
        }
        if ($end) {
            $activation = $activation
                ->withActivationEnd(DateTime::createFromFormat('Y-m-d H:i:s', (string) $end->get(IL_CAL_DATETIME)));
        } else {
            $activation = $activation->withActivationEnd();
        }

        $form_service = $this->obj_service->commonSettings()->legacyForm($form, $this->obj);
        $form_service->saveTitleIconVisibility();
        $form_service->saveTopActionsVisibility();
        $form_service->saveIcon();
        $form_service->saveTileImage();

        $lso->updateSettings($settings);
        $lso->updateActivation($activation);
        $lso->update();

        $this->tpl->setOnScreenMessage("success", $this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this);
        return null;
    }
}
