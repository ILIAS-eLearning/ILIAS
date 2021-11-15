<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilChatroomSettingsGUI
 * @author  Jan Posselt <jposselt@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilChatroomSettingsGUI extends ilChatroomGUIHandler
{
    public function saveGeneral() : void
    {
        $formFactory = new ilChatroomFormFactory();
        $settingsForm = $formFactory->getSettingsForm();
        $this->obj_service->commonSettings()->legacyForm($settingsForm, $this->gui->object)->addTileImage();

        if (!$settingsForm->checkInput()) {
            $settingsForm->setValuesByPost();
            $this->general($settingsForm);
        } else {
            $this->gui->object->setTitle($settingsForm->getInput('title'));
            $this->gui->object->setDescription($settingsForm->getInput('desc'));

            $period = $settingsForm->getItemByPostVar('access_period');
            if ($period->getStart() && $period->getEnd()) {
                $this->gui->object->setAccessType(ilObjectActivation::TIMINGS_ACTIVATION);
                $this->gui->object->setAccessBegin($period->getStart()->get(IL_CAL_UNIX));
                $this->gui->object->setAccessEnd($period->getEnd()->get(IL_CAL_UNIX));
                $this->gui->object->setAccessVisibility((int) $settingsForm->getInput('access_visibility'));
            } else {
                $this->gui->object->setAccessType(ilObjectActivation::TIMINGS_DEACTIVATED);
            }

            $this->gui->object->update();
            $this->obj_service->commonSettings()->legacyForm($settingsForm, $this->gui->object)->saveTileImage();
            // @todo: Do not rely on raw post data
            $settings = $this->http->request()->getParsedBody();
            $room = ilChatroom::byObjectId($this->gui->object->getId());
            if (!$room) {
                $room = new ilChatroom();
                $settings['object_id'] = $this->gui->object->getId();
            }
            $room->saveSettings($settings);

            ilUtil::sendSuccess($this->ilLng->txt('saved_successfully'), true);
            $this->ilCtrl->redirect($this->gui, 'settings-general');
        }
    }

    public function general(ilPropertyFormGUI $settingsForm = null) : void
    {
        if (!ilChatroom::checkUserPermissions(['visible', 'read'], $this->gui->ref_id)) {
            $this->ilCtrl->setParameterByClass(ilRepositoryGUI::class, 'ref_id', ROOT_FOLDER_ID);
            $this->ilCtrl->redirectByClass(ilRepositoryGUI::class);
        }

        $chatSettings = new ilSetting('chatroom');
        if (!$chatSettings->get('chat_enabled', '0')) {
            ilUtil::sendInfo($this->ilLng->txt('server_disabled'), true);
        }

        $this->gui->switchToVisibleMode();

        $formFactory = new ilChatroomFormFactory();

        $room = ilChatroom::byObjectId($this->gui->object->getId());

        if (!$settingsForm) {
            $settingsForm = $formFactory->getSettingsForm();

            $settings = [
                'title' => $this->gui->object->getTitle(),
                'desc' => $this->gui->object->getDescription(),
                'access_period' => [
                    'start' => $this->gui->object->getAccessBegin() ? new ilDateTime(
                        $this->gui->object->getAccessBegin(),
                        IL_CAL_UNIX
                    ) : null,
                    'end' => $this->gui->object->getAccessEnd() ? new ilDateTime(
                        $this->gui->object->getAccessEnd(),
                        IL_CAL_UNIX
                    ) : null
                ],
                'access_visibility' => $this->gui->object->getAccessVisibility()
            ];

            $presentationHeader = new ilFormSectionHeaderGUI();
            $presentationHeader->setTitle($this->ilLng->txt('settings_presentation_header'));
            $settingsForm->addItem($presentationHeader);
            $this->obj_service->commonSettings()->legacyForm($settingsForm, $this->gui->object)->addTileImage();

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

        $this->mainTpl->setVariable('ADM_CONTENT', $settingsForm->getHtml());
    }

    public function executeDefault(string $requestedMethod) : void
    {
        $this->general();
    }
}
