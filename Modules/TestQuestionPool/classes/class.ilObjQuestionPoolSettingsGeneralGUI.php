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
    public const CMD_SHOW_ADDITIONAL_FORM = 'showAdditionalForm';
    public const CMD_SAVE_ADDITIONAL_FORM = 'saveAdditionalForm';
    public const TAB_COMMON_SETTINGS = 'settings';
    public const TAB_ADDITIONAL_SETTINGS = 'additional_settings';
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
        }

        //if ($errors) {
        //    $this->tpl->setOnScreenMessage('failure', $this->lng->txt('form_input_not_valid'));
        //    $this->showFormCmd($form);
        //}

        //$this->performSaveForm($form);

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, self::CMD_SHOW_GENERAL_FORM);
    }

    private function performSaveForm($data): void
    {
        $md_obj = new ilMD($this->poolOBJ->getId(), 0, "qpl");
        $md_section = $md_obj->getGeneral();

        $general_settings = $data['general_settings'] ?? [];

        if ($md_section) {
            $md_section->setTitle($general_settings['title'] ?? '');
            $md_section->update();
        }

        // Description
        $md_desc_ids = $md_section->getDescriptionIds();
        if ($md_desc_ids) {
            $md_desc = $md_section->getDescription(array_pop($md_desc_ids));
        }
        if (isset($md_desc)) {
            $md_desc->setDescription($general_settings['description'] ?? '');
            $md_desc->update();
        } else {
            $md_desc = $md_section->addDescription();
            $md_desc->setDescription($general_settings['description'] ?? '');
            $md_desc->save();
        }

        $this->poolOBJ->setTitle($general_settings['title'] ?? '');
        $this->poolOBJ->setDescription($general_settings['description'] ?? '');
        $this->poolOBJ->update();

        $availability = $data['availability'] ?? [];
        $this->poolOBJ->getObjectProperties()->storePropertyIsOnline(
            current($availability) ?: new ilObjectPropertyIsOnline(false)
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

        if ($md_section) {
            $title = $this->ui_factory->input()->field()->text($this->lng->txt("title"))
                                      ->withRequired(true)
                                      ->withValue($md_section->getTitle());

            $ids = $md_section->getDescriptionIds();
            if ($ids) {
                $desc_obj = $md_section->getDescription(array_pop($ids));
                if ($desc_obj) {
                    $description = $this->ui_factory->input()->field()->textarea(
                        $this->lng->txt("description")
                    )->withValue($desc_obj->getDescription());
                }
            }

            $items['general_settings'] = $this->ui_factory->input()->field()->section(
                [
                    'title' => $title,
                    'description' => $description ?? null,
                ],
                $this->lng->txt('qpl_form_general_settings')
            );
        }

        $online = $this->poolOBJ->getObjectProperties()->getPropertyIsOnline()->toForm(
            $this->lng,
            $this->ui_factory->input()->field(),
            $this->refinery
        );
        $availability = $this->ui_factory->input()->field()->section(
            [$online],
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
