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

declare(strict_types=1);

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

        $settingsForm = $formFactory->getSettingsForm(
            $this->gui,
            $this->ilCtrl
        );

        $result = (new \ILIAS\Data\Factory())->error($this->ilLng->txt('form_input_not_valid'));
        if ($this->http->request()->getMethod() === 'POST') {
            $settingsForm = $settingsForm->withRequest($this->http->request());
            $result = $settingsForm->getInputGroup()->getContent();
        }

        if (!$result->isOK()) {
            $this->mainTpl->setOnScreenMessage('failure', $result->error());
            $this->general($settingsForm);
            return;
        }

        $values = $result->value();

        $this->gui->getObject()->getObjectProperties()->storePropertyTitleAndDescription(
            $values[ilChatroomFormFactory::PROP_TITLE_AND_DESC]
        );
        $this->gui->getObject()->getObjectProperties()->storePropertyIsOnline(
            $values[ilChatroomFormFactory::PROP_ONLINE_STATUS]
        );
        $this->gui->getObject()->getObjectProperties()->storePropertyTileImage(
            $values[ilChatroomFormFactory::PROP_TILE_IMAGE]
        );

        $room = ilChatroom::byObjectId($this->gui->getObject()->getId());
        $mutated_settings = $room->getSettings();
        if (!$room) {
            $room = new ilChatroom();
            $mutated_settings['object_id'] = $this->gui->getObject()->getId();
        }

        foreach ($mutated_settings as $setting => &$value) {
            if (array_key_exists($setting, $values)) {
                $value = $values[$setting];
            }
        }
        unset($value);

        $room->saveSettings($mutated_settings);

        $this->mainTpl->setOnScreenMessage('success', $this->ilLng->txt('saved_successfully'), true);
        $this->ilCtrl->redirect($this->gui, 'settings-general');
    }

    public function general(\ILIAS\UI\Component\Input\Container\Form\Form $settingsForm = null): void
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

        if ($settingsForm === null) {
            $settings = [
                'title' => $this->gui->getObject()->getTitle(),
                'desc' => $this->gui->getObject()->getDescription(),
            ];
            if ($room) {
                $settings = array_merge($settings, $room->getSettings());
            }

            $settingsForm = $formFactory->getSettingsForm(
                $this->gui,
                $this->ilCtrl,
                $settings
            );
        }

        $this->mainTpl->setVariable('ADM_CONTENT', $this->uiRenderer->render($settingsForm));
    }

    public function executeDefault(string $requestedMethod): void
    {
        $this->general();
    }
}
