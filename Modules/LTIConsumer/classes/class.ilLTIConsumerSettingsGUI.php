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
 * Class ilLTIConsumerSettingsGUI
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Modules/LTIConsumer
 *
 * @ilCtrl_Calls ilLTIConsumerSettingsGUI: ilLTIConsumeProviderSettingsGUI
 * @ilCtrl_Calls ilLTIConsumerSettingsGUI: ilCertificateGUI
 */
class ilLTIConsumerSettingsGUI
{
    public const SUBTAB_ID_OBJECT_SETTINGS = 'subtab_object_settings';
    public const SUBTAB_ID_PROVIDER_SETTINGS = 'subtab_provider_settings';
    public const SUBTAB_ID_CERTIFICATE = 'subtab_certificate';

    public const CMD_SHOW_SETTINGS = 'showSettings';
    public const CMD_SAVE_SETTINGS = 'saveSettings';
    public const CMD_DELIVER_CERTIFICATE = 'deliverCertificate';

    /**
     * @var ilObjLTIConsumer
     */
    protected ilObjLTIConsumer $object;

    /**
     * @var ilLTIConsumerAccess
     */
    protected ilLTIConsumerAccess $access;
    private \ilGlobalTemplateInterface $main_tpl;

    /**
     * ilLTIConsumerAccess constructor.
     */
    public function __construct(ilObjLTIConsumer $object, ilLTIConsumerAccess $access)
    {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->object = $object;
        $this->access = $access;
    }

    protected function initSubTabs(): void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        $DIC->language()->loadLanguageModule('lti');

        if (!self::isUserDynamicRegistrationTransaction($this->object->getProvider())) {
            $DIC->tabs()->addSubTab(
                self::SUBTAB_ID_OBJECT_SETTINGS,
                $DIC->language()->txt(self::SUBTAB_ID_OBJECT_SETTINGS),
                $DIC->ctrl()->getLinkTarget($this)
            );
        }

        if ($this->needsProviderSettingsSubTab()) {
            $DIC->tabs()->addSubTab(
                self::SUBTAB_ID_PROVIDER_SETTINGS,
                $DIC->language()->txt(self::SUBTAB_ID_PROVIDER_SETTINGS),
                $DIC->ctrl()->getLinkTargetByClass(ilLTIConsumeProviderSettingsGUI::class)
            );
        }

        $validator = new ilCertificateActiveValidator();

        if ($validator->validate()) {
            $DIC->tabs()->addSubTab(
                self::SUBTAB_ID_CERTIFICATE,
                $DIC->language()->txt(self::SUBTAB_ID_CERTIFICATE),
                $DIC->ctrl()->getLinkTargetByClass(ilCertificateGUI::class, 'certificateEditor')
            );
        }
    }

    protected function needsProviderSettingsSubTab(): bool
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        if ($this->object->getProvider()->isGlobal()) {
            return false;
        }

        if ($this->object->getProvider()->getCreator() != $DIC->user()->getId()) {
            return false;
        }

        return true;
    }

    public static function isUserDynamicRegistrationTransaction(ilLTIConsumeProvider $provider): bool
    {
        global $DIC;
        if (!ilSession::has('lti_dynamic_registration_client_id')) {
            return false;
        }
        if ($provider->getCreator() != $DIC->user()->getId()) {
            return false;
        }
        if ($provider->getClientId() == ilSession::get('lti_dynamic_registration_client_id')) {
            return true;
        }
        return false;
    }

    /**
     * Execute Command
     */
    public function executeCommand(): void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $this->initSubTabs();

        $nc = $DIC->ctrl()->getNextClass();

        switch ($nc) {
            case strtolower(ilCertificateGUI::class):

                $validator = new ilCertificateActiveValidator();

                if (!$validator->validate()) {
                    throw new ilCmiXapiException('access denied!');
                }

                $DIC->tabs()->activateSubTab(self::SUBTAB_ID_CERTIFICATE);

                $guiFactory = new ilCertificateGUIFactory();
                $gui = $guiFactory->create($this->object);

                $DIC->ctrl()->forwardCommand($gui);

                break;

            case strtolower(ilLTIConsumeProviderSettingsGUI::class):

                $DIC->tabs()->activateSubTab(self::SUBTAB_ID_PROVIDER_SETTINGS);

                $gui = new ilLTIConsumeProviderSettingsGUI($this->object, $this->access);
                $DIC->ctrl()->forwardCommand($gui);
                break;

            default:
                if (self::isUserDynamicRegistrationTransaction($this->object->getProvider()) && $this->needsProviderSettingsSubTab()) {
                    $DIC->tabs()->activateSubTab(self::SUBTAB_ID_PROVIDER_SETTINGS);
                    $gui = new ilLTIConsumeProviderSettingsGUI($this->object, $this->access);
                    $DIC->ctrl()->forwardCommand($gui);
                } else {
                    $DIC->tabs()->activateSubTab(self::SUBTAB_ID_OBJECT_SETTINGS);
                    $command = $DIC->ctrl()->getCmd(self::CMD_SHOW_SETTINGS) . 'Cmd';
                    $this->{$command}();
                }
        }
    }

    protected function showSettingsCmd(ilLTIConsumerSettingsFormGUI $form = null): void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        if ($form === null) {
            $form = $this->buildForm();
        }

        $DIC->ui()->mainTemplate()->setContent($form->getHTML());
    }

    protected function saveSettingsCmd(): void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $form = $this->buildForm();

        if ($form->checkInput()) {
            $oldMasteryScore = $this->object->getMasteryScore();

            $form->initObject($this->object);
            $this->object->update();

            if ($oldMasteryScore !== $this->object->getMasteryScore()) {
                ilLPStatusWrapper::_refreshStatus($this->object->getId());
            }

            $this->main_tpl->setOnScreenMessage('success', $DIC->language()->txt('msg_obj_modified'), true);
            $DIC->ctrl()->redirect($this, self::CMD_SHOW_SETTINGS);
        }

        $this->showSettingsCmd($form);
    }

    protected function buildForm(): \ilLTIConsumerSettingsFormGUI
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $form = new ilLTIConsumerSettingsFormGUI(
            $this->object,
            $DIC->ctrl()->getFormAction($this),
            self::CMD_SAVE_SETTINGS,
            self::CMD_SHOW_SETTINGS
        );

        return $form;
    }

    protected function deliverCertificateCmd(): void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $validator = new ilCertificateDownloadValidator();

        if (!$validator->isCertificateDownloadable($DIC->user()->getId(), $this->object->getId())) {
            $this->main_tpl->setOnScreenMessage('failure', $DIC->language()->txt("permission_denied"), true);
            $DIC->ctrl()->redirectByClass(ilObjLTIConsumerGUI::class, ilObjLTIConsumerGUI::DEFAULT_CMD);
        }

        $repository = new ilUserCertificateRepository();

        $certLogger = $DIC->logger()->cert();
        $pdfGenerator = new ilPdfGenerator($repository, $certLogger);

        $pdfAction = new ilCertificatePdfAction(
            $certLogger,
            $pdfGenerator,
            new ilCertificateUtilHelper(),
            $DIC->language()->txt('error_creating_certificate_pdf')
        );

        $pdfAction->downloadPdf($DIC->user()->getId(), $this->object->getId());
    }
}
