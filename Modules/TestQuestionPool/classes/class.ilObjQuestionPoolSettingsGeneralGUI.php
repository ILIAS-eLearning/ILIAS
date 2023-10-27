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

use ILIAS\UI\Component\Input\Container\Form\Standard as Form;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Renderer as UIRenderer;
use Psr\Http\Message\ServerRequestInterface as HttpRequest;
use ILIAS\UI\Component\MessageBox\MessageBox;

/**
 * GUI class that manages the editing of general test question pool settings/properties
 * shown on "general" subtab
 *
 * @author         Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package        Modules/TestQuestionPool
 *
 * @ilCtrl_Calls   ilObjQuestionPoolSettingsGeneralGUI: ilPropertyFormGUI
 */
class ilObjQuestionPoolSettingsGeneralGUI
{
    public const CMD_SHOW_GENERAL_FORM = 'showForm';
    public const CMD_SAVE_GENERAL_FORM = 'saveForm';
    public const TAB_COMMON_SETTINGS = 'settings';
    protected ilObjQuestionPool|ilObject $poolOBJ;

    public function __construct(
        private readonly ilCtrl $ctrl,
        private readonly ilAccessHandler $access,
        private readonly ilLanguage $lng,
        private readonly ilGlobalTemplateInterface $tpl,
        private readonly ilTabsGUI $tabs,
        private readonly ilObjQuestionPoolGUI $poolGUI,
        private readonly Refinery $refinery,
        private readonly UIFactory $ui_factory,
        private readonly UIRenderer $ui_renderer,
        private readonly HttpRequest $http_request,
    ) {
        $this->poolOBJ = $poolGUI->getObject();
    }

    /**
     * Command Execution
     */
    public function executeCommand(): void
    {
        // allow only write access

        if (!$this->access->checkAccess('write', '', $this->poolGUI->getRefId())) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('cannot_edit_question_pool'), true);
            $this->ctrl->redirectByClass('ilObjQuestionPoolGUI', 'infoScreen');
        }

        $this->tabs->activateTab('settings');
        $this->tabs->activateSubTab('qpl_settings_subtab_general');

        // process command

        $nextClass = $this->ctrl->getNextClass();

        switch ($nextClass) {
            default:
                $cmd = $this->ctrl->getCmd(self::CMD_SHOW_GENERAL_FORM) . 'Cmd';
                $this->$cmd();
        }
    }

    private function showFormCmd(Form $form = null): void
    {
        $this->tabs->activateSubTab(self::TAB_COMMON_SETTINGS);
        if ($form === null) {
            $form = $this->buildForm();
        }
        $this->tpl->setContent($this->ui_renderer->render($form));
    }

    private function saveFormCmd(): void
    {
        $form = $this->buildForm();
        $form = $form->withRequest($this->http_request);

        $result = $form->getInputGroup()->getContent();

        if ($result->isOK()) {
            $values = $result->value();
            $this->performSaveForm($values);
            $this->tpl->setOnScreenMessage(MessageBox::SUCCESS, $this->lng->txt("msg_obj_modified"), true);
            $this->ctrl->redirect($this, self::CMD_SHOW_GENERAL_FORM);
        } else {
            $this->tpl->setOnScreenMessage(MessageBox::FAILURE, $this->lng->txt('form_input_not_valid'));
            $this->showFormCmd($form);
        }
    }

    private function performSaveForm($data): void
    {
        $md_obj = new ilMD($this->poolOBJ->getId(), 0, "qpl");
        $md_section = $md_obj->getGeneral();

        $title_and_description = $data['general_settings']['title_and_description'] ?? null;
        if ($title_and_description instanceof ilObjectPropertyTitleAndDescription) {
            $this->poolOBJ->getObjectProperties()->storePropertyTitleAndDescription(
                $title_and_description
            );
        }

        $online = $data['availability']['online'] ?? null;
        $this->poolOBJ->getObjectProperties()->storePropertyIsOnline(
            $online ?? $this->poolOBJ->getObjectProperties()->getPropertyIsOnline()->withOffline()
        );

        $display_settings = $data['display_settings'] ?? [];
        if (isset($display_settings['tile_image'])) {
            $this->poolOBJ->getObjectProperties()->storePropertyTileImage($display_settings['tile_image']);
        }

        $skill_service = $data['skill_service'] ?? [];
        $this->poolOBJ->setSkillServiceEnabled($skill_service['skill_service'] ?? false);

        $additional_features = $data['additional_features'] ?? [];
        $this->poolOBJ->setShowTaxonomies($additional_features['showTax'] ?? false);

        $this->poolOBJ->saveToDb();
    }

    private function buildForm(): Form
    {
        $items = [];

        $md_obj = new ilMD($this->poolOBJ->getId(), 0, "qpl");
        $md_section = $md_obj->getGeneral();

        $title_and_description = $this->poolOBJ->getObjectProperties()->getPropertyTitleAndDescription()->toForm(
            $this->lng,
            $this->ui_factory->input()->field(),
            $this->refinery
        );

        $items['general_settings'] = $this->ui_factory->input()->field()->section(
            [
                'title_and_description' => $title_and_description
            ],
            $this->lng->txt('qpl_form_general_settings')
        );

        $online = $this->poolOBJ->getObjectProperties()->getPropertyIsOnline()->toForm(
            $this->lng,
            $this->ui_factory->input()->field(),
            $this->refinery
        );
        $availability = $this->ui_factory->input()->field()->section(
            ['online' => $online],
            $this->lng->txt('qpl_settings_availability')
        );
        $items['availability'] = $availability;

        $timg = $this->poolOBJ->getObjectProperties()->getPropertyTileImage()->toForm(
            $this->lng,
            $this->ui_factory->input()->field(),
            $this->refinery
        );
        $items['display_settings'] = $this->ui_factory->input()->field()->section(
            ['tile_image' => $timg],
            $this->lng->txt('tst_presentation_settings_section')
        );

        if (ilObjQuestionPool::isSkillManagementGloballyActivated()) {
            $skill_service = $this->ui_factory->input()->field()->checkbox(
                $this->lng->txt('tst_activate_skill_service')
            )->withValue($this->poolOBJ->isSkillServiceEnabled());

            $skill_service_section = $this->ui_factory->input()->field()->section(
                ['skill_service' => $skill_service],
                $this->lng->txt('obj_features')
            );
            $items['skill_service'] = $skill_service_section;
        }

        $showTax = $this->ui_factory->input()->field()->checkbox(
            $this->lng->txt('qpl_settings_general_form_property_show_taxonomies')
        )->withValue($this->poolOBJ->getShowTaxonomies());

        $additional_features_section = $this->ui_factory->input()->field()->section(
            ['showTax' => $showTax],
            $this->lng->txt('obj_features')
        );
        $items['additional_features'] = $additional_features_section;

        return $this->ui_factory->input()->container()->form()->standard(
            $this->ctrl->getFormAction($this, self::CMD_SAVE_GENERAL_FORM),
            $items
        );
    }
}
