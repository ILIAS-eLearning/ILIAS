<?php declare(strict_types=1);

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
 * Certificate Settings.
 * @author            Helmut Schottmüller <ilias@aurealis.de>
 * @version           $Id$
 * @ilCtrl_Calls      ilObjCertificateSettingsGUI: ilPermissionGUI
 * @ilCtrl_IsCalledBy ilObjCertificateSettingsGUI: ilAdministrationGUI
 * @ingroup           ServicesCertificate
 */
class ilObjCertificateSettingsGUI extends ilObjectGUI
{
    protected \ILIAS\HTTP\GlobalHttpState $httpState;
    protected \ILIAS\FileUpload\FileUpload $upload;

    public function __construct($data, int $id = 0, bool $call_by_reference = true, bool $prepare_output = true)
    {
        global $DIC;

        parent::__construct($data, $id, $call_by_reference, $prepare_output);

        $this->httpState = $DIC->http();
        $this->upload = $DIC->upload();
        $this->type = 'cert';
        $this->lng->loadLanguageModule('certificate');
        $this->lng->loadLanguageModule('trac');
    }

    public function executeCommand() : void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();

        if (!$this->rbac_system->checkAccess('read', $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('no_permission'), $this->error->WARNING);
        }

        switch (strtolower($next_class)) {
            case strtolower(ilPermissionGUI::class):
                $this->tabs_gui->setTabActive('perm_settings');
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            default:
                if (!$cmd || $cmd === 'view') {
                    $cmd = 'settings';
                }

                $this->$cmd();
                break;
        }
    }

    public function getAdminTabs() : void
    {
        if ($this->rbac_system->checkAccess('visible,read', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                'settings',
                $this->ctrl->getLinkTarget($this, 'settings'),
                ['settings', 'view']
            );
        }

        if ($this->rbac_system->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                'perm_settings',
                $this->ctrl->getLinkTargetByClass(ilPermissionGUI::class, 'perm'),
                [],
                strtolower(ilPermissionGUI::class)
            );
        }
    }

    public function settings() : void
    {
        $this->tabs_gui->setTabActive('settings');
        $form_settings = new ilSetting('certificate');

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt('certificate_settings'));

        $active = new ilCheckboxInputGUI($this->lng->txt('active'), 'active');
        $active->setChecked((bool) $form_settings->get('active', '0'));
        $form->addItem($active);

        $info = new ilNonEditableValueGUI($this->lng->txt('info'), 'info');
        $info->setValue($this->lng->txt('certificate_usage'));
        $form->addItem($info);

        $bgimage = new ilImageFileInputGUI($this->lng->txt('certificate_background_image'), 'background');
        $bgimage->setRequired(false);

        if (
            $this->upload->hasUploads() &&
            $this->httpState->request()->getMethod() === 'POST' &&
            $bgimage->checkInput()
        ) {
            if (!$this->upload->hasBeenProcessed()) {
                $this->upload->process();
            }
            
            if (is_array($this->upload->getResults()) && $this->upload->getResults() !== []) {
                $results = $this->upload->getResults();
                $file = array_pop($results);
                if ($file->isOK()) {
                    $result = $this->object->uploadBackgroundImage($file->getPath());
                    if ($result === false) {
                        $bgimage->setAlert($this->lng->txt('certificate_error_upload_bgimage'));
                    }
                }
            }
        }

        if ($this->object->hasBackgroundImage()) {
            ilWACSignedPath::setTokenMaxLifetimeInSeconds(15);
            $bgimage->setImage(ilWACSignedPath::signFile($this->object->getDefaultBackgroundImagePathWeb()));
        }
        $bgimage->setInfo($this->lng->txt('default_background_info'));
        $form->addItem($bgimage);
        $format = new ilSelectInputGUI($this->lng->txt('certificate_page_format'), 'pageformat');
        $defaultformats = [
            'a4' => $this->lng->txt('certificate_a4'), // (297 mm x 210 mm)
            'a4landscape' => $this->lng->txt('certificate_a4_landscape'), // (210 mm x 297 mm)',
            'a5' => $this->lng->txt('certificate_a5'), // (210 mm x 148.5 mm)
            'a5landscape' => $this->lng->txt('certificate_a5_landscape'), // (148.5 mm x 210 mm)
            'letter' => $this->lng->txt('certificate_letter'), // (11 inch x 8.5 inch)
            'letterlandscape' => $this->lng->txt('certificate_letter_landscape') // (11 inch x 8.5 inch)
        ];
        $format->setOptions($defaultformats);
        $format->setValue($form_settings->get("pageformat", ''));
        $format->setInfo($this->lng->txt("certificate_page_format_info"));
        $form->addItem($format);

        if ($this->rbac_system->checkAccess('write', $this->object->getRefId())) {
            $form->addCommandButton('save', $this->lng->txt('save'));
        }

        if (!ilObjUserTracking::_enabledLearningProgress()) {
            ilAdministrationSettingsFormHandler::addFieldsToForm(
                ilAdministrationSettingsFormHandler::FORM_CERTIFICATE,
                $form,
                $this
            );
        }

        $persistentCertificateMode = new ilRadioGroupInputGUI(
            $this->lng->txt('persistent_certificate_mode'),
            'persistent_certificate_mode'
        );
        $persistentCertificateMode->setRequired(true);

        $cronJobMode = new ilRadioOption(
            $this->lng->txt('persistent_certificate_mode_cron'),
            'persistent_certificate_mode_cron'
        );
        $cronJobMode->setInfo($this->lng->txt('persistent_certificate_mode_cron_info'));

        $instantMode = new ilRadioOption(
            $this->lng->txt('persistent_certificate_mode_instant'),
            'persistent_certificate_mode_instant'
        );
        $instantMode->setInfo($this->lng->txt('persistent_certificate_mode_instant_info'));

        $persistentCertificateMode->addOption($cronJobMode);
        $persistentCertificateMode->addOption($instantMode);

        $persistentCertificateMode->setValue($form_settings->get(
            'persistent_certificate_mode',
            'persistent_certificate_mode_cron'
        ));

        $form->addItem($persistentCertificateMode);

        $this->tpl->setContent($form->getHTML());

        if (strcmp($this->ctrl->getCmd(), 'save') === 0) {
            $backgroundDelete = $this->httpState->wrapper()->post()->has('background_delete') && $this->httpState->wrapper()->post()->retrieve(
                'background_delete',
                $this->refinery->kindlyTo()->bool()
            );
            if ($backgroundDelete) {
                $this->object->deleteBackgroundImage();
            }
        }
    }

    public function save() : void
    {
        $form_settings = new ilSetting("certificate");

        $mode = $this->httpState->wrapper()->post()->retrieve(
            'persistent_certificate_mode',
            $this->refinery->kindlyTo()->string()
        );
        $previousMode = $form_settings->get('persistent_certificate_mode', 'persistent_certificate_mode_cron');
        if ($mode !== $previousMode && $mode === 'persistent_certificate_mode_instant') {
            $cron = new ilCertificateCron();
            $cron->init();
            $cron->run();
        }

        $form_settings->set(
            'pageformat',
            $this->httpState->wrapper()->post()->retrieve('pageformat', $this->refinery->kindlyTo()->string())
        );
        $form_settings->set(
            'active',
            (string) ($this->httpState->wrapper()->post()->has('active') && $this->httpState->wrapper()->post()->retrieve(
                'active',
                $this->refinery->kindlyTo()->bool()
            ))
        );
        $form_settings->set('persistent_certificate_mode', $mode);

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'));
        $this->settings();
    }
}
