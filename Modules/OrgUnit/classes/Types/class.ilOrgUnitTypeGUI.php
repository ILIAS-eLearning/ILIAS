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
 ********************************************************************
 */

declare(strict_types=1);


use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\Refinery\Factory as Refinery;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ilOrgUnitTypeGUI
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitTypeGUI
{
    private ilCtrl $ctrl;
    private ilGlobalTemplateInterface $tpl;
    private ilTabsGUI $tabs;
    private ilAccessHandler $access;
    private ilToolbarGUI $toolbar;
    private \ilSetting $settings;
    private ilLanguage $lng;
    protected \ILIAS\UI\Component\Link\Factory $link_factory;
    protected UIFactory $ui_factory;
    protected UIRenderer $ui_renderer;
    protected Refinery $refinery;
    protected ServerRequestInterface $request;

    /**
     * @param ilObjOrgUnitGUI $parent_gui
     */
    public function __construct(
        private ilObjOrgUnitGUI $parent_gui
    ) {
        global $DIC;

        $this->tpl = $DIC->ui()->mainTemplate();
        $this->ctrl = $DIC->ctrl();
        $this->access = $DIC->access();
        $this->toolbar = $DIC->toolbar();
        $this->tabs = $DIC->tabs();
        $this->lng = $DIC->language();
        $this->settings = $DIC->settings();
        $this->lng->loadLanguageModule('orgu');
        $this->ctrl->saveParameter($this, 'type_id');
        $this->lng->loadLanguageModule('meta');
        $this->checkAccess();
        $this->link_factory = $DIC['ui.factory']->link();
        $this->ui_factory = $DIC['ui.factory'];
        $this->ui_renderer = $DIC['ui.renderer'];
        $this->refinery = $DIC['refinery'];
        $this->request = $DIC->http()->request();
        $this->lng->loadLanguageModule('meta');
    }

    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd();
        $next_class = $this->ctrl->getNextClass($this);
        switch ($next_class) {
            case '':
                switch ($cmd) {
                    case '':
                    case 'listTypes':
                        $this->listTypes();
                        break;
                    case 'add':
                        $this->add();
                        break;
                    case 'edit':
                        $this->setSubTabsEdit('general');
                        $this->edit();
                        break;
                    case 'editCustomIcons':
                        $this->setSubTabsEdit('custom_icons');
                        $this->editCustomIcons();
                        break;
                    case 'editAMD':
                        $this->setSubTabsEdit('amd');
                        $this->editAMD();
                        break;
                    case 'updateAMD':
                        $this->setSubTabsEdit('amd');
                        $this->updateAMD();
                        break;
                    case 'updateCustomIcons':
                        $this->setSubTabsEdit('custom_icons');
                        $this->updateCustomIcons();
                        break;
                    case 'create':
                        $this->create();
                        break;
                    case 'update':
                        $this->setSubTabsEdit('general');
                        $this->update();
                        break;
                    case 'delete':
                        $this->delete();
                        break;
                }
                break;
        }
    }

    private function checkAccess(): void
    {
        if (!$this->access->checkAccess("write", "", $this->parent_gui->object->getRefId())) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("permission_denied"), true);
            $this->ctrl->redirect($this->parent_gui);
        }
    }

    private function setSubTabsEdit(string $active_tab_id): void
    {
        $this->tabs->addSubTab('general', $this->lng->txt('meta_general'), $this->ctrl->getLinkTarget($this, 'edit'));
        if ($this->settings->get('custom_icons')) {
            $this->tabs->addSubTab(
                'custom_icons',
                $this->lng->txt('icon_settings'),
                $this->ctrl->getLinkTarget($this, 'editCustomIcons')
            );
        }
        if (count(ilOrgUnitType::getAvailableAdvancedMDRecordIds())) {
            $this->tabs->addSubTab('amd', $this->lng->txt('md_advanced'), $this->ctrl->getLinkTarget($this, 'editAMD'));
        }
        $this->tabs->setSubTabActive($active_tab_id);
    }

    /**
     * Display form for editing custom icons
     */
    private function editCustomIcons(): void
    {
        $form = new ilOrgUnitTypeCustomIconsFormGUI($this, new ilOrgUnitType((int) $_GET['type_id']));
        $this->tpl->setContent($form->getHTML());
    }

    private function updateCustomIcons(): void
    {
        $form = new ilOrgUnitTypeCustomIconsFormGUI($this, new ilOrgUnitType((int) $_GET['type_id']));
        if ($form->saveObject()) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_obj_modified'), true);
            $this->ctrl->redirect($this);
        } else {
            $this->tpl->setContent($form->getHTML());
        }
    }

    private function getAmdForm(): \ILIAS\UI\Component\Input\Container\Form\Standard
    {
        $action = $this->ctrl->getFormAction($this, 'updateAMD');
        $records = ilOrgUnitType::getAvailableAdvancedMDRecords();
        $options = [];
        foreach ($records as $record) {
            $options[$record->getRecordId()] = $record->getTitle();
        }

        $type = new ilOrgUnitType((int) $_GET['type_id']);
        $records_selected = $type->getAssignedAdvancedMDRecordIds();
        $selected = [];
        foreach ($records_selected as $record_id) {
            $selected[] = $record_id;
        }

        $trafo = $this->refinery->custom()->transformation(
            fn($v) => is_array($v) ? array_shift($v) : []
        );

        $field = $this->ui_factory->input()->field()->multiselect(
            $this->lng->txt('orgu_type_available_amd_sets'),
            $options
        )
        ->withValue($selected);

        $section = $this->ui_factory->input()->field()->section(
            [$field],
            $this->lng->txt('orgu_type_assign_amd_sets')
        )
        ->withAdditionalTransformation($trafo);

        $store = $this->refinery->custom()->transformation(
            function (?array $record_ids) use ($type, $records_selected) {
                $record_ids = $record_ids ?? [];
                $record_ids_removed = array_diff($records_selected, $record_ids);
                $record_ids_added = array_diff($record_ids, $records_selected);
                foreach ($record_ids_added as $record_id) {
                    $type->assignAdvancedMDRecord((int)$record_id);
                }
                foreach ($record_ids_removed as $record_id) {
                    $type->deassignAdvancedMdRecord((int)$record_id);
                }
                return true;
            }
        );

        return $this->ui_factory->input()->container()->form()->standard($action, [$section])
           ->withAdditionalTransformation($trafo)
           ->withAdditionalTransformation($store);
    }

    private function editAMD(): void
    {
        $form = $this->getAmdForm();
        $this->tpl->setContent(
            $this->ui_renderer->render($form)
        );
    }

    private function updateAMD(): void
    {
        $form = $this->getAmdForm()->withRequest($this->request);
        if($form->getData()) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_obj_modified'), true);
            $this->ctrl->redirect($this);
        } else {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('error'), true);
            $this->tpl->setContent($this->ui_renderer->render($form));
        }
    }

    /**
     * Display all types in a table with actions to edit/delete
     */
    private function listTypes(): void
    {
        $url = $this->ctrl->getLinkTarget($this, 'add');
        $link = $this->link_factory->standard(
            $this->lng->txt('orgu_type_add'),
            $url
        );
        $this->toolbar->addComponent($link);

        $table = new ilOrgUnitTypeTableGUI($this, 'listTypes');
        $this->tpl->setContent($table->getHTML());
    }

    /**
     * Display form to create a new OrgUnit type
     */
    private function add(): void
    {
        $form = new ilOrgUnitTypeFormGUI($this, new ilOrgUnitType());
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * Display form to edit an existing OrgUnit type
     */
    private function edit(): void
    {
        $type = new ilOrgUnitType((int) $_GET['type_id']);
        $form = new ilOrgUnitTypeFormGUI($this, $type);
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * Create (save) type
     */
    protected function create(): void
    {
        $form = new ilOrgUnitTypeFormGUI($this, new ilOrgUnitType());
        if ($form->saveObject()) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_obj_created'), true);
            $this->ctrl->redirect($this);
        } else {
            $this->tpl->setContent($form->getHTML());
        }
    }

    /**
     * Update (save) type
     */
    private function update(): void
    {
        $form = new ilOrgUnitTypeFormGUI($this, new ilOrgUnitType((int) $_GET['type_id']));
        if ($form->saveObject()) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_obj_modified'), true);
            $this->ctrl->redirect($this);
        } else {
            $this->tpl->setContent($form->getHTML());
        }
    }

    /**
     * Delete a type
     */
    private function delete(): void
    {
        $type = new ilOrgUnitType((int) $_GET['type_id']);
        try {
            $type->delete();
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('orgu_type_msg_deleted'), true);
            $this->ctrl->redirect($this);
        } catch (ilException $e) {
            $this->tpl->setOnScreenMessage('failure', $e->getMessage(), true);
            $this->ctrl->redirect($this);
        }
    }
}
