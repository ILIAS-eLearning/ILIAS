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
 * Class ilLTIConsumeProviderSettingsGUI
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Modules/LTIConsumer
 */
class ilLTIConsumeProviderSettingsGUI
{
    public const CMD_SHOW_SETTINGS = 'showSettings';
    public const CMD_SAVE_SETTINGS = 'saveSettings';

    /**
     * @var ilObjLTIConsumer
     */
    protected ilObjLTIConsumer $object;

    /**
     * @var ilLTIConsumerAccess
     */
    protected ilLTIConsumerAccess $access;

    /**
     * ilLTIConsumerAccess constructor.
     */
    public function __construct(ilObjLTIConsumer $object, ilLTIConsumerAccess $access)
    {
        $this->object = $object;
        $this->access = $access;
    }

    /**
     * Execute Command
     */
    public function executeCommand(): void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        switch ($DIC->ctrl()->getNextClass()) {
            default:

                $command = $DIC->ctrl()->getCmd(self::CMD_SHOW_SETTINGS) . 'Cmd';
                $this->{$command}();
        }
    }

    protected function showSettingsCmd(ilLTIConsumeProviderFormGUI $form = null): void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        if ($form === null) {
            $form = $this->buildForm($this->object->getProvider());
        }

        $DIC->ui()->mainTemplate()->setContent($form->getHTML());
    }

    protected function saveSettingsCmd(): void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $provider = $this->object->getProvider();
        $form = $this->buildForm($provider);

        if ($form->checkInput()) {
            $form->initProvider($provider);
            $this->object->getProvider()->save();
            if (ilLTIConsumerSettingsGUI::isUserDynamicRegistrationTransaction($provider)) {
                $this->object->setTitle($provider->getTitle());
                $this->object->update();
                ilSession::clear('lti_dynamic_registration_client_id');
                ilSession::clear('custom_params');
            }
            $DIC->ctrl()->redirect($this, self::CMD_SHOW_SETTINGS);
        }

        $this->showSettingsCmd($form);
    }

    /**
     * @throws ilCtrlException
     */
    protected function buildForm(ilLTIConsumeProvider $provider): ilLTIConsumeProviderFormGUI
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $form = new ilLTIConsumeProviderFormGUI($provider);

        $form->initForm(
            $DIC->ctrl()->getFormAction($this),
            self::CMD_SAVE_SETTINGS,
            self::CMD_SHOW_SETTINGS
        );

        return $form;
    }
}
