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
 * Class ilChatroomSettingsGUI
 * @author  Jan Posselt <jposselt@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilChatroomSettingsGUI extends ilChatroomGUIHandler
{
    public function saveGeneral(): void
    {
        $formFactory = new ilChatroomFormFactory();
        $settingsForm = $formFactory->getSettingsForm($this->obj_service, $this->gui->getObject());

        if (!$settingsForm->checkInput()) {
            $settingsForm->setValuesByPost();
            $this->general($settingsForm);
        } else {
            $this->gui->getObject()->setTitle($settingsForm->getInput('title'));
            $this->gui->getObject()->setDescription($settingsForm->getInput('desc'));

            /** @var ilDateDurationInputGUI $period */
            $period = $settingsForm->getItemByPostVar('access_period');
            if ($period->getStart() && $period->getEnd()) {
                $this->gui->getObject()->setAccessType(ilObjectActivation::TIMINGS_ACTIVATION);
                $this->gui->getObject()->setAccessBegin($period->getStart()->get(IL_CAL_UNIX));
                $this->gui->getObject()->setAccessEnd($period->getEnd()->get(IL_CAL_UNIX));
                $this->gui->getObject()->setAccessVisibility((int) $settingsForm->getInput('access_visibility'));
            } else {
                $this->gui->getObject()->setAccessType(ilObjectActivation::TIMINGS_DEACTIVATED);
            }

            $this->gui->getObject()->update();
            $this->obj_service->commonSettings()->legacyForm($settingsForm, $this->gui->getObject())->saveTileImage();

            $room = ilChatroom::byObjectId($this->gui->getObject()->getId());
            $requestSettings = $room->getSettings();
            if (!$room) {
                $room = new ilChatroom();
                $requestSettings['object_id'] = $this->gui->getObject()->getId();
            }

            foreach ($requestSettings as $setting => &$value) {
                if ($settingsForm->getItemByPostVar($setting) !== null) {
                    $value = $settingsForm->getInput($setting);
                }
            }

            $room->saveSettings($requestSettings);

            $this->mainTpl->setOnScreenMessage('success', $this->ilLng->txt('saved_successfully'), true);
            $this->ilCtrl->redirect($this->gui, 'settings-general');
        }
    }

    public function general(ilPropertyFormGUI $settingsForm = null): void
    {
        if (!ilChatroom::checkUserPermissions(['visible', 'read'], $this->gui->getRefId())) {
            $this->ilCtrl->setParameterByClass(ilRepositoryGUI::class, 'ref_id', ROOT_FOLDER_ID);
            $this->ilCtrl->redirectByClass(ilRepositoryGUI::class);
        }

        $chatSettings = new ilSetting('chatroom');
        if (!$chatSettings->get('chat_enabled', '0')) {
            $this->mainTpl->setOnScreenMessage('info', $this->ilLng->txt('server_disabled'), true);
        }

        $this->gui->switchToVisibleMode();

        $formFactory = new ilChatroomFormFactory();

        $room = ilChatroom::byObjectId($this->gui->getObject()->getId());

        if (!$settingsForm) {
            $settingsForm = $formFactory->getSettingsForm($this->obj_service, $this->gui->getObject());

            $settings = [
                'title' => $this->gui->getObject()->getTitle(),
                'desc' => $this->gui->getObject()->getDescription(),
                'access_period' => [
                    'start' => $this->gui->getObject()->getAccessBegin() ? (new ilDateTime(
                        $this->gui->getObject()->getAccessBegin(),
                        IL_CAL_UNIX
                    ))->get(IL_CAL_DATETIME) : '',
                    'end' => $this->gui->getObject()->getAccessEnd() ? (new ilDateTime(
                        $this->gui->getObject()->getAccessEnd(),
                        IL_CAL_UNIX
                    ))->get(IL_CAL_DATETIME) : ''
                ],
                'access_visibility' => (bool) $this->gui->getObject()->getAccessVisibility()
            ];

            if ($room) {
                ilChatroomFormFactory::applyValues(
                    $settingsForm,
                    array_merge($settings, $room->getSettings())
                );
            } else {
                ilChatroomFormFactory::applyValues($settingsForm, $settings);
            }
        }

        $settingsForm->setTitle($this->ilLng->txt('settings_title'));
        $settingsForm->addCommandButton('settings-saveGeneral', $this->ilLng->txt('save'));
        $settingsForm->setFormAction($this->ilCtrl->getFormAction($this->gui, 'settings-saveGeneral'));

        $this->mainTpl->setVariable('ADM_CONTENT', $settingsForm->getHTML());
    }

    public function executeDefault(string $requestedMethod): void
    {
        $this->general();
    }
}
